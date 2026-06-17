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
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN kategori ENUM('KGB', 'KP_Jafung', 'KJ_Jafung') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN kategori ENUM('KGB', 'KP_Jafung') NOT NULL");
    }
};
