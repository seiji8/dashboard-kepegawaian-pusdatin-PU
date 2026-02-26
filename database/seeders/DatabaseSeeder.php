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

        // Kasus KP (Poin Cukup)
        $nip_siti = '102'; // ID Dummy Simple
        
        Pegawai::create([
            'id_pegawai_api' => $nip_siti,
            'nip' => $nip_siti,
            'nama' => 'Hasan',
            'email' => 'hasan.inf1re7@gmail.com',
            'no_hp' => '08987654321',
            'tipe_jabatan' => 'Fungsional',
            'jabatan_saat_ini' => 'Pranata Komputer Ahli Pertama',
            'jenjang' => 'Ahli Pertama', // Berfungsi sebagai referensi kamus JF BKN
            'pangkat_golongan' => 'III/a',
            'tmt_pangkat_terakhir' => '2022-01-01',
            'tmt_kgb_terakhir' => '2026-02-01',
        ]);
        
        // 4. DATA DUMMY ANGKA KREDIT AWAL
        \App\Models\RiwayatAngkaKredit::create([
            'id_pegawai_api' => '102', // Hasan
            'nomor_sk' => 'SK-DUMMY-001',
            'tanggal_sk' => '2022-12-01',
            'total_kredit' => 55, // Lebih dari target 50
            'jabatan_saat_penilaian' => 'Pranata Komputer Ahli Pertama'
        ]);

        \App\Models\RiwayatAngkaKredit::create([
            'id_pegawai_api' => '104', // Bimo (Kekurangan AK)
            'nomor_sk' => 'SK-DUMMY-002',
            'tanggal_sk' => '2023-12-01',
            'total_kredit' => 40, // Kurang dari 50
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
            'pangkat_golongan' => 'III/c',
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
            'pangkat_golongan' => 'III/a',
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
            'jenjang' => null,
            'pangkat_golongan' => 'III/d',
            'tmt_kgb_terakhir' => '2024-01-01',
        ]);

        // 4. Seeder Notifikasi (Professional Wording)
        $this->call(NotifikasiSeeder::class);

        // 5. Update TMT Manual (jika pegawai ada)
        $this->call(UpdateTmtManualSeeder::class);

    }
}