<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\RefMatriksJf;
use App\Models\RiwayatAngkaKredit;
use App\Models\NotifikasiRules;
use Database\Seeders\NotifikasiSeeder;
use Database\Seeders\UpdateTmtManualSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DATA ADMIN (Tanpa pakai Factory bawaan)
        User::create([
            'username' => 'superadmin',
            'nama_lengkap' => 'Super Admin Pusat',
            'email' => 'admin@pu.go.id',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
        ]);
        
        User::create([
            'username' => 'admin_tu',
            'nama_lengkap' => 'Admin Tata Usaha',
            'email' => 'tu@pu.go.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin_pegawai',
        ]);

        User::create([
            'username' => 'devy',
            'nama_lengkap' => 'Devy Wardhani',
            'email' => 'devy.wardhani@pu.go.id',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
        ]);

        User::create([
            'username' => 'hasan',
            'nama_lengkap' => 'Hasan',
            'email' => 'hasan.inf1re7@gmail.com',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
        ]);

        // 2. DATA ATURAN / KAMUS MATRIKS JAFUNG
        $this->call(\Database\Seeders\RefMatriksJfSeeder::class);



        // 3. DATA PEGAWAI DUMMY
        // Kasus KGB (H-1 Bulan)
        $tmt_kgb = now()->subYears(2)->addMonth();
        
        Pegawai::create([
            'id_pegawai_api' => '101', // ID Dummy Simple
            'nip' => '101',
            'nama' => 'Hilmi',
            'email' => 'hilmiasardan@gmail.com',
            'no_hp' => '08123456789',
            'tipe_jabatan' => 'Fungsional',
            'jabatan_saat_ini' => 'Pranata Komputer Ahli Pertama',
            'jenjang' => 'Ahli Pertama', // Berfungsi sebagai referensi kamus JF BKN
            'pangkat_golongan' => 'III/a',
            'tmt_cpns' => null,
            'tmt_kgb_terakhir' => '2024-04-01',
        ]);

        // Kasus KP (Poin Cukup) -> Dimodifikasi jadi kasus Kenaikan Jenjang (UKOM)
        $nip_siti = '102'; // ID Dummy Simple
        
        Pegawai::create([
            'id_pegawai_api' => $nip_siti,
            'nip' => $nip_siti,
            'nama' => 'Hasan',
            'email' => 'hasan.inf1re7@gmail.com',
            'no_hp' => '08987654321',
            'tipe_jabatan' => 'Fungsional',
            'jabatan_saat_ini' => 'Analis Kebijakan Ahli Muda',
            'jenjang' => 'Ahli Muda', // Berfungsi sebagai referensi kamus JF BKN
            'pangkat_golongan' => 'III/d', // Titik lompat ke Ahli Madya
            'tmt_pangkat_terakhir' => '2021-04-01',
            'tmt_kgb_terakhir' => '2024-04-01',
        ]);
        
        // 4. DATA DUMMY ANGKA KREDIT AWAL
        RiwayatAngkaKredit::create([
            'id_pegawai_api' => '102', // Hasan
            'nomor_sk' => 'SK-DUMMY-001',
            'tanggal_sk' => '2022-12-01',
            'total_kredit' => 210, // Lebih dari syarat kumulatif jenjang (200 AK) untuk naik ke Ahli Madya
            'jabatan_saat_penilaian' => 'Analis Kebijakan Ahli Muda'
        ]);

        RiwayatAngkaKredit::create([
            'id_pegawai_api' => '104', // Bimo (Kekurangan AK)
            'nomor_sk' => 'SK-DUMMY-002',
            'tanggal_sk' => '2023-12-01',
            'total_kredit' => 110, // Kurang dari 50
            'jabatan_saat_penilaian' => 'Pranata Komputer Ahli Pertama'
        ]);

        RiwayatAngkaKredit::create([
            'id_pegawai_api' => '103', // Raissa
            'nomor_sk' => 'SK-DUMMY-003',
            'tanggal_sk' => '2023-12-01',
            'total_kredit' => 210, // Kurang dari 50
            'jabatan_saat_penilaian' => 'Pranata Komputer Ahli Pertama'
        ]);

        // 5. Pegawai Tambahan (Untuk test fitur KGB)
        // KGB Mendekati: tmt_kgb + 2 tahun = Maret 2026 (H-1 bulan)
        Pegawai::create([
            'id_pegawai_api' => '103',
            'nip' => '103',
            'nama' => 'Raissa',
            'email' => 'ahmad.budiman@test.go.id',
            'tipe_jabatan' => 'Fungsional',
            'jabatan_saat_ini' => 'Analis Kebijakan Ahli Muda',
            'jenjang' => 'Ahli Muda', // Berfungsi sebagai referensi kamus JF BKN
            'pangkat_golongan' => 'III/d',
            'tmt_kgb_terakhir' => '2024-03-01',
        ]);

        // KGB Mendekati: tmt_kgb + 2 tahun = April 2026 (H-2 bulan)
        Pegawai::create([
            'id_pegawai_api' => '104',
            'nip' => '104',
            'nama' => 'Bimo',
            'email' => 'siti.nurhaliza@test.go.id',
            'tipe_jabatan' => 'Fungsional',
            'jabatan_saat_ini' => 'Pranata Komputer Ahli Pertama',
            'jenjang' => 'Ahli Pertama',
            'pangkat_golongan' => 'III/d',
            'tmt_kgb_terakhir' => '2024-04-01',
        ]);

        // KGB Usulan: tmt_kgb + 2 tahun = Januari 2026 (sudah lewat)
        Pegawai::create([
            'id_pegawai_api' => '105',
            'nip' => '105',
            'nama' => 'Eza Aditya',
            'email' => 'ezaadityanugroho1@gmail.com',
            'tipe_jabatan' => 'Struktural',
            'jabatan_saat_ini' => 'Kepala Seksi Perencanaan',
            'kd_eselon' => '7', // Eselon
            //  (Max III/d)
            'jenjang' => null,
            'pangkat_golongan' => 'III/c', // Kurang satu tingkat dari batas Mentok Eselon
            'tmt_pangkat_terakhir' => '2022-04-01', // Target 4thn = Apr 2026, window H-2bln = Feb 2026
            'tmt_struktural' => '2023-01-01', // Sudah > 1 Tahun
            'tmt_kgb_terakhir' => '2024-01-01',
        ]);

        // 4. Seeder Notifikasi (Professional Wording)
        $this->call(NotifikasiSeeder::class);

        // =====================================
        // KASUS STUDY KP STRUKTURAL
        // =====================================

        // Kasus 1: KP Struktural - Aman (Sudah di Puncak Golru)
        // Eselon 3, Puncak di IV/d
        Pegawai::create([
            'id_pegawai_api' => '201',
            'nip' => '201',
            'nama' => 'Struktural Aman Puncak',
            'email' => 'struktural1@test.go.id',
            'tipe_jabatan' => 'Struktural',
            'jabatan_saat_ini' => 'Kepala Bagian Keuangan',
            'kd_eselon' => '3', // Eselon 3 -> max IV/d
            'pangkat_golongan' => 'IV/d',
            'tmt_pangkat_terakhir' => '2022-04-01',
            'tmt_struktural' => '2023-01-01',
            'tmt_kgb_terakhir' => '2024-01-01',
        ]);

        // Kasus 2: KP Struktural - Aman (Belum Masuk Waktu H-2 Bulan dari 4 Tahun)
        Pegawai::create([
            'id_pegawai_api' => '202',
            'nip' => '202',
            'nama' => 'Struktural Aman Waktu',
            'email' => 'struktural2@test.go.id',
            'tipe_jabatan' => 'Struktural',
            'jabatan_saat_ini' => 'Kepala Bidang Perencanaan',
            'kd_eselon' => '3',
            'pangkat_golongan' => 'IV/c', // Belum Puncak IV/d
            'tmt_pangkat_terakhir' => '2025-01-01', // Target 2029
            'tmt_struktural' => '2025-02-01',
            'tmt_kgb_terakhir' => '2026-01-01',
        ]);

        // Kasus 3: KP Struktural - Usulan (Fallback TMT Pangkat Null)
        Pegawai::create([
            'id_pegawai_api' => '203',
            'nip' => '203',
            'nama' => 'Struktural Usulan Fallback',
            'email' => 'struktural3@test.go.id',
            'tipe_jabatan' => 'Struktural',
            'jabatan_saat_ini' => 'Kepala Subbagian Umum',
            'kd_eselon' => '4', // Eselon 4 -> max IV/c
            'pangkat_golongan' => 'IV/b',
            'tmt_pangkat_terakhir' => null, // Uji coba jika TMT tidak ditarik dari API
            'tmt_struktural' => '2024-01-01', // Tapi masa jabatan struktural > 1 Tahun
            'tmt_kgb_terakhir' => '2025-01-01', 
        ]);

        // =====================================
        // KASUS STUDY KP REGULER (KHUSUS PELAKSANA)
        // =====================================

        // Kasus 1: KP Reguler - Aman (Masa Pangkat < 4 Tahun)
        Pegawai::create([
            'id_pegawai_api' => '301',
            'nip' => '301',
            'nama' => 'Pelaksana Aman Waktu',
            'email' => 'pelaksana1@test.go.id',
            'tipe_jabatan' => 'Pelaksana', // Wajib Pelaksana
            'jabatan_saat_ini' => 'Pengadministrasi Perkantoran',
            'pangkat_golongan' => 'II/c',
            'tmt_pangkat_terakhir' => '2024-01-01', // Baru 2 Tahun (Target 2028)
            'tmt_kgb_terakhir' => '2025-01-01',
        ]);

        // Kasus 2: KP Reguler - Usulan (Masa Pangkat >= 4 Tahun)
        Pegawai::create([
            'id_pegawai_api' => '302',
            'nip' => '302',
            'nama' => 'Pelaksana Usulan Waktu',
            'email' => 'pelaksana2@test.go.id',
            'tipe_jabatan' => 'Pelaksana',
            'jabatan_saat_ini' => 'Analis Data',
            'pangkat_golongan' => 'III/a',
            'tmt_pangkat_terakhir' => '2021-04-01', // Sudah ~5 Tahun
            'tmt_kgb_terakhir' => '2025-01-01',
        ]);

        // 5. Update TMT Manual (jika pegawai ada)
        $this->call(UpdateTmtManualSeeder::class);

    }
}