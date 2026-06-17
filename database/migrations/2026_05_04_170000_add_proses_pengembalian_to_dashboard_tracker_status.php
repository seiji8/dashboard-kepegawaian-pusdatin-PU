<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN status_saat_ini ENUM(
                'Aman','Mendekati','Menunggu UKOM','Usulan','Proses',
                'Upload E-HRM','Menunggu SKP','Selesai',
                'Sedang Tubel','Proses Pengaktifan','Proses Pengembalian'
            ) DEFAULT 'Aman'");
        }

        DB::table('dashboard_tracker')
            ->where('status_saat_ini', 'Proses Pengaktifan')
            ->update(['status_saat_ini' => 'Proses Pengembalian']);
    }

    public function down(): void
    {
        DB::table('dashboard_tracker')
            ->where('status_saat_ini', 'Proses Pengembalian')
            ->update(['status_saat_ini' => 'Proses Pengaktifan']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN status_saat_ini ENUM(
                'Aman','Mendekati','Menunggu UKOM','Usulan','Proses',
                'Upload E-HRM','Menunggu SKP','Selesai',
                'Sedang Tubel','Proses Pengaktifan'
            ) DEFAULT 'Aman'");
        }
    }
};
