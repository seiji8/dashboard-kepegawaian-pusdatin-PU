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
        Schema::create('riwayat_angka_kredit', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 20); // Foreign Key
            $table->year('tahun');
            $table->enum('triwulan', ['1', '2', '3', '4']);
            $table->float('nilai_konversi'); 
            $table->string('keterangan_skp')->nullable();
            $table->boolean('is_processed')->default(false);
            
            // Relasi
            $table->foreign('nip')->references('nip')->on('pegawai')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_angka_kredit');
    }
};
