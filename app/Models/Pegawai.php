<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    // 1. Konfigurasi Primary Key (Menggunakan ID dari API)
    protected $table = 'pegawai';
    protected $primaryKey = 'id_pegawai_api'; // Primary Key baru
    public $incrementing = false; // Bukan Auto Increment
    protected $keyType = 'string'; // Tipe data String/UUID

    // 2. Mass Assignment (Biar bisa langsung simpan data banyak dari API)
    protected $guarded = []; // Artinya: Semua kolom BOLEH diisi

    // 2b. Casting Tipe Data (Agar otomatis jadi Carbon / Date)
    protected $casts = [
        'tmt_cpns' => 'date',
        'tmt_pangkat_terakhir' => 'date',
        'tmt_kgb_terakhir' => 'date',
    ];

    // 3. Relasi ke Tabel Lain

    // Satu Pegawai punya BANYAK Riwayat Jabatan
    public function riwayat_jabatan()
    {
        return $this->hasMany(RiwayatJabatan::class, 'nip', 'nip');
    }

    // Satu Pegawai punya BANYAK Riwayat Diklat
    public function riwayat_diklat()
    {
        return $this->hasMany(RiwayatDiklat::class, 'nip', 'nip');
    }

    // Satu Pegawai punya BANYAK Riwayat Angka Kredit (Fase 2)
    public function riwayatAngkaKredit()
    {
        // Catatan: relasi mengacu pada struktur aktual DB di mana FK adalah nip
        return $this->hasMany(RiwayatAngkaKredit::class, 'nip', 'nip');
    }

    // Satu Pegawai punya BANYAK Riwayat Angka Kredit
    public function riwayat_angka_kredit()
    {
        return $this->hasMany(RiwayatAngkaKredit::class, 'nip', 'nip');
    }

    // Satu Pegawai punya BANYAK Status di Dashboard (KGB, JF, dll)
    public function dashboard_tracker()
    {
        return $this->hasMany(DashboardTracker::class, 'pegawai_id', 'id_pegawai_api');
    }

    // Satu Pegawai punya BANYAK Log
    public function logs()
    {
        return $this->hasMany(Logs::class, 'target_nip', 'nip');
    }
}