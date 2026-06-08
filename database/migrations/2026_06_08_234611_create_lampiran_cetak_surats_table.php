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
        Schema::create('lampiran_cetak_surat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_tracker_id')->constrained('dashboard_tracker')->onDelete('cascade');
            $table->string('nip', 50)->nullable();
            $table->string('nama_dokumen')->nullable();
            $table->string('judul_lampiran')->nullable();
            $table->string('file_path')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('urutan')->default(1);
            $table->integer('halaman_cetak')->default(1);
            $table->integer('ukuran_bytes')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lampiran_cetak_surat');
    }
};
