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

        // 1. Ambil Kredensial dari config (aman setelah config:cache)
        $baseUrl = config('ehrm.base_url');
        $apiKey  = config('ehrm.api_key');
        $email   = config('ehrm.email');
        $password = config('ehrm.password');

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
        $this->syncTahap1DataUtamaPegawai($baseUrl, $apiKey, $token);

        // ============================================================
        // TAHAP 2: RIWAYAT JABATAN
        // ============================================================
        $this->syncTahap2RiwayatJabatan($baseUrl, $apiKey, $token);

        // ============================================================
        // TAHAP 3: RIWAYAT ANGKA KREDIT
        // ============================================================
        $this->syncTahap3RiwayatAngkaKredit($baseUrl, $apiKey, $token);

        // ============================================================
        // TAHAP 5: SINKRONISASI DATA TAMBAHAN (API BARU)
        // ============================================================
        $newToken = config('ehrm.new_token');
        $newBaseUrl = 'https://ehrm.pu.go.id/api/modules-api';
        $this->syncTahap5DataTambahan($newBaseUrl, $newToken);

        $this->info('🎉 Sinkronisasi LENGKAP Selesai!');
        ActivityLogger::logApiSync('Sinkronisasi data pegawai dari API e-HRM selesai');
    }

    /**
     * TAHAP 1: SINKRONISASI DATA UTAMA PEGAWAI
     */
    private function syncTahap1DataUtamaPegawai(string $baseUrl, string $apiKey, string $token): void
    {
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
                // tmt_kgb_terakhir TIDAK diambil dari API lama — sumber kebenaran adalah Tahap 5 (API baru /gaji-pokok-latest)
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
                    // Wrap dalam transaction agar FK_CHECKS tidak stuck di 0 jika terjadi error
                    \DB::transaction(function () use ($oldId, $realApiId) {
                        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
                        try {
                            \DB::table('pegawai')->where('id_pegawai_api', $oldId)->update(['id_pegawai_api' => $realApiId]);
                            \DB::table('dashboard_tracker')->where('pegawai_id', $oldId)->update(['pegawai_id' => $realApiId]);
                            \DB::table('riwayat_angka_kredit')->where('id_pegawai_api', $oldId)->update(['id_pegawai_api' => $realApiId]);
                        } finally {
                            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
                        }
                    });
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

        // Hapus pegawai (dan relasinya via Cascade Delete) yang sudah tidak ada di API e-HRM (misal pindah tugas dari Pusdatin)
        // KECUALI pegawai dummy/seeder yang digunakan untuk testing (id_pegawai_api mengandung kata 'dummy')
        $apiNips = collect($dataPegawai)->pluck('nip')->filter()->all();
        if (count($apiNips) > 0) {
            $deletedCount = Pegawai::whereNotIn('nip', $apiNips)
                ->where('id_pegawai_api', 'NOT LIKE', '%dummy%')
                ->delete();
            if ($deletedCount > 0) {
                $this->info("🗑️ Berhasil menghapus {$deletedCount} pegawai yang sudah tidak terdaftar di e-HRM Pusdatin.");
                ActivityLogger::logApiSync("Menghapus {$deletedCount} pegawai dari database lokal karena tidak terdaftar lagi di e-HRM Pusdatin");
            }
        }

        $this->updateProgressCache(25, 1, 'done', 'Data Utama Pegawai Selesai');
        $bar->finish();
        $this->newLine();
    }

    /**
     * TAHAP 2: RIWAYAT JABATAN
     */
    private function syncTahap2RiwayatJabatan(string $baseUrl, string $apiKey, string $token): void
    {
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
                $currentRiw++;
                $bar2->advance();

                $apiId = $item['id_pegawai'] ?? $item['id'] ?? null;
                if (!$apiId) continue;

                // Sort riwjabatan by tglmulai desc agar [0] selalu yang terbaru
                // Sort riwjabatan by tglmulai desc, if equal, prefer records with tipejabatan and kd_eselon
                $riwJabatanSorted = collect($item['riwjabatan'] ?? [])->sort(function ($a, $b) {
                    $tmtA = $a['tglmulai'] ?? '';
                    $tmtB = $b['tglmulai'] ?? '';
                    if ($tmtA !== $tmtB) {
                        return strcmp($tmtB, $tmtA);
                    }
                    $tipeA = !empty($a['tipejabatan']) ? 1 : 0;
                    $tipeB = !empty($b['tipejabatan']) ? 1 : 0;
                    if ($tipeA !== $tipeB) {
                        return $tipeB <=> $tipeA;
                    }
                    $eselonA = (!empty($a['kd_eselon']) || !empty($a['kdeselon'])) ? 1 : 0;
                    $eselonB = (!empty($b['kd_eselon']) || !empty($b['kdeselon'])) ? 1 : 0;
                    return $eselonB <=> $eselonA;
                })->values()->all();
                $riwPangkatArr    = $item['riwpangkat'] ?? [];

                // Ambil NIP dari nipbaru di riwjabatan / riwpangkat (sudah sorted)
                $nipFromRiw = $riwJabatanSorted[0]['nipbaru']
                    ?? ($riwPangkatArr[0]['nipbaru'] ?? null);

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

                if (!empty($riwJabatanSorted)) {
                    // Update Tipe Jabatan & kd_eselon di Pegawai (sudah sorted, [0] = terbaru)
                    $latest = $riwJabatanSorted[0];
                    $updateData = ['tipe_jabatan' => $latest['tipejabatan'] ?? null];

                    // Ambil kd_eselon dari riwayat jabatan terbaru
                    if (!empty($latest['kdeselon'])) {
                        $updateData['kd_eselon'] = $latest['kdeselon'];
                    } elseif (!empty($latest['kd_eselon'])) {
                        $updateData['kd_eselon'] = $latest['kd_eselon'];
                    }

                    $pegawai->update($updateData);

                    foreach ($riwJabatanSorted as $jab) {
                        \App\Models\RiwayatJabatan::updateOrCreate(
                            [
                                'nip'     => $pegawai->nip,
                                'nosk'    => $jab['nosk'] ?? '-',
                                'jabatan' => $jab['jabatan'] ?? '-',
                            ],
                            [
                                'tgl_sk'       => $this->parseDate($jab['tgl_sk'] ?? null),
                                'tmt_jabatan'  => $this->parseDate($jab['tglmulai'] ?? null),
                                'tgl_selesai'  => $this->parseDate($jab['tglselesai'] ?? null),
                                'tipe_jabatan' => $jab['tipejabatan'] ?? null,
                                'file_sk'      => $jab['sk'] ?? null,
                                'kd_eselon'    => $jab['kd_eselon'] ?? null,
                            ]
                        );
                    }
                }

                // Mapping Pangkat untuk Fallback Validasi
                $pangkatMap = [
                    'I/a' => 'Juru Muda',
                    'I/b' => 'Juru Muda Tingkat I',
                    'I/c' => 'Juru',
                    'I/d' => 'Juru Tingkat I',
                    'II/a' => 'Pengatur Muda',
                    'II/b' => 'Pengatur Muda Tingkat I',
                    'II/c' => 'Pengatur',
                    'II/d' => 'Pengatur Tingkat I',
                    'III/a' => 'Penata Muda',
                    'III/b' => 'Penata Muda Tingkat I',
                    'III/c' => 'Penata',
                    'III/d' => 'Penata Tingkat I',
                    'IV/a' => 'Pembina',
                    'IV/b' => 'Pembina Tingkat I',
                    'IV/c' => 'Pembina Utama Muda',
                    'IV/d' => 'Pembina Utama Madya',
                    'IV/e' => 'Pembina Utama'
                ];

                // Sync tmt_pangkat_terakhir dari riwpangkat (sudah sorted di atas)
                if (!empty($riwPangkatArr)) {
                    $riwPangkatSorted2 = collect($riwPangkatArr)->sortByDesc('tglmulai');
                    
                    // Reset sk_pangkat_terakhir if validation fails later
                    $updateDataPangkat = ['sk_pangkat_terakhir' => null];
                    
                    $matchedPangkat = null;
                    if ($pegawai->tmt_pangkat_terakhir) {
                        $currentTmtStr = $pegawai->tmt_pangkat_terakhir->format('Y-m-d');
                        $currentGolongan = $pegawai->pangkat_golongan ?? '';
                        $expectedDeskripsi = strtolower($pangkatMap[$currentGolongan] ?? '');

                        $matchedPangkat = $riwPangkatSorted2->first(function ($pangkat) use ($currentTmtStr, $expectedDeskripsi) {
                            $parsed = $this->parseDate($pangkat['tglmulai'] ?? null);
                            $tmtMatches = $parsed && $parsed === $currentTmtStr;
                            
                            $deskripsiMatches = false;
                            if ($expectedDeskripsi && !empty($pangkat['deskripsi'])) {
                                $deskripsiMatches = (strpos(strtolower($pangkat['deskripsi']), $expectedDeskripsi) !== false);
                            }
                            
                            return $tmtMatches || $deskripsiMatches;
                        });
                    }

                    if ($matchedPangkat) {
                        // Found matching rank!
                        if (!empty($matchedPangkat['sk'])) {
                            $updateDataPangkat['sk_pangkat_terakhir'] = $matchedPangkat['sk'];
                        }
                    } elseif (!$pegawai->tmt_pangkat_terakhir) {
                        // Fallback to latest if employee has no TMT Pangkat at all
                        $latestPangkat = $riwPangkatSorted2->first();
                        if ($latestPangkat && !empty($latestPangkat['tglmulai'])) {
                            $tmtParsed = $this->parseDate($latestPangkat['tglmulai']);
                            if ($tmtParsed) {
                                $updateDataPangkat['tmt_pangkat_terakhir'] = $tmtParsed;
                            }
                            if (!empty($latestPangkat['sk'])) {
                                $updateDataPangkat['sk_pangkat_terakhir'] = $latestPangkat['sk'];
                            }
                        }
                    }

                    if (!empty($updateDataPangkat)) {
                        $pegawai->update($updateDataPangkat);
                    }
                }

                // Progress sudah di-increment di atas (sebelum continue)
                $stepProgress = 25 + intval(($currentRiw / max(1, $totalRiw)) * 20);
                if ($currentRiw % 50 === 0 || $currentRiw === $totalRiw) {
                    $this->updateProgressCache($stepProgress, 2, 'processing', "Memproses riwayat jabatan $currentRiw / $totalRiw pegawai...");
                }
            }
            $this->updateProgressCache(45, 2, 'done', 'Riwayat Jabatan Selesai');
            $bar2->finish();
        } else {
            $this->error('❌ Gagal ambil Riwayat Jabatan.');
        }
        $this->newLine();
    }

    /**
     * TAHAP 3: RIWAYAT ANGKA KREDIT
     */
    private function syncTahap3RiwayatAngkaKredit(string $baseUrl, string $apiKey, string $token): void
    {
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
    }



    /**
     * TAHAP 5: SINKRONISASI DATA TAMBAHAN (API BARU)
     */
    private function syncTahap5DataTambahan(string $newBaseUrl, ?string $newToken): void
    {
        $this->info('⬇️  [5/5] Mengunduh Data Tambahan (KGB, SKP, Diklat, Jabatan, Tubel)...');

        if (!$newToken) {
            $this->error('❌ EHRM_NEW_TOKEN tidak ditemukan di .env. Lewati tahap ini.');
            return;
        }

        // PENTING: sertakan 'id_pegawai_api' (PRIMARY KEY model Pegawai) agar $peg->update() tidak silently fail
        $allPegawai = Pegawai::select('nip', 'nama', 'id_pegawai_api')->get();
        $totalBaru = $allPegawai->count();
        $bar5 = $this->output->createProgressBar($totalBaru);
        $bar5->start();

        $this->updateProgressCache(80, 5, 'processing', 'Memulai sinkronisasi data tambahan...');

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
                
                // 1. KGB — sumber kebenaran tmt_kgb_terakhir (API baru lebih akurat dari API lama)
                $kgbResp = $responses["kgb_".$nip] ?? null;
                if ($kgbResp instanceof \Illuminate\Http\Client\Response && $kgbResp->successful()) {
                    $dataKgb = $kgbResp->json()['data'][$nip] ?? [];
                    if (is_array($dataKgb) && !empty($dataKgb)) {
                        // Sort by tanggal_berlaku desc agar [0] selalu KGB terbaru
                        $latestKgb = collect($dataKgb)->sortByDesc(function ($item) {
                            return Carbon::parse($item['tanggal_berlaku'])->timestamp;
                        })->first();
                        if ($latestKgb && !empty($latestKgb['tanggal_berlaku'])) {
                            $peg->update([
                                'tmt_kgb_terakhir' => $this->parseDate($latestKgb['tanggal_berlaku'])
                            ]);
                        }
                    }
                }

                // 2. SKP (Kp)
                $kpResp = $responses["kp_".$nip] ?? null;
                if ($kpResp instanceof \Illuminate\Http\Client\Response && $kpResp->successful()) {
                    $dataKp = $kpResp->json()['data'][$nip] ?? [];
                    if (is_array($dataKp) && !empty($dataKp)) {
                        \App\Models\RiwayatSkp::where('nip', $nip)->delete();
                        
                        $distinctArsip = [];

                        $currentYear = intval(date('Y'));
                        
                        foreach ($dataKp as $kp) {
                            $tahunSkp = isset($kp['tahun']) ? intval($kp['tahun']) : 0;
                            
                            // Simpan data SKP (triwulan & tahunan) untuk 3 tahun terakhir saja
                            if ($tahunSkp >= ($currentYear - 3)) {
                                \App\Models\RiwayatSkp::create([
                                    'nip' => $nip,
                                    'tahun' => $kp['tahun'] ?? null,
                                    'status' => $kp['status'] ?? null,
                                    'nilai_kinerja' => $kp['nilai_kinerja'] ?? null,
                                    'nilai_skp' => $kp['nilai_skp'] ?? null,
                                    'arsip_skp' => $kp['arsip_skp'] ?? null,
                                ]);
                                
                                if (!empty($kp['arsip_skp'])) {
                                    $distinctArsip[] = $kp['arsip_skp'];
                                }
                            }
                        }
                        
                        // Ambil maksimal 2 arsip distinct
                        if (!empty($distinctArsip)) {
                            $distinctArsip = array_values(array_unique($distinctArsip));
                            $distinctArsip = array_slice($distinctArsip, 0, 2);
                            $peg->update(['arsip_skp_2_tahun' => $distinctArsip]);
                        }

                        // Tempatkan arsip SKP di modul KJ jika ada
                        // Gunakan id_pegawai_api (bisa berisi numeric ID setelah Tahap 1)
                        $kjTracker = \App\Models\DashboardTracker::where('pegawai_id', $peg->id_pegawai_api)->where('kategori', 'KJ_Jafung')->first();
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

                        // Tempatkan arsip SKP di modul KP_Jafung jika ada
                        $kpJafungTracker = \App\Models\DashboardTracker::where('pegawai_id', $peg->id_pegawai_api)->where('kategori', 'KP_Jafung')->first();
                        if ($kpJafungTracker) {
                            $latestSkp = collect($dataKp)->whereNotNull('arsip_skp')->sortByDesc('tahun')->first();
                            if ($latestSkp) {
                                \App\Models\KelengkapanDokumen::updateOrCreate(
                                    [
                                        'dashboard_tracker_id' => $kpJafungTracker->id,
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

                // 3. Diklat Baru (Menggunakan API Baru sebagai satu-satunya sumber)
                $diklatResp = $responses["diklat_".$nip] ?? null;
                if ($diklatResp instanceof \Illuminate\Http\Client\Response && $diklatResp->successful()) {
                    $dataDiklat = $diklatResp->json()['data'][$nip] ?? [];
                    if (is_array($dataDiklat)) {
                        // Hapus semua data diklat lama karena kita sekarang hanya menggunakan API baru
                        \App\Models\RiwayatDiklat::where('nip', $nip)->delete();
                        
                        if (!empty($dataDiklat)) {
                            foreach ($dataDiklat as $d) {
                                \App\Models\RiwayatDiklat::create([
                                    'nip' => $nip,
                                    'jenis_diklat' => $d['jenis_diklat'] ?? null,
                                    'file_sertifikat' => $d['arsip'] ?? null,
                                    'arsip' => $d['arsip_bpsdm'] ?? null,
                                    'status_diklat' => 1, // Asumsikan selesai jika ada di API ini
                                    'nama_diklat' => $d['nama_diklat'] ?? $d['jenis_diklat'] ?? 'DIKLAT TAMBAHAN',
                                ]);
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
                        // Gunakan id_pegawai_api (bisa berisi numeric ID setelah Tahap 1)
                        $kjTracker = \App\Models\DashboardTracker::where('pegawai_id', $peg->id_pegawai_api)->where('kategori', 'KJ_Jafung')->first();
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
                // Tahap 5: progress 80% → 100%
                $stepProgress = 80 + intval(($currentBaru / max(1, $totalBaru)) * 20);
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

    private function parseDate(?string $dateString): ?string
    {
        // Guard: null, false, 0, atau string kosong/whitespace → return null
        if (!$dateString || trim((string) $dateString) === '') return null;
        try {
            $parsed = Carbon::parse($dateString);
            // Guard tambahan: Carbon::parse('') bisa return 'today', reject jika input tidak bermakna
            return $parsed->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Memperbarui progress ke Cache untuk divisualisasikan real-time di frontend.
     */
    private function updateProgressCache(int $progress, int $step, string $status, string $detailText): void
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