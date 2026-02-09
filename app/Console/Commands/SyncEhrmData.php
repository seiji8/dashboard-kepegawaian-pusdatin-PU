<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Pegawai;
use Carbon\Carbon;

class SyncEhrmData extends Command
{
    // INI KUNCINYA: Nama perintah yang nanti dipanggil
    protected $signature = 'sync:ehrm';

    protected $description = 'Sinkronisasi Data Pegawai dari API e-HRM KemenPU';

    public function handle()
    {
        $this->info('🚀 Memulai proses sinkronisasi...');

        // 1. Ambil Kredensial dari .env
        $baseUrl = env('EHRM_BASE_URL');
        $apiKey  = env('EHRM_API_KEY');
        $email   = env('EHRM_USER_EMAIL');
        $password = env('EHRM_USER_PASS');

        // 2. Login ke API Gateway
        $this->info('🔑 Sedang login...');
        $loginResponse = Http::post("$baseUrl/user/login", [
            'email' => $email,
            'password' => $password,
        ]);

        if ($loginResponse->failed()) {
            $this->error('❌ Gagal Login! Cek .env Anda.');
            return;
        }

        $token = $loginResponse->json()['session_token'] ?? null;
        if (!$token) {
            $this->error('❌ Token tidak ditemukan dalam respon login.');
            return;
        }

        // 3. Tarik Data Pegawai
        $this->info('⬇️  Mengunduh data pegawai...');
        $pegawaiResponse = Http::withHeaders([
            'X-DreamFactory-Api-Key' => $apiKey,
            'X-DreamFactory-Session-Token' => $token,
        ])->get("$baseUrl/v1/ehrm/pegawai");

        if ($pegawaiResponse->failed()) {
            $this->error('❌ Gagal ambil data pegawai.');
            return;
        }

        // Ambil array data (sesuaikan dengan struktur JSON API aslinya)
        $dataPegawai = $pegawaiResponse->json()['resource'] ?? $pegawaiResponse->json();

        // 4. Simpan ke Database
        $bar = $this->output->createProgressBar(count($dataPegawai));
        $bar->start();

        foreach ($dataPegawai as $item) {
            Pegawai::updateOrCreate(
                ['nip' => $item['nip']], // Kunci Update (Primary Key)
                [
                    'nama' => $item['nama'] ?? 'Tanpa Nama',
                    'email' => $item['email'] ?? null,
                    'no_hp' => $item['no_hp'] ?? null,
                    
                    'jabatan_saat_ini' => $item['jabatan_nama'] ?? null,
                    'pangkat_saat_ini' => $item['pangkat_golongan'] ?? null,
                    
                    // Parse Tanggal (Pastikan format Y-m-d)
                    'tmt_cpns' => $this->parseDate($item['tmt_cpns'] ?? null),
                    'tmt_pangkat_terakhir' => $this->parseDate($item['tmt_pangkat'] ?? null),
                    'tmt_kgb_terakhir' => $this->parseDate($item['tmt_kgb'] ?? null),
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('🎉 Sinkronisasi Selesai!');
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