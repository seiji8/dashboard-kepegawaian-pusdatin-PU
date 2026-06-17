<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // MODIFY COLUMN hanya didukung MySQL, skip untuk SQLite (testing)
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN status_saat_ini ENUM('Aman', 'Mendekati', 'Usulan', 'Proses', 'Upload E-HRM', 'Menunggu SKP', 'Selesai') DEFAULT 'Aman'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN status_saat_ini ENUM('Aman', 'Mendekati', 'Usulan', 'Menunggu SKP', 'Selesai') DEFAULT 'Aman'");
    }
};
