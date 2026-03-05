<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_diklat', function (Blueprint $table) {
            $table->string('kode_jenis', 10)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_diklat', function (Blueprint $table) {
            $table->integer('kode_jenis')->nullable()->change();
        });
    }
};
