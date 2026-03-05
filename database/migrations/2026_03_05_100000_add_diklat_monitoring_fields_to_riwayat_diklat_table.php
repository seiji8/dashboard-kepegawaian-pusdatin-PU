<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_diklat', function (Blueprint $table) {
            $table->integer('id_diklat')->nullable()->after('id');
            $table->tinyInteger('status_diklat')->default(0)->after('file_sertifikat'); // 0=Proses, 1=Lulus
            $table->integer('kode_jenis')->nullable()->after('status_diklat');
            $table->string('jenis_diklat')->nullable()->after('kode_jenis');
            $table->string('arsip')->nullable()->after('jenis_diklat');
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_diklat', function (Blueprint $table) {
            $table->dropColumn(['id_diklat', 'status_diklat', 'kode_jenis', 'jenis_diklat', 'arsip']);
        });
    }
};
