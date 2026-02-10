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
            $table->string('nomor_sk')->nullable(); // tknopak
            $table->date('tanggal_sk')->nullable(); // tktglpak
            $table->date('tmt_angka_kredit')->nullable(); // tmtakrid
            $table->decimal('kredit_utama', 8, 3)->nullable(); // tkutama1
            $table->decimal('kredit_penunjang', 8, 3)->nullable(); // tkutama2
            $table->decimal('total_kredit', 8, 3)->nullable(); // tkutama3 (asumsi)
            $table->text('jabatan_saat_penilaian')->nullable(); // Changed to text
            
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
