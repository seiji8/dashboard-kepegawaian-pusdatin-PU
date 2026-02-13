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
        $tmt_kgb = now()->subYears(2)->addMonth();
        
        Pegawai::create([
            'id_pegawai_api' => \Illuminate\Support\Str::uuid()->toString(), // Dummy UUID
            'nip' => '199001012022011001',
            'nama' => 'Hilmi',
            'email' => 'hilmiasardan@gmail.com',
            'no_hp' => '08123456789',
            'jabatan_saat_ini' => 'Pranata Komputer Ahli Pertama',
            'pangkat_golongan' => 'III/a',
            'tmt_cpns' => null,
            'tmt_kgb_terakhir' => '2024-04-01',
        ]);

        // Kasus KP (Poin Cukup)
        $nip_siti = '199505052020012001';
        
        Pegawai::create([
            'id_pegawai_api' => \Illuminate\Support\Str::uuid()->toString(), // Dummy UUID
            'nip' => $nip_siti,
            'nama' => 'Hasan',
            'email' => 'hasan.inf1re7@gmail.com',
            'no_hp' => '08987654321',
            'jabatan_saat_ini' => 'Pranata Komputer Ahli Pertama',
            'pangkat_golongan' => 'III/a',
            'tmt_pangkat_terakhir' => '2022-01-01',
            'tmt_kgb_terakhir' => '2026-02-01',
        ]);

        RiwayatAngkaKredit::create([
            'nip' => $nip_siti,
            'nomor_sk' => 'SK-DUMMY-001',
            'tanggal_sk' => '2022-12-01',
            'total_kredit' => 55, // Langsung tembak 55 biar lolos
            'jabatan_saat_penilaian' => 'Pranata Komputer Ahli Pertama',
        ]);

        // Pegawai Tambahan (Untuk test fitur KGB)
        // KGB Mendekati: tmt_kgb + 2 tahun = Maret 2026 (H-1 bulan)
        Pegawai::create([
            'id_pegawai_api' => \Illuminate\Support\Str::uuid()->toString(),
            'nip' => '198201012010011001',
            'nama' => 'Raissa',
            'email' => 'ahmad.budiman@test.go.id',
            'jabatan_saat_ini' => 'Analis Kebijakan Ahli Muda',
            'pangkat_golongan' => 'III/c',
            'tmt_kgb_terakhir' => '2024-03-01',
        ]);

        // KGB Mendekati: tmt_kgb + 2 tahun = April 2026 (H-2 bulan)
        Pegawai::create([
            'id_pegawai_api' => \Illuminate\Support\Str::uuid()->toString(),
            'nip' => '199212152015022001',
            'nama' => 'Bimo',
            'email' => 'siti.nurhaliza@test.go.id',
            'jabatan_saat_ini' => 'Pranata Komputer Ahli Pertama',
            'pangkat_golongan' => 'III/a',
            'tmt_kgb_terakhir' => '2024-04-01',
        ]);

        // KGB Usulan: tmt_kgb + 2 tahun = Januari 2026 (sudah lewat)
        Pegawai::create([
            'id_pegawai_api' => \Illuminate\Support\Str::uuid()->toString(),
            'nip' => '198507082008011001',
            'nama' => 'Eza Aditya',
            'email' => 'ezaadityanugroho1@gmail.com',
            'jabatan_saat_ini' => 'Kepala Seksi Perencanaan',
            'pangkat_golongan' => 'III/d',
            'tmt_kgb_terakhir' => '2024-01-01',
        ]);

        // 4. Seeder Notifikasi (Professional Wording)
        $this->call(NotifikasiSeeder::class);

        // 5. Update TMT Manual (jika pegawai ada)
        $this->call(UpdateTmtManualSeeder::class);
    }
}