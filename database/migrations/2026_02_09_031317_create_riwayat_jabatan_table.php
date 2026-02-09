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
        Schema::create('riwayat_jabatan', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 20); // Foreign Key
            $table->string('nosk')->nullable();
            $table->date('tgl_sk')->nullable();
            $table->text('jabatan'); // Changed to text
            $table->date('tmt_jabatan')->nullable();
            $table->date('tgl_selesai')->nullable();
            $table->string('tipe_jabatan')->nullable();
            $table->string('file_sk')->nullable(); 
            
            // Relasi
            $table->foreign('nip')->references('nip')->on('pegawai')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_jabatan');
    }
};
