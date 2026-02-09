<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotifikasiRules extends Model
{
    use HasFactory;

    protected $table = 'notifikasi_rules';
    protected $guarded = [];
    
    // Relasi: Siapa admin yang terakhir update rule ini?
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}