<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify ENUM field to include new categories
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN kategori ENUM('KGB', 'KP_Jafung', 'KP_Struktural', 'KP_Reguler', 'KJ_Jafung', 'UKOM') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back if needed
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN kategori ENUM('KGB', 'KP_Jafung', 'KP_Struktural') NOT NULL");
    }
};
