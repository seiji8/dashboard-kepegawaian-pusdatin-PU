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
        // 1. DATA ADMIN
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

        // 3. Seeder Notifikasi (Professional Wording)
        $this->call(NotifikasiSeeder::class);

        // 4. DATA DUMMY PEGAWAI UNTUK TESTING
        if (app()->environment('local')) {
            $this->call(\Database\Seeders\DummyKJSeeder::class);
            $this->call(\Database\Seeders\DummyStructuralSeeder::class);
            $this->call(\Database\Seeders\UkomDummySeeder::class);
        }
    }
}