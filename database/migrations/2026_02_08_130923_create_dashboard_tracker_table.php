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
        Schema::create('dashboard_tracker', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 20);
            $table->enum('kategori', ['KGB', 'KP_Jafung', 'KP_Struktural']);
            $table->enum('status_saat_ini', ['Normal', 'Mendekati', 'Menunggu SKP', 'Menunggu Upload', 'Mencukupi', 'Usulan'])->default('Normal');
            $table->date('tanggal_target')->nullable();
            $table->integer('dokumen_terupload')->default(0);
            $table->integer('dokumen_total')->default(0);
            $table->timestamps();
            
            $table->foreign('nip')->references('nip')->on('pegawai')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_tracker');
    }
};
