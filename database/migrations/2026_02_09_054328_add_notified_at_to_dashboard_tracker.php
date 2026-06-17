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
        Schema::table('dashboard_tracker', function (Blueprint $table) {
            // Kita tambahkan kolom tanggal, boleh kosong (nullable)
            // 'after' cuma biar posisinya rapi di database (setelah kolom status)
            $table->timestamp('notified_at')->nullable()->after('status_saat_ini');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboard_tracker', function (Blueprint $table) {
            // Kalau migrasi di-rollback, kolom ini dibuang
            $table->dropColumn('notified_at');
        });
    }
};
