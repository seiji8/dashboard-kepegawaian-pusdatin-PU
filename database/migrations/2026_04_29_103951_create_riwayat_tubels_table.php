<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('riwayat_tubels', function (Blueprint $table) {
            $table->id();
            $table->string('nip')->index();
            $table->string('keterangan')->nullable();
            $table->string('pendidikan')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->date('perpanjangan1_tanggal_mulai')->nullable();
            $table->date('perpanjangan2_tanggal_mulai')->nullable();
            $table->string('no_izin')->nullable();
            $table->text('arsip_izin_belajar')->nullable();
            $table->text('arsip_perpanjangan1')->nullable();
            $table->text('arsip_perpanjangan2')->nullable();
            $table->text('arsip_pengembalian')->nullable();
            $table->string('status_tubel')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_tubels');
    }
};
