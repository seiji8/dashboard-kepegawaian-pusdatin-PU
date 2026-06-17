<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefMatriksJf extends Model
{
    use HasFactory;

    protected $table = 'ref_matriks_jf';

    protected $guarded = [];

    // Biasanya tidak butuh relasi direct, karena ini tabel referensi (kamus).
    // Nanti dipanggil pakai query logic di Controller.
}
