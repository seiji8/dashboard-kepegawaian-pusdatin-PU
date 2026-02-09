<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    use HasFactory;

    protected $table = 'logs';
    protected $guarded = [];

    // Relasi 1: Siapa admin yang melakukan aksi? (Bisa null jika sistem)
    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Relasi 2: Siapa pegawai yang jadi target aksi/notif?
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'target_nip', 'nip');
    }
}