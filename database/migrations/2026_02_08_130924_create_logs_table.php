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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['API_SYNC', 'NOTIF_SENT', 'ADMIN_ACTION', 'SYSTEM_LOG']);
            $table->text('deskripsi');
            $table->string('target_nip', 20)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('waktu')->useCurrent();

            $table->foreign('target_nip')->references('nip')->on('pegawai')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
