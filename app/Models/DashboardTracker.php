<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardTracker extends Model
{
    use HasFactory;

    protected $table = 'dashboard_tracker';
    protected $guarded = [];

    // Relasi 1: Status ini milik pegawai siapa?
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id_pegawai_api');
    }

    // Relasi 2: Status ini punya dokumen apa saja yang harus diupload?
    public function kelengkapan_dokumen()
    {
        return $this->hasMany(KelengkapanDokumen::class, 'dashboard_tracker_id', 'id');
    }
}