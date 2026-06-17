<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatDiklat extends Model
{
    use HasFactory;

    protected $table = 'riwayat_diklat'; // Nama tabel di database

    protected $guarded = []; // Izinkan isi semua kolom

    // Relasi Balik: Diklat ini punya siapa?
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}
