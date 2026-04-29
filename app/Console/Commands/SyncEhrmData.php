<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Pegawai;
use Carbon\Carbon;
use App\Helpers\ActivityLogger;

class SyncEhrmData extends Command
{
    protected $signature = 'ehrm:sync';
    protected $description = 'Sinkronisasi Data Pegawai dari API e-HRM KemenPU';

    public function handle()
    {
        $this->info('🚀 Memulai proses sinkronisasi Lengkap...');
        ActivityLogger::logApiSync('Memulai sinkronisasi data pegawai dari API e-HRM');

        // 1. Ambil Kredensial dari .env
        $baseUrl = env('EHRM_BASE_URL');
        $apiKey  = env('EHRM_API_KEY');
        $email   = env('EHRM_USER_EMAIL');
        $password = env('EHRM_USER_PASS');

        // 2. Login ke API Gateway
        $this->info('🔑 Sedang login...');
        $loginResponse = Http::timeout(30)->withHeaders([
            'X-DreamFactory-Api-Key' => $apiKey,
        ])->post("$baseUrl/user/login", [
            'email' => $email,
            'password' => $password,
        ]);

        if ($loginResponse->failed()) {
            $this->error('❌ Gagal Login! Detail: ' . $loginResponse->body());
            return;
        }

        $token = $loginResponse->json()['session_token'] ?? null;
        if (!$token) {
            $this->error('❌ Token tidak ditemukan.');
            return;
        }

        // ============================================================
        // TAHAP 1: SINKRONISASI DATA UTAMA PEGAWAI
        // ============================================================
        $this->info('⬇️  [1/4] Mengunduh data utama pegawai...');
        $pegawaiResponse = Http::timeout(60)->withHeaders([
            'X-DreamFactory-Api-Key' => $apiKey,
            'X-DreamFactory-Session-Token' => $token,
        ])->get("$baseUrl/v1/ehrm/pegawai");

        if ($pegawaiResponse->failed()) {
            $this->error('❌ Gagal ambil data pegawai. Detail: ' . $pegawaiResponse->body());
            return;
        }

        $dataPegawai = $pegawaiResponse->json()['resource'] ?? $pegawaiResponse->json();
        $totalPegawai = count($dataPegawai);
        $bar = $this->output->createProgressBar($totalPegawai);
        $bar->start();

        $this->updateProgressCache(5, 1, 'processing', 'Memulai sinkronisasi data utama pegawai...');

        $currentPegawai = 0;
        foreach ($dataPegawai as $item) {
            // Field 'id_pegawai' adalah ID numerik dari API e-HRM, berbeda dengan NIP
            $numericId = $item['id_pegawai'] ?? $item['id'] ?? null;

            $updateData = [
                'nama'             => $item['nama_lengkap'] ?? $item['nama'] ?? 'Tanpa Nama',
                'email'            => $item['email_pu'] ?? $item['email'] ?? null,
                'no_hp'            => $item['telphp'] ?? null,
                'jabatan_saat_ini' => $item['jabatan_lengkap'] ?? null,
                'pangkat_golongan' => $item['golongan'] ?? null,
                'jenjang'          => $item['jenjang'] ?? null,
                'kd_eselon'        => $item['kd_eselon'] ?? null,
            ] + array_filter([
                'tmt_cpns'            => $this->parseDate($item['tmt_cpns'] ?? null),
                'tmt_pangkat_terakhir'=> $this->parseDate($item['tmt_pangkat'] ?? null),
                'tmt_kgb_terakhir'    => $this->parseDate($item['tmt_kgb'] ?? null),
            ]);

            // Simpan ID numerik ke kolom terpisah (tanpa mengubah PK id_pegawai_api)
            if ($numericId) {
                $updateData['numeric_api_id'] = (int) $numericId;
            }

            $pegawai = Pegawai::where('nip', $item['nip'])->first();
            if ($pegawai) {
                // FIX: Update id_pegawai_api jika masih berisi NIP (bukan numeric API ID)
                // Harus pakai raw SQL karena id_pegawai_api adalah PRIMARY KEY
                $realApiId = $item['id_pegawai'] ?? $item['id'] ?? null;
                if ($realApiId && $pegawai->id_pegawai_api != $realApiId) {
                    $oldId = $pegawai->id_pegawai_api;
                    // Matikan FK check sementara karena kita update PK + FK sekaligus
                    \DB::statement('SET FOREIGN_KEY_CHECKS=0');
                    \DB::table('pegawai')->where('id_pegawai_api', $oldId)->update(['id_pegawai_api' => $realApiId]);
                    \DB::table('dashboard_tracker')->where('pegawai_id', $oldId)->update(['pegawai_id' => $realApiId]);
                    \DB::table('riwayat_angka_kredit')->where('id_pegawai_api', $oldId)->update(['id_pegawai_api' => $realApiId]);
                    \DB::statement('SET FOREIGN_KEY_CHECKS=1');
                    $pegawai = Pegawai::where('nip', $item['nip'])->first();
                }
                $pegawai->update($updateData);
            } else {
                $updateData['nip'] = $item['nip'];
                $updateData['id_pegawai_api'] = $item['nip']; // PK = NIP
                Pegawai::create($updateData);
            }
            $currentPegawai++;
            // Calculate progress: Step 1 goes from 5% to 25% (20% total)
            $stepProgress = 5 + intval(($currentPegawai / $totalPegawai) * 20);
            if ($currentPegawai % 50 === 0 || $currentPegawai === $totalPegawai) {
                $this->updateProgressCache($stepProgress, 1, 'processing', "Memproses $currentPegawai / $totalPegawai pegawai...");
            }
            $bar->advance();
        }
        $this->updateProgressCache(25, 1, 'done', 'Data Utama Pegawai Selesai');
        $bar->finish();
        $this->newLine();


        // ============================================================
        // TAHAP 2: RIWAYAT JABATAN
        // ============================================================
        $this->info('⬇️  [2/4] Mengunduh Riwayat Jabatan...');
        $riwResponse = Http::timeout(60)->withHeaders([
            'X-DreamFactory-Api-Key' => $apiKey,
            'X-DreamFactory-Session-Token' => $token,
        ])->get("$baseUrl/v1/ehrm/riw");

        if ($riwResponse->successful()) {
            $dataRiw = $riwResponse->json()['resource'] ?? $riwResponse->json();
            $totalRiw = count($dataRiw);
            $bar2 = $this->output->createProgressBar($totalRiw);
            $bar2->start();

            $this->updateProgressCache(25, 2, 'processing', 'Memulai sinkronisasi riwayat jabatan...');

            $currentRiw = 0;
            foreach ($dataRiw as $item) {
                $apiId = $item['id'] ?? null;
                if (!$apiId) continue;

                // Ambil NIP dari nipbaru di riwjabatan / riwpangkat
                $nipFromRiw = $item['riwjabatan'][0]['nipbaru']
                    ?? $item['riwpangkat'][0]['nipbaru']
                    ?? null;

                // Cari pegawai: prioritaskan NIP dari riwayat, fallback ke numeric_api_id
                $pegawai = null;
                if ($nipFromRiw) {
                    $pegawai = Pegawai::where('nip', $nipFromRiw)->first();
                }
                if (!$pegawai) {
                    $pegawai = Pegawai::where('numeric_api_id', (int)$apiId)->first();
                }
                if (!$pegawai) continue;

                // Update numeric_api_id jika belum terisi
                if (!$pegawai->numeric_api_id) {
                    $pegawai->update(['numeric_api_id' => (int)$apiId]);
                }

                if (isset($item['riwjabatan']) && is_array($item['riwjabatan'])) {
                    // Update Tipe Jabatan & kd_eselon di Pegawai (ambil data terbaru)
                    if (count($item['riwjabatan']) > 0) {
                        $latest = $item['riwjabatan'][0];
                        $updateData = ['tipe_jabatan' => $latest['tipejabatan'] ?? null];

                        // Ambil kd_eselon dari riwayat jabatan terbaru (hanya ada di riwjabatan, bukan data utama)
                        if (isset($latest['kd_eselon']) && $latest['kd_eselon']) {
                            $updateData['kd_eselon'] = $latest['kd_eselon'];
                        }

                        $pegawai->update($updateData);
                    }

                    foreach ($item['riwjabatan'] as $jab) {
                        \App\Models\RiwayatJabatan::updateOrCreate(
                            [
                                'nip' => $pegawai->nip,
                                'nosk' => $jab['nosk'] ?? '-',
                                'jabatan' => $jab['jabatan'] ?? '-',
                            ],
                            [
                                'tgl_sk' => $this->parseDate($jab['tgl_sk'] ?? null),
                                'tmt_jabatan' => $this->parseDate($jab['tglmulai'] ?? null),
                                'tgl_selesai' => $this->parseDate($jab['tglselesai'] ?? null),
                                'tipe_jabatan' => $jab['tipejabatan'] ?? null,
                                'file_sk' => $jab['sk'] ?? null,
                            ]
                        );
                    }
                }

                // Sync tmt_pangkat_terakhir dari riwpangkat (jika ada)
                if (isset($item['riwpangkat']) && is_array($item['riwpangkat']) && count($item['riwpangkat']) > 0) {
                    // Sort by tglmulai desc → ambil yang terbaru
                    $riwPangkatSorted = collect($item['riwpangkat'])->sortByDesc('tglmulai');
                    $latestPangkat = $riwPangkatSorted->first();

                    if ($latestPangkat && !empty($latestPangkat['tglmulai'])) {
                        $tmtParsed = $this->parseDate($latestPangkat['tglmulai']);
                        if ($tmtParsed) {
                            $pegawai->update(['tmt_pangkat_terakhir' => $tmtParsed]);
                        }
                    }
                }

                $currentRiw++;
                // Calculate progress: Step 2 goes from 25% to 45% (20% total)
                $stepProgress = 25 + intval(($currentRiw / max(1, $totalRiw)) * 20);
                if ($currentRiw % 50 === 0 || $currentRiw === $totalRiw) {
                    $this->updateProgressCache($stepProgress, 2, 'processing', "Memproses riwayat jabatan $currentRiw / $totalRiw pegawai...");
                }
                $bar2->advance();
            }
            $this->updateProgressCache(45, 2, 'done', 'Riwayat Jabatan Selesai');
            $bar2->finish();
        } else {
            $this->error('❌ Gagal ambil Riwayat Jabatan.');
        }
        $this->newLine();


        // ============================================================
        // TAHAP 3: RIWAYAT ANGKA KREDIT
        // ============================================================
        $this->info('⬇️  [3/4] Mengunduh Riwayat Angka Kredit...');
        
        // Hanya ambil pegawai yang sudah punya numeric_api_id (ID numerik dari API)
        $allPegawai = Pegawai::select('nip', 'id_pegawai_api', 'numeric_api_id')
                             ->whereNotNull('numeric_api_id')
                             ->get();
        $totalAk = $allPegawai->count();
        $bar3 = $this->output->createProgressBar($totalAk);
        $bar3->start();

        $this->updateProgressCache(45, 3, 'processing', 'Memulai sinkronisasi angka kredit...');

        $currentAk = 0;
        $akChunks = $allPegawai->chunk(3);
        foreach ($akChunks as $chunk) {
            $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($chunk, $baseUrl, $apiKey, $token) {
                $reqs = [];
                foreach ($chunk as $peg) {
                    if (!$peg->numeric_api_id) continue;
                    // Gunakan numeric_api_id (bukan NIP) sebagai filter ke API
                    $reqs[] = $pool->as("peg_".$peg->nip)->timeout(20)->withHeaders([
                        'X-DreamFactory-Api-Key' => $apiKey,
                        'X-DreamFactory-Session-Token' => $token,
                    ])->get("$baseUrl/v1/ehrm/riw/angka-kredit", [
                        'filter' => "id_pegawai={$peg->numeric_api_id}"
                    ]);
                }
                return $reqs;
            });

            foreach ($chunk as $peg) {
                $akKey = "peg_".$peg->nip;
                
                if (isset($responses[$akKey])) {
                    $akResponse = $responses[$akKey];
                    
                    if ($akResponse instanceof \Illuminate\Http\Client\Response && $akResponse->successful()) {
                        $listAK = $akResponse->json()['resource'] ?? [];
                        
                        if (is_array($listAK) && !empty($listAK)) {
                            \App\Models\RiwayatAngkaKredit::where('id_pegawai_api', $peg->id_pegawai_api)->delete();

                            foreach ($listAK as $ak) {
                                $totalKredit = floatval($ak['penilaian_ak'] ?? 0);
                                if ($totalKredit == 0) {
                                    $totalKredit = floatval($ak['tkutama3'] ?? 0);
                                }
                                if ($totalKredit == 0) {
                                    $totalKredit = floatval($ak['tkutama1'] ?? 0) + floatval($ak['tkutama2'] ?? 0);
                                }

                                \App\Models\RiwayatAngkaKredit::create([
                                    'id_pegawai_api'      => $peg->id_pegawai_api,
                                    'nomor_sk'            => $ak['tknopak'] ?? '-',
                                    'tanggal_sk'          => $this->parseDate($ak['tktglpak'] ?? null),
                                    'tmt_angka_kredit'    => $this->parseDate($ak['tmtakrid'] ?? null),
                                    'kredit_utama'        => floatval($ak['tkutama1'] ?? 0),
                                    'kredit_penunjang'    => floatval($ak['tkutama2'] ?? 0),
                                    'total_kredit'        => $totalKredit,
                                    'jabatan_saat_penilaian' => $ak['tk_keterangan'] ?? null,
                                ]);
                            }
                        }
                    } elseif ($akResponse instanceof \Exception) {
                        \Illuminate\Support\Facades\Log::warning("Gagal ambil AK NIP {$peg->nip}: " . $akResponse->getMessage());
                    }
                }

                $currentAk++;
                $stepProgress = 45 + intval(($currentAk / max(1, $totalAk)) * 25);
                if ($currentAk % 5 === 0 || $currentAk === $totalAk) {
                    $this->updateProgressCache($stepProgress, 3, 'processing', "Memproses angka kredit $currentAk / $totalAk pegawai...");
                }
                $bar3->advance();
            }
            sleep(1);
        }
        $this->updateProgressCache(70, 3, 'done', 'Angka Kredit Selesai');
        $bar3->finish();
        $this->newLine();

