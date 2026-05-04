<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah TUBEL ke kategori enum
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN kategori ENUM(
            'KGB','KP_Jafung','KP_Struktural','KP_Reguler',
            'KJ_Jafung','UKOM','DIKLAT_HUTANG','DIKLAT_ANOMALI','TUBEL'
        ) NOT NULL");

        // 2. Tambah 'Sedang Tubel' dan 'Proses Pengaktifan' ke status enum
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN status_saat_ini ENUM(
            'Aman','Mendekati','Menunggu UKOM','Usulan','Proses',
            'Upload E-HRM','Menunggu SKP','Selesai',
            'Sedang Tubel','Proses Pengaktifan'
        ) DEFAULT 'Aman'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN kategori ENUM(
            'KGB','KP_Jafung','KP_Struktural','KP_Reguler',
            'KJ_Jafung','UKOM','DIKLAT_HUTANG','DIKLAT_ANOMALI'
        ) NOT NULL");

        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN status_saat_ini ENUM(
            'Aman','Mendekati','Menunggu UKOM','Usulan','Proses',
            'Upload E-HRM','Menunggu SKP','Selesai'
        ) DEFAULT 'Aman'");
    }
};
