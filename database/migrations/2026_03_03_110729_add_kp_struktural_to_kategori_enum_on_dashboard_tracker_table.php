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
        // Karena enum bergantung pada Dialect MySQL,
        // cara paling aman menambah nilai Enum di Laravel 10+ adalah menggunakan raw query
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN kategori ENUM('KGB', 'KP_Jafung', 'KP_Struktural', 'KJ_Jafung', 'UKOM') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN kategori ENUM('KGB', 'KP_Jafung', 'KJ_Jafung', 'UKOM') NOT NULL");
    }
};
