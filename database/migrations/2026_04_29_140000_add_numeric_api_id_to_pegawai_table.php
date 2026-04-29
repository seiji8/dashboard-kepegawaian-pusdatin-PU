<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            // Menyimpan ID numerik dari API e-HRM (berbeda dengan id_pegawai_api yang berisi NIP)
            // Dibutuhkan untuk query ke endpoint /riw/angka-kredit?filter=id_pegawai={numeric_id}
            $table->unsignedBigInteger('numeric_api_id')->nullable()->after('id_pegawai_api');
        });
    }

    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropColumn('numeric_api_id');
        });
    }
};