        // ============================================================
        // TAHAP 4: RIWAYAT DIKLAT
        // ============================================================
        $this->info('⬇️  [4/4] Mengunduh Riwayat Diklat (Per NIP)...');
        
        $allPegawai = Pegawai::select('nip', 'nama')->get();
        $totalDiklat = $allPegawai->count();
        $bar4 = $this->output->createProgressBar($totalDiklat);
        $bar4->start();

        $this->updateProgressCache(70, 4, 'processing', 'Memulai sinkronisasi riwayat diklat...');

        $currentDiklat = 0;
        $diklatChunks = $allPegawai->chunk(3);
        foreach ($diklatChunks as $chunk) {
            $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($chunk, $baseUrl, $apiKey, $token) {
                $reqs = [];
                foreach ($chunk as $peg) {
                    $reqs[] = $pool->as("nip_".$peg->nip)->timeout(20)->withHeaders([
                        'X-DreamFactory-Api-Key' => $apiKey,
                        'X-DreamFactory-Session-Token' => $token,
                    ])->get("$baseUrl/v1/ehrm/riw/diklat", ['nip' => $peg->nip]);
                }
                return $reqs;
            });

            foreach ($chunk as $peg) {
                $nip = $peg->nip;
                $diklatKey = "nip_".$nip;

                if (isset($responses[$diklatKey])) {
                    $diklatResp = $responses[$diklatKey];

                    if ($diklatResp instanceof \Illuminate\Http\Client\Response && $diklatResp->successful()) {
                        $listDiklat = $diklatResp->json()['resource'] ?? $diklatResp->json();
                        
                        if (is_array($listDiklat)) {
                            // Hapus data lama → replace dengan data fresh dari API
                            \App\Models\RiwayatDiklat::where('nip', $nip)->delete();

                            foreach ($listDiklat as $d) {
                                $namaDiklat = $d['uraian'] ?? $d['nmdiklat'] ?? $d['nama_diklat'] ?? '-';
                                $noSertif = $d['nomor_sertifikat'] ?? $d['no_sertifikat'] ?? $d['nosertifikat'] ?? null;

                                \App\Models\RiwayatDiklat::create([
                                    'nip' => $nip,
                                    'nama_diklat' => $namaDiklat,
                                    'nomor_sertifikat' => $noSertif,
                                    'id_diklat' => $d['id_diklat'] ?? $d['id'] ?? null,
                                    'penyelenggara' => $d['institusi_penyelenggara'] ?? $d['penyelenggara'] ?? null,
                                    'tanggal_mulai' => $this->parseDate($d['tglmulaidiklat'] ?? $d['tgl_mulai'] ?? null),
                                    'tanggal_selesai' => $this->parseDate($d['tglakhirdiklat'] ?? $d['tgl_selesai'] ?? null),
                                    'jumlah_jam' => $d['jam_diklat'] ?? $d['jam'] ?? null,
                                    'tanggal_sertifikat' => $this->parseDate($d['tgl_sertifikat'] ?? null),
                                    'file_sertifikat' => $d['arsip'] ?? $d['file'] ?? null,
                                    'status_diklat' => intval($d['status'] ?? 0),
                                    'kode_jenis' => $d['kode_jenis'] ?? null,
                                    'jenis_diklat' => $d['jenisdiklat'] ?? null,
                                    'arsip' => $d['arsip'] ?? null,
                                ]);
                            }
                        }
                    } elseif ($diklatResp instanceof \Exception) {
                        \Illuminate\Support\Facades\Log::warning("Gagal ambil Diklat NIP {$nip}: " . $diklatResp->getMessage());
                    }
                }

                $currentDiklat++;
                $stepProgress = 70 + intval(($currentDiklat / max(1, $totalDiklat)) * 25);
                if ($currentDiklat % 5 === 0 || $currentDiklat === $totalDiklat) {
                    $this->updateProgressCache($stepProgress, 4, 'processing', "Memproses riwayat diklat $currentDiklat / $totalDiklat pegawai...");
                }
                $bar4->advance();
            }
            // Beri jeda 1 detik tiap 3 request bersamaan
            sleep(1);
        }
        $bar4->finish();
        $this->newLine();

        // ============================================================
        // TAHAP 5: SINKRONISASI DATA TAMBAHAN (API BARU)
        // ============================================================
        $this->info('⬇️  [5/5] Mengunduh Data Tambahan (KGB, SKP, Diklat, Jabatan, Tubel)...');
        
        $newToken = env('EHRM_NEW_TOKEN');
        $newBaseUrl = 'https://ehrm.pu.go.id/api/modules-api';

        if (!$newToken) {
            $this->error('❌ EHRM_NEW_TOKEN tidak ditemukan di .env. Lewati tahap ini.');
        } else {
            $allPegawai = Pegawai::select('nip', 'nama')->get();
            $totalBaru = $allPegawai->count();
            $bar5 = $this->output->createProgressBar($totalBaru);
            $bar5->start();

            $this->updateProgressCache(85, 5, 'processing', 'Memulai sinkronisasi data tambahan...');

            $currentBaru = 0;
            $baruChunks = $allPegawai->chunk(3);
            foreach ($baruChunks as $chunk) {
                $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($chunk, $newBaseUrl, $newToken) {
                    $reqs = [];
                    foreach ($chunk as $peg) {
                        $nip = $peg->nip;
                        $headers = [
                            'Authorization' => "Bearer {$newToken}",
                            'Accept' => 'application/json'
                        ];
                        
                        $reqs[] = $pool->as("kgb_".$nip)->timeout(20)->withHeaders($headers)->get("$newBaseUrl/gaji-pokok-latest", ['nip' => $nip]);
                        $reqs[] = $pool->as("kp_".$nip)->timeout(20)->withHeaders($headers)->get("$newBaseUrl/skp-2tahun", ['nip' => $nip]);
                        $reqs[] = $pool->as("diklat_".$nip)->timeout(20)->withHeaders($headers)->get("$newBaseUrl/all-diklat", ['nip' => $nip]);
                        $reqs[] = $pool->as("jabatan_".$nip)->timeout(20)->withHeaders($headers)->get("$newBaseUrl/jabatan-latest", ['nip' => $nip]);
                        $reqs[] = $pool->as("tubel_".$nip)->timeout(20)->withHeaders($headers)->get("$newBaseUrl/tubel-latest", ['nip' => $nip]);
                    }
                    return $reqs;
                });

                foreach ($chunk as $peg) {
                    $nip = $peg->nip;
                    
                    // 1. KGB
                    $kgbResp = $responses["kgb_".$nip] ?? null;
                    if ($kgbResp instanceof \Illuminate\Http\Client\Response && $kgbResp->successful()) {
                        $dataKgb = $kgbResp->json()['data'][$nip] ?? [];
                        if (is_array($dataKgb) && !empty($dataKgb) && isset($dataKgb[0]['tanggal_berlaku'])) {
                            $peg->update([
                                'tmt_kgb_terakhir' => $this->parseDate($dataKgb[0]['tanggal_berlaku'])
                            ]);
                        }
                    }

                    // 2. SKP (Kp)
                    $kpResp = $responses["kp_".$nip] ?? null;
                    if ($kpResp instanceof \Illuminate\Http\Client\Response && $kpResp->successful()) {
                        $dataKp = $kpResp->json()['data'][$nip] ?? [];
                        if (is_array($dataKp) && !empty($dataKp)) {
                            \App\Models\RiwayatSkp::where('nip', $nip)->delete();
                            foreach ($dataKp as $kp) {
                                \App\Models\RiwayatSkp::create([
                                    'nip' => $nip,
                                    'tahun' => $kp['tahun'] ?? null,
                                    'status' => $kp['status'] ?? null,
                                    'nilai_kinerja' => $kp['nilai_kinerja'] ?? null,
                                    'nilai_skp' => $kp['nilai_skp'] ?? null,
                                    'arsip_skp' => $kp['arsip_skp'] ?? null,
                                ]);
                            }

                            // Tempatkan arsip SKP di modul KJ jika ada
                            $kjTracker = \App\Models\DashboardTracker::where('pegawai_id', $nip)->where('kategori', 'KJ_Jafung')->first();
                            if ($kjTracker) {
                                $latestSkp = collect($dataKp)->whereNotNull('arsip_skp')->sortByDesc('tahun')->first();
                                if ($latestSkp) {
                                    \App\Models\KelengkapanDokumen::updateOrCreate(
                                        [
                                            'dashboard_tracker_id' => $kjTracker->id,
                                            'nama_dokumen' => 'SKP 2 Tahun Terakhir',
                                            'nip' => $nip
                                        ],
                                        [
                                            'is_uploaded' => true,
                                            'link_file' => $latestSkp['arsip_skp']
                                        ]
                                    );
                                }
                            }
                        }
                    }

                    // 3. Diklat Baru
                    $diklatResp = $responses["diklat_".$nip] ?? null;
                    if ($diklatResp instanceof \Illuminate\Http\Client\Response && $diklatResp->successful()) {
                        $dataDiklat = $diklatResp->json()['data'][$nip] ?? [];
                        if (is_array($dataDiklat) && !empty($dataDiklat)) {
                            foreach ($dataDiklat as $d) {
                                $arsipDoc = $d['arsip'] ?? $d['arsip_bpsdm'] ?? null;
                                if ($arsipDoc) {
                                    $existing = \App\Models\RiwayatDiklat::where('nip', $nip)
                                        ->where(function($q) use ($arsipDoc) {
                                            $q->where('file_sertifikat', $arsipDoc)
                                              ->orWhere('arsip', $arsipDoc);
                                        })->first();
                                        
                                    if (!$existing) {
                                        \App\Models\RiwayatDiklat::create([
                                            'nip' => $nip,
                                            'jenis_diklat' => $d['jenis_diklat'] ?? null,
                                            'file_sertifikat' => $d['arsip'] ?? null,
                                            'arsip' => $d['arsip_bpsdm'] ?? $d['arsip'] ?? null,
                                            'status_diklat' => 1,
                                            'nama_diklat' => $d['jenis_diklat'] ?? 'DIKLAT TAMBAHAN',
                                        ]);
                                    }
                                }
                            }
                        }
                    }

                    // 4. Jabatan
                    $jabatanResp = $responses["jabatan_".$nip] ?? null;
                    if ($jabatanResp instanceof \Illuminate\Http\Client\Response && $jabatanResp->successful()) {
                        $dataJab = $jabatanResp->json()['data'][$nip] ?? [];
                        if (is_array($dataJab) && !empty($dataJab)) {
                            $j = $dataJab[0];
                            \App\Models\RiwayatJabatan::updateOrCreate(
                                [
                                    'nip' => $nip,
                                    'nosk' => $j['no_sk'] ?? '-',
                                ],
                                [
                                    'tmt_jabatan' => $this->parseDate($j['tanggal_mulai'] ?? null),
                                    'tgl_selesai' => $this->parseDate($j['tanggal_selesai'] ?? null),
                                    'jabatan' => $j['jabatan_utama'] ?? $j['jabatan_nama'] ?? null,
                                    'file_sk' => $j['arsip'] ?? null,
                                ]
                            );

                            // Tempatkan arsip jabatan di modul KJ
                            $kjTracker = \App\Models\DashboardTracker::where('pegawai_id', $nip)->where('kategori', 'KJ_Jafung')->first();
                            if ($kjTracker && !empty($j['arsip'])) {
                                \App\Models\KelengkapanDokumen::updateOrCreate(
                                    [
                                        'dashboard_tracker_id' => $kjTracker->id,
                                        'nama_dokumen' => 'SK Jabatan Terakhir',
                                        'nip' => $nip
                                    ],
                                    [
                                        'is_uploaded' => true,
                                        'link_file' => $j['arsip']
                                    ]
                                );
                            }
                        }
                    }

                    // 5. Tubel
                    $tubelResp = $responses["tubel_".$nip] ?? null;
                    if ($tubelResp instanceof \Illuminate\Http\Client\Response && $tubelResp->successful()) {
                        $dataTubel = $tubelResp->json()['data'][$nip] ?? [];
                        if (is_array($dataTubel) && !empty($dataTubel)) {
                            \App\Models\RiwayatTubel::where('nip', $nip)->delete();
                            foreach ($dataTubel as $t) {
                                \App\Models\RiwayatTubel::create([
                                    'nip' => $nip,
                                    'keterangan' => $t['keterangan'] ?? null,
                                    'pendidikan' => $t['pendidikan'] ?? null,
                                    'tanggal_mulai' => $this->parseDate($t['tanggal_mulai'] ?? null),
                                    'tanggal_selesai' => $this->parseDate($t['tanggal_selesai'] ?? null),
                                    'perpanjangan1_tanggal_mulai' => $this->parseDate($t['perpanjangan1_tanggal_mulai'] ?? null),
                                    'perpanjangan2_tanggal_mulai' => $this->parseDate($t['perpanjangan2_tanggal_mulai'] ?? null),
                                    'no_izin' => $t['no_izin'] ?? null,
                                    'arsip_izin_belajar' => $t['arsip_izin_belajar'] ?? null,
                                    'arsip_perpanjangan1' => $t['arsip_perpanjangan1'] ?? null,
                                    'arsip_perpanjangan2' => $t['arsip_perpanjangan2'] ?? null,
                                    'arsip_pengembalian' => $t['arsip_pengembalian'] ?? null,
                                    'status_tubel' => $t['status_tubel'] ?? null,
                                ]);
                            }
                        }
                    }
                    
                    $currentBaru++;
                    $stepProgress = 85 + intval(($currentBaru / max(1, $totalBaru)) * 15);
                    if ($currentBaru % 5 === 0 || $currentBaru === $totalBaru) {
                        $this->updateProgressCache($stepProgress, 5, 'processing', "Memproses data tambahan $currentBaru / $totalBaru pegawai...");
                    }
                    $bar5->advance();
                }
                // Beri jeda 1 detik tiap 3 chunk request
                sleep(1);
            }
            $bar5->finish();
            $this->updateProgressCache(100, 5, 'done', 'Data Tambahan Selesai');
            $this->newLine();
        }
        $this->info('🎉 Sinkronisasi LENGKAP Selesai!');
        ActivityLogger::logApiSync('Sinkronisasi data pegawai dari API e-HRM selesai');
    }

    private function parseDate($dateString)
    {
        if (!$dateString) return null;
        try {
            return Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Memperbarui progress ke Cache untuk divisualisasikan real-time di frontend.
     */
    private function updateProgressCache($progress, $step, $status, $detailText)
    {
        $currentCache = Cache::get('sync_status', []);
        
        $currentCache['progress'] = $progress;
        $currentCache['current_step'] = $step;
        if ($step === 1) $currentCache['step_1_status'] = $status;
        if ($step === 2) $currentCache['step_2_status'] = $status;
        if ($step === 3) $currentCache['step_3_status'] = $status;
        if ($step === 4) $currentCache['step_4_status'] = $status;
        if ($step === 5) $currentCache['step_5_status'] = $status;
        $currentCache['detail_text'] = $detailText;

        Cache::put('sync_status', $currentCache, now()->addMinutes(15));
    }
}