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
        'arsip_skp_2_tahun' => 'array',
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

    // Satu Pegawai punya BANYAK Riwayat SKP
    public function riwayat_skp()
    {
        return $this->hasMany(RiwayatSkp::class, 'nip', 'nip');
    }

    // Satu Pegawai punya BANYAK Riwayat Tubel
    public function riwayat_tubel()
    {
        return $this->hasMany(RiwayatTubel::class, 'nip', 'nip');
    }

    // Satu Pegawai punya BANYAK Riwayat Angka Kredit (Fase 2)
    public function riwayatAngkaKredit()
    {
        // Catatan: relasi mengacu pada struktur aktual DB di mana FK adalah id_pegawai_api
        return $this->hasMany(RiwayatAngkaKredit::class, 'id_pegawai_api', 'id_pegawai_api');
    }

    // Satu Pegawai punya BANYAK Riwayat Angka Kredit
    public function riwayat_angka_kredit()
    {
        return $this->hasMany(RiwayatAngkaKredit::class, 'id_pegawai_api', 'id_pegawai_api');
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

    // 4. Accessor Eselon
    // Mapping kd_eselon (kode angka) ke nama_eselon (label resmi)
    const ESELON_MAP = [
        '1' => 'I/a',
        '2' => 'I/b',
        '3' => 'II/a',
        '4' => 'II/b',
        '5' => 'III/a',
        '6' => 'III/b',
        '7' => 'IV/a',
        '8' => 'IV/b',
        '9' => 'V',
    ];

    /**
     * Getter otomatis: $pegawai->nama_eselon
     * Mengkonversi kd_eselon (angka) menjadi nama eselon (I.a, II.b, dst.)
     */
    public function getNamaEselonAttribute(): string
    {
        $kd = (string) ($this->kd_eselon ?? '');

        return self::ESELON_MAP[$kd] ?? '-';
    }

    // 5. Accessor Pangkat/Golongan
    const PANGKAT_MAP = [
        'I/a' => 'Juru Muda',
        'I/b' => 'Juru Muda Tk. I',
        'I/c' => 'Juru',
        'I/d' => 'Juru Tk. I',
        'II/a' => 'Pengatur Muda',
        'II/b' => 'Pengatur Muda Tk. I',
        'II/c' => 'Pengatur',
        'II/d' => 'Pengatur Tk. I',
        'III/a' => 'Penata Muda',
        'III/b' => 'Penata Muda Tk. I',
        'III/c' => 'Penata',
        'III/d' => 'Penata Tk. I',
        'IV/a' => 'Pembina',
        'IV/b' => 'Pembina Tk. I',
        'IV/c' => 'Pembina Utama Muda',
        'IV/d' => 'Pembina Utama Madya',
        'IV/e' => 'Pembina Utama',
    ];

    /**
     * Getter otomatis: $pegawai->nama_pangkat
     * Mengkonversi pangkat_golongan ("III/a") menjadi nama lengkap ("Penata Muda (III/a)")
     */
    public function getNamaPangkatAttribute(): string
    {
        $gol = $this->pangkat_golongan;
        if (! $gol || $gol == '-') {
            return '-';
        }

        return self::PANGKAT_MAP[$gol] ?? '-';
    }
}
