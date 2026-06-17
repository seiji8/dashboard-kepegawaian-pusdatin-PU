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
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN status_saat_ini ENUM(
                'Aman','Mendekati','Menunggu UKOM','Usulan','Proses',
                'Upload E-HRM','Menunggu SKP','Selesai',
                'Sedang Tubel','Proses Pengaktifan','Proses Pengembalian','Proses Pengaktifan Kembali'
            ) DEFAULT 'Aman'");
        }

        DB::table('dashboard_tracker')
            ->where('status_saat_ini', 'Proses Pengembalian')
            ->update(['status_saat_ini' => 'Proses Pengaktifan Kembali']);
    }

    public function down(): void
    {
        DB::table('dashboard_tracker')
            ->where('status_saat_ini', 'Proses Pengaktifan Kembali')
            ->update(['status_saat_ini' => 'Proses Pengembalian']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN status_saat_ini ENUM(
                'Aman','Mendekati','Menunggu UKOM','Usulan','Proses',
                'Upload E-HRM','Menunggu SKP','Selesai',
                'Sedang Tubel','Proses Pengaktifan','Proses Pengembalian'
            ) DEFAULT 'Aman'");
        }
    }
};
