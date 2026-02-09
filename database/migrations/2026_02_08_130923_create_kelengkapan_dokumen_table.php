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
        Schema::create('kelengkapan_dokumen', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 20);
            $table->unsignedBigInteger('dashboard_tracker_id');
            $table->string('nama_dokumen');
            $table->boolean('is_uploaded')->default(false);
            $table->text('link_file')->nullable();
            $table->enum('status_verifikasi', ['Pending', 'Valid', 'Ditolak'])->default('Pending');
            $table->text('keterangan_tolak')->nullable();
            $table->timestamps();
            
            $table->foreign('nip')->references('nip')->on('pegawai')->onDelete('cascade');
            $table->foreign('dashboard_tracker_id')->references('id')->on('dashboard_tracker')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelengkapan_dokumen');
    }
};
