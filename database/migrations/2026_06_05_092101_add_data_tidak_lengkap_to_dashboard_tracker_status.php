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
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN status_saat_ini ENUM(
            'Aman','Mendekati','Menunggu UKOM','Usulan','Proses',
            'Upload E-HRM','Menunggu SKP','Selesai',
            'Sedang Tubel','Proses Pengaktifan','Proses Pengembalian','Proses Pengaktifan Kembali',
            'Data Tidak Lengkap'
        ) DEFAULT 'Aman'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('dashboard_tracker')
            ->where('status_saat_ini', 'Data Tidak Lengkap')
            ->update(['status_saat_ini' => 'Aman']);

        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN status_saat_ini ENUM(
            'Aman','Mendekati','Menunggu UKOM','Usulan','Proses',
            'Upload E-HRM','Menunggu SKP','Selesai',
            'Sedang Tubel','Proses Pengaktifan','Proses Pengembalian','Proses Pengaktifan Kembali'
        ) DEFAULT 'Aman'");
    }
};
