<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN status_saat_ini ENUM('Aman','Mendekati','Usulan','Proses','Upload E-HRM','Menunggu SKP','Menunggu UKOM','Selesai') DEFAULT 'Aman'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN status_saat_ini ENUM('Aman','Mendekati','Usulan','Proses','Upload E-HRM','Menunggu SKP','Selesai') DEFAULT 'Aman'");
    }
};
