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

        NotifikasiRules::create([
            'kategori' => 'KGB',
            'template_pesan' => 'Yth. {nama}, TMT KGB Anda jatuh pada {tanggal_target}. Segera proses berkas.',
            'interval_hari' => 1,
        ]);

        NotifikasiRules::create([
            'kategori' => 'KP_Jafung',
            'template_pesan' => 'Selamat {nama}, Angka Kredit Anda ({poin}) sudah mencukupi untuk naik pangkat ke {next_pangkat}.',
            'interval_hari' => 1,
        ]);

        // 3. DATA PEGAWAI DUMMY
        // Kasus KGB (H-1 Bulan)
        $tmt_kgb = now()->subYears(2)->addMonth(1);
        
        Pegawai::create([
            'id_pegawai_api' => \Illuminate\Support\Str::uuid()->toString(), // Dummy UUID
            'nip' => '199001012022011001',
            'nama' => 'Hilmi Jim',
            'email' => 'hilmiasardan@gmail.com',
            'no_hp' => '08123456789',
            'jabatan_saat_ini' => 'Pranata Komputer Ahli Pertama',
            'pangkat_golongan' => 'III/a',
            'tmt_cpns' => null,
            'tmt_kgb_terakhir' => '2024-02-01',
        ]);

        // Kasus KP (Poin Cukup)
        $nip_siti = '199505052020012001';
        
        Pegawai::create([
            'id_pegawai_api' => \Illuminate\Support\Str::uuid()->toString(), // Dummy UUID
            'nip' => $nip_siti,
            'nama' => 'Raissa Cantik',
            'email' => 'raissaakhdiyan@gmail.com',
            'no_hp' => '08987654321',
            'jabatan_saat_ini' => 'Pranata Komputer Ahli Pertama',
            'pangkat_golongan' => 'III/a',
            'tmt_pangkat_terakhir' => '2022-01-01',
            'tmt_kgb_terakhir' => '2024-03-01',
        ]);

        RiwayatAngkaKredit::create([
            'nip' => $nip_siti,
            'nomor_sk' => 'SK-DUMMY-001',
            'tanggal_sk' => '2022-12-01',
            'total_kredit' => 55, // Langsung tembak 55 biar lolos
            'jabatan_saat_penilaian' => 'Pranata Komputer Ahli Pertama',
        ]);

        // 4. Update TMT Manual (jika pegawai ada)
        $this->call(UpdateTmtManualSeeder::class);
    }
}