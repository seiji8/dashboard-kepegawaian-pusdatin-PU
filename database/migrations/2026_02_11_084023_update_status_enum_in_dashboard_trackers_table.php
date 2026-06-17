<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Using raw SQL for MySQL to modify ENUM
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN status_saat_ini ENUM('Aman', 'Mendekati', 'Usulan', 'Proses', 'Upload E-HRM', 'Menunggu SKP', 'Selesai') DEFAULT 'Aman'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous ENUM definition (assuming basic set)
        // Adjust these values based on what the original migration likely had.
        // It likely had 'Aman', 'Mendekati', 'Usulan', 'Menunggu SKP', 'Selesai'
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN status_saat_ini ENUM('Aman', 'Mendekati', 'Usulan', 'Menunggu SKP', 'Selesai') DEFAULT 'Aman'");
    }
};
