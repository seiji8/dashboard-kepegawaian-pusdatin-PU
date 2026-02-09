<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\RefMatriksJf;
use App\Models\RiwayatAngkaKredit;
use App\Models\NotifikasiRules;

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

        // 2. DATA ATURAN
        RefMatriksJf::create([
            'jabatan_asal' => 'Pranata Komputer Ahli Pertama',
            'pangkat_asal' => 'III/a',
            'target_ak' => 50,
            'syarat_tahun_min' => 2,
            'next_pangkat' => 'III/b',
            'next_jenjang' => 'Pranata Komputer Ahli Pertama',
            'is_naik_jenjang' => false,
        ]);



        // 3. DATA PEGAWAI DUMMY
        // Kasus KGB (H-1 Bulan)
        $tmt_kgb = now()->subYears(2)->addMonth(1);
        
        Pegawai::create([
            'nip' => '199001012022011001',
            'nama' => 'Budi Santoso (Target KGB)',
            'email' => 'budi@pu.go.id',
            'no_hp' => '08123456789',
            'jabatan_saat_ini' => 'Pranata Komputer Ahli Pertama',
            'pangkat_saat_ini' => 'III/a',
            'tmt_cpns' => $tmt_kgb,
            'tmt_kgb_terakhir' => null,
        ]);

        // Kasus KP (Poin Cukup)
        $nip_siti = '199505052020012001';
        
        Pegawai::create([
            'nip' => $nip_siti,
            'nama' => 'Siti Aminah (Target KP)',
            'email' => 'siti@pu.go.id',
            'no_hp' => '08987654321',
            'jabatan_saat_ini' => 'Pranata Komputer Ahli Pertama',
            'pangkat_saat_ini' => 'III/a',
            'tmt_pangkat_terakhir' => '2022-01-01',
        ]);

        RiwayatAngkaKredit::create([
            'nip' => $nip_siti,
            'tahun' => 2022,
            'triwulan' => '4',
            'nilai_konversi' => 55, // Langsung tembak 55 biar lolos
            'keterangan_skp' => 'SKP 2022-2023',
        ]);

        // 4. Seeder Notifikasi (Professional Wording)
        $this->call(NotifikasiSeeder::class);
    }
}