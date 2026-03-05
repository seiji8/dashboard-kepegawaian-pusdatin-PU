<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN kategori ENUM('KGB', 'KP_Jafung', 'KP_Struktural', 'KP_Reguler', 'KJ_Jafung', 'UKOM', 'DIKLAT_HUTANG', 'DIKLAT_ANOMALI') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN kategori ENUM('KGB', 'KP_Jafung', 'KP_Struktural', 'KP_Reguler', 'KJ_Jafung', 'UKOM') NOT NULL");
    }
};
