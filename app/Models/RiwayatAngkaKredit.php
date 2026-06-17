<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatAngkaKredit extends Model
{
    use HasFactory;

    protected $table = 'riwayat_angka_kredit';

    protected $guarded = [];

    // Relasi Balik: Nilai ini punya siapa?
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai_api', 'id_pegawai_api');
    }
}
