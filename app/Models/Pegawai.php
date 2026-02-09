<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    // 1. Konfigurasi Primary Key (Wajib karena pakai NIP)
    protected $table = 'pegawai';
    protected $primaryKey = 'nip';
    public $incrementing = false; // Karena NIP bukan angka urut (1,2,3)
    protected $keyType = 'string'; // Tipe data NIP adalah string

    // 2. Mass Assignment (Biar bisa langsung simpan data banyak dari API)
    protected $guarded = []; // Artinya: Semua kolom BOLEH diisi

    // 3. Relasi ke Tabel Lain

    // Satu Pegawai punya BANYAK Riwayat Diklat
    public function riwayat_diklat()
    {
        return $this->hasMany(RiwayatDiklat::class, 'nip', 'nip');
    }

    // Satu Pegawai punya BANYAK Riwayat Angka Kredit
    public function riwayat_angka_kredit()
    {
        return $this->hasMany(RiwayatAngkaKredit::class, 'nip', 'nip');
    }

    // Satu Pegawai punya BANYAK Status di Dashboard (KGB, JF, dll)
    public function dashboard_tracker()
    {
        return $this->hasMany(DashboardTracker::class, 'nip', 'nip');
    }

    // Satu Pegawai punya BANYAK Log
    public function logs()
    {
        return $this->hasMany(Logs::class, 'target_nip', 'nip');
    }
}