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
            Pegawai::updateOrCreate(
                ['nip' => $item['nip']], 
                [
                    'id_pegawai_api' => $item['id'] ?? null, 
                    'nama' => $item['nama_lengkap'] ?? $item['nama'] ?? 'Tanpa Nama',
                    'email' => $item['email_pu'] ?? $item['email'] ?? null,
                    'no_hp' => $item['telphp'] ?? null,
                    
                    'jabatan_saat_ini' => $item['jabatan_lengkap'] ?? null,
                    'pangkat_golongan' => $item['golongan'] ?? null,
                    'jenjang' => $item['jenjang'] ?? null,
                    'kd_eselon' => $item['kd_eselon'] ?? null,
                ] + array_filter([
                    // Hanya update tanggal jika API mengembalikan nilai (tidak timpa data manual dgn NULL)
                    'tmt_cpns' => $this->parseDate($item['tmt_cpns'] ?? null),
                    'tmt_pangkat_terakhir' => $this->parseDate($item['tmt_pangkat'] ?? null),
                    'tmt_kgb_terakhir' => $this->parseDate($item['tmt_kgb'] ?? null),
                ])
            );
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

                $pegawai = Pegawai::where('id_pegawai_api', $apiId)->first();
                if (!$pegawai) continue;

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
        
        $allPegawai = Pegawai::select('nip', 'id_pegawai_api')->get();
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
                    if (!$peg->id_pegawai_api) continue;
                    $reqs[] = $pool->as("peg_".$peg->id_pegawai_api)->timeout(20)->withHeaders([
                        'X-DreamFactory-Api-Key' => $apiKey,
                        'X-DreamFactory-Session-Token' => $token,
                    ])->get("$baseUrl/v1/ehrm/riw/angka-kredit", [
                        'filter' => "id_pegawai={$peg->id_pegawai_api}"
                    ]);
                }
                return $reqs;
            });

            foreach ($chunk as $peg) {
                $akKey = "peg_".$peg->id_pegawai_api;
                
                if ($peg->id_pegawai_api && isset($responses[$akKey])) {
                    $akResponse = $responses[$akKey];
                    
                    if ($akResponse instanceof \Illuminate\Http\Client\Response && $akResponse->successful()) {
                        $listAK = $akResponse->json()['resource'] ?? $akResponse->json();
                        
                        if (is_array($listAK)) {
                            \App\Models\RiwayatAngkaKredit::where('id_pegawai_api', $peg->id_pegawai_api)->delete();

                            foreach ($listAK as $ak) {
                                \App\Models\RiwayatAngkaKredit::create([
                                    'id_pegawai_api' => $peg->id_pegawai_api,
                                    'nomor_sk' => $ak['tknopak'] ?? '-',
                                    'tanggal_sk' => $this->parseDate($ak['tktglpak'] ?? null),
                                    'tmt_angka_kredit' => $this->parseDate($ak['tmtakrid'] ?? null),
                                    'kredit_utama' => floatval($ak['tkutama1'] ?? 0),
                                    'kredit_penunjang' => floatval($ak['tkutama2'] ?? 0),
                                    'total_kredit' => floatval($ak['tkutama3'] > 0 ? $ak['tkutama3'] : 
                                                      (($ak['tkutama1'] ?? 0) + ($ak['tkutama2'] ?? 0) > 0 ? ($ak['tkutama1'] ?? 0) + ($ak['tkutama2'] ?? 0) : 
                                                      ($ak['penilaian_ak'] ?? 0))),
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
            // Beri jeda 1 detik tiap 3 request bersamaan, supaya DB e-HRM tak crash (max_client_conn)
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
        // TAHAP 5: CEK KELENGKAPAN DOKUMEN KGB (PLACEHOLDER)
        // ============================================================
        // Todo: Implementasi pengecekan status dokumen ke API e-HRM
        // Jika dokumen sudah lengkap di e-HRM, update status tracker:
        // $tracker->update(['dokumen_terupload' => 1]);
        // $this->info('ℹ️  [5/5] Cek Dokumen KGB (Menunggu Integrasi API)...');
        $this->newLine();
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
        $currentCache['detail_text'] = $detailText;

        Cache::put('sync_status', $currentCache, now()->addMinutes(15));
    }
}