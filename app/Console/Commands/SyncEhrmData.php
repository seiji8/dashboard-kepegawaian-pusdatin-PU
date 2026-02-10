<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Pegawai;
use Carbon\Carbon;

class SyncEhrmData extends Command
{
    protected $signature = 'ehrm:sync';
    protected $description = 'Sinkronisasi Data Pegawai dari API e-HRM KemenPU';

    public function handle()
    {
        $this->info('🚀 Memulai proses sinkronisasi Lengkap...');

        // 1. Ambil Kredensial dari .env
        $baseUrl = env('EHRM_BASE_URL');
        $apiKey  = env('EHRM_API_KEY');
        $email   = env('EHRM_USER_EMAIL');
        $password = env('EHRM_USER_PASS');

        // 2. Login ke API Gateway
        $this->info('🔑 Sedang login...');
        $loginResponse = Http::withHeaders([
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
        $pegawaiResponse = Http::withHeaders([
            'X-DreamFactory-Api-Key' => $apiKey,
            'X-DreamFactory-Session-Token' => $token,
        ])->get("$baseUrl/v1/ehrm/pegawai");

        if ($pegawaiResponse->failed()) {
            $this->error('❌ Gagal ambil data pegawai.');
            return;
        }

        $dataPegawai = $pegawaiResponse->json()['resource'] ?? $pegawaiResponse->json();
        $bar = $this->output->createProgressBar(count($dataPegawai));
        $bar->start();

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
                    
                    // === [PENTING] BAGIAN INI KITA KOMENTARI DULU ===
                    // Tujuannya: Agar data tanggal manual tidak tertimpa NULL dari API
                    // 'tmt_cpns' => $this->parseDate($item['tmt_cpns'] ?? null),
                    // 'tmt_pangkat_terakhir' => $this->parseDate($item['tmt_pangkat'] ?? null),
                    // 'tmt_kgb_terakhir' => $this->parseDate($item['tmt_kgb'] ?? null), 
                    // ================================================
                ]
            );
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();


        // ============================================================
        // TAHAP 2: RIWAYAT JABATAN
        // ============================================================
        $this->info('⬇️  [2/4] Mengunduh Riwayat Jabatan...');
        $riwResponse = Http::withHeaders([
            'X-DreamFactory-Api-Key' => $apiKey,
            'X-DreamFactory-Session-Token' => $token,
        ])->get("$baseUrl/v1/ehrm/riw");

        if ($riwResponse->successful()) {
            $dataRiw = $riwResponse->json()['resource'] ?? $riwResponse->json();
            $bar2 = $this->output->createProgressBar(count($dataRiw));
            $bar2->start();

            foreach ($dataRiw as $item) {
                $apiId = $item['id'] ?? null;
                if (!$apiId) continue;

                $pegawai = Pegawai::where('id_pegawai_api', $apiId)->first();
                if (!$pegawai) continue;

                if (isset($item['riwjabatan']) && is_array($item['riwjabatan'])) {
                    // Update Tipe Jabatan di Pegawai (ambil data terbaru)
                    if (count($item['riwjabatan']) > 0) {
                        $latest = $item['riwjabatan'][0];
                        $pegawai->update(['tipe_jabatan' => $latest['tipejabatan'] ?? null]);
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
                $bar2->advance();
            }
            $bar2->finish();
        } else {
            $this->error('❌ Gagal ambil Riwayat Jabatan.');
        }
        $this->newLine();


        // ============================================================
        // TAHAP 3: RIWAYAT ANGKA KREDIT
        // ============================================================
        $this->info('⬇️  [3/4] Mengunduh Riwayat Angka Kredit...');
        $akResponse = Http::withHeaders([
            'X-DreamFactory-Api-Key' => $apiKey,
            'X-DreamFactory-Session-Token' => $token,
        ])->get("$baseUrl/v1/ehrm/riw/angka-kredit");

        if ($akResponse->successful()) {
            $dataAK = $akResponse->json()['resource'] ?? $akResponse->json();
            $bar3 = $this->output->createProgressBar(count($dataAK));
            $bar3->start();

            foreach ($dataAK as $ak) {
                $idPegawaiApi = $ak['id_pegawai'] ?? null;
                if (!$idPegawaiApi) continue;

                $pegawai = Pegawai::where('id_pegawai_api', $idPegawaiApi)->first();
                
                if ($pegawai) {
                    \App\Models\RiwayatAngkaKredit::updateOrCreate(
                        [
                            'nip' => $pegawai->nip,
                            'nomor_sk' => $ak['tknopak'] ?? '-',
                        ],
                        [
                            'tanggal_sk' => $this->parseDate($ak['tktglpak'] ?? null),
                            'tmt_angka_kredit' => $this->parseDate($ak['tmtakrid'] ?? null),
                            'kredit_utama' => $ak['tkutama1'] ?? 0,
                            'kredit_penunjang' => $ak['tkutama2'] ?? 0,
                            'total_kredit' => $ak['tkutama3'] ?? 0,
                            'jabatan_saat_penilaian' => $ak['tk_keterangan'] ?? null,
                        ]
                    );
                }
                $bar3->advance();
            }
            $bar3->finish();
        }
        $this->newLine();

        // ============================================================
        // TAHAP 4: RIWAYAT DIKLAT
        // ============================================================
        $this->info('⬇️  [4/4] Mengunduh Riwayat Diklat (Per NIP)...');
        
        $allPegawai = Pegawai::select('nip', 'nama')->get();
        $bar4 = $this->output->createProgressBar($allPegawai->count());
        $bar4->start();

        foreach ($allPegawai as $peg) {
            $nip = $peg->nip;
            
            $diklatResp = Http::withHeaders([
                'X-DreamFactory-Api-Key' => $apiKey,
                'X-DreamFactory-Session-Token' => $token,
            ])->get("$baseUrl/v1/ehrm/riw/diklat", [
                'nip' => $nip
            ]);

            if ($diklatResp->successful()) {
                $listDiklat = $diklatResp->json()['resource'] ?? $diklatResp->json();
                
                if (is_array($listDiklat)) {
                    foreach ($listDiklat as $d) {
                        \App\Models\RiwayatDiklat::updateOrCreate(
                            [
                                'nip' => $nip,
                                'nama_diklat' => $d['nmdiklat'] ?? $d['nama_diklat'] ?? '-',
                                'nomor_sertifikat' => $d['no_sertifikat'] ?? $d['nosertifikat'] ?? '-',
                            ],
                            [
                                'penyelenggara' => $d['penyelenggara'] ?? null,
                                'tanggal_mulai' => $this->parseDate($d['tgl_mulai'] ?? null),
                                'tanggal_selesai' => $this->parseDate($d['tgl_selesai'] ?? null),
                                'jumlah_jam' => $d['jam'] ?? null,
                                'tanggal_sertifikat' => $this->parseDate($d['tgl_sertifikat'] ?? null),
                                'file_sertifikat' => $d['file'] ?? null,
                            ]
                        );
                    }
                }
            }
            $bar4->advance();
        }
        $bar4->finish();

        $this->newLine();
        $this->info('🎉 Sinkronisasi LENGKAP Selesai!');
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
}