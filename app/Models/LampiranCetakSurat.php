<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LampiranCetakSurat extends Model
{
    use HasFactory;

    protected $table = 'lampiran_cetak_surat';

    protected $fillable = [
        'dashboard_tracker_id',
        'nip',
        'nama_dokumen',
        'judul_lampiran',
        'file_path',
        'mime_type',
        'urutan',
        'halaman_cetak',
        'ukuran_bytes',
    ];
}
