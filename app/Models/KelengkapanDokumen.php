<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelengkapanDokumen extends Model
{
    use HasFactory;

    protected $table = 'kelengkapan_dokumen';
    protected $guarded = [];

    // Relasi Balik: Dokumen ini syarat untuk tracker yang mana?
    public function tracker()
    {
        return $this->belongsTo(DashboardTracker::class, 'dashboard_tracker_id', 'id');
    }
    
    // Relasi Opsional: Dokumen ini milik pegawai siapa?
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}