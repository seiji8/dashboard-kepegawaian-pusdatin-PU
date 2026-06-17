<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class RiwayatTubel extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'perpanjangan1_tanggal_mulai' => 'date',
        'perpanjangan2_tanggal_mulai' => 'date',
    ];

    /** Relasi ke Pegawai lewat NIP */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }

    /**
     * Tanggal selesai efektif — gunakan perpanjangan terakhir jika ada,
     * fallback ke tanggal_selesai utama.
     */
    public function getTanggalSelesaiEfektifAttribute(): ?Carbon
    {
        return $this->perpanjangan2_tanggal_mulai
            ?? $this->perpanjangan1_tanggal_mulai
            ?? $this->tanggal_selesai;
    }
}
