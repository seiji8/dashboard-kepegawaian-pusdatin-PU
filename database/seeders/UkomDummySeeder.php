<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;
use App\Models\DashboardTracker;
use Carbon\Carbon;

class UkomDummySeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // DUMMY 1: UKOM BIASA
        // Jenjang: Ahli Pertama → masuk sub-tab "UKOM Biasa"
        // ============================================================
        $dummy1 = Pegawai::updateOrCreate(
            ['id_pegawai_api' => 'DUMMY-UKOM-001'],
            [
                'nip'             => '199001010001001001',
                'nama'            => '[TEST] Budi Santoso (UKOM Biasa)',
                'email'           => 'sanfaedloni@students.unnes.ac.id',
                'jabatan_saat_ini' => 'Analis Kebijakan Ahli Pertama',
                'tipe_jabatan'    => 'fungsional',
                'jenjang'         => 'Ahli Pertama',
                'pangkat_golongan' => 'Penata Muda / III-a',
                'tmt_pangkat_terakhir' => Carbon::now()->subYears(3)->format('Y-m-d'),
                'arsip_skp_2_tahun' => json_encode(['skp_2023.pdf', 'skp_2024.pdf']),
            ]
        );

        DashboardTracker::updateOrCreate(
            [
                'pegawai_id' => 'DUMMY-UKOM-001',
                'kategori'   => 'UKOM',
            ],
            [
                'status_saat_ini'   => 'Usulan',
                'keterangan'        => 'Pegawai masuk kategori Uji Kompetensi - DATA DUMMY TESTING',
                'dokumen_total'     => 2,
                'dokumen_terupload' => 2,
                'tanggal_target'    => Carbon::now()->format('Y-m-d'),
                'notified_at'       => Carbon::now(),
                'dikonfirmasi_at'   => null,
            ]
        );

        // ============================================================
        // DUMMY 2: UKOM MADYA
        // Jenjang: Ahli Muda → masuk sub-tab "UKOM Madya"
        // ============================================================
        $dummy2 = Pegawai::updateOrCreate(
            ['id_pegawai_api' => 'DUMMY-UKOM-002'],
            [
                'nip'             => '199002020002002002',
                'nama'            => '[TEST] Siti Rahayu (UKOM Madya)',
                'email'           => 'sanfaedloni@students.unnes.ac.id',
                'jabatan_saat_ini' => 'Analis Kebijakan Ahli Muda',
                'tipe_jabatan'    => 'fungsional',
                'jenjang'         => 'Ahli Muda',
                'pangkat_golongan' => 'Penata / III-c',
                'tmt_pangkat_terakhir' => Carbon::now()->subYears(3)->format('Y-m-d'),
                'arsip_skp_2_tahun' => json_encode(['skp_2023.pdf', 'skp_2024.pdf']),
            ]
        );

        DashboardTracker::updateOrCreate(
            [
                'pegawai_id' => 'DUMMY-UKOM-002',
                'kategori'   => 'UKOM',
            ],
            [
                'status_saat_ini'   => 'Usulan',
                'keterangan'        => 'Pegawai masuk kategori Uji Kompetensi (Madya) - DATA DUMMY TESTING',
                'dokumen_total'     => 2,
                'dokumen_terupload' => 2,
                'tanggal_target'    => Carbon::now()->format('Y-m-d'),
                'notified_at'       => Carbon::now(),
                'dikonfirmasi_at'   => null,
            ]
        );

        $this->command->info('✅ Dummy UKOM (Biasa & Madya) berhasil dibuat!');
        $this->command->info('   → [TEST] Budi Santoso  = UKOM Biasa   (jenjang: Ahli Pertama)');
        $this->command->info('   → [TEST] Siti Rahayu   = UKOM Madya   (jenjang: Ahli Muda)');
        $this->command->newLine();
        $this->command->warn('⚠️  Ingat: Hapus data dummy ini setelah selesai testing!');
        $this->command->warn('   Jalankan: docker compose exec app php artisan db:seed --class=CleanUkomDummySeeder');
    }
}
