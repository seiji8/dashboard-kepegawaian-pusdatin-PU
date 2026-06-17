<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MODIFY COLUMN hanya didukung MySQL, skip untuk SQLite (testing)
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // 1. Ubah enum untuk memasukkan DIKLAT_BELUM_UPLOAD
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN kategori ENUM('KGB', 'KP_Jafung', 'KP_Struktural', 'KP_Reguler', 'KJ_Jafung', 'UKOM', 'TUBEL', 'DIKLAT_HUTANG', 'DIKLAT_ANOMALI', 'DIKLAT_BELUM_UPLOAD') NOT NULL");

        // 2. Ubah kategori NotifikasiRules dari DIKLAT_HUTANG ke DIKLAT_BELUM_UPLOAD, hapus DIKLAT_ANOMALI
        DB::table('notifikasi_rules')->where('kategori', 'DIKLAT_HUTANG')->update([
            'kategori' => 'DIKLAT_BELUM_UPLOAD',
        ]);
        DB::table('notifikasi_rules')->where('kategori', 'DIKLAT_ANOMALI')->delete();

        // 3. Hapus data dashboard_tracker untuk diklat lama
        DB::table('dashboard_tracker')->whereIn('kategori', ['DIKLAT_HUTANG', 'DIKLAT_ANOMALI'])->delete();

        // 4. Ubah enum final (menghapus DIKLAT_HUTANG dan DIKLAT_ANOMALI)
        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN kategori ENUM('KGB', 'KP_Jafung', 'KP_Struktural', 'KP_Reguler', 'KJ_Jafung', 'UKOM', 'TUBEL', 'DIKLAT_BELUM_UPLOAD') NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN kategori ENUM('KGB', 'KP_Jafung', 'KP_Struktural', 'KP_Reguler', 'KJ_Jafung', 'UKOM', 'TUBEL', 'DIKLAT_HUTANG', 'DIKLAT_ANOMALI', 'DIKLAT_BELUM_UPLOAD') NOT NULL");

        DB::table('notifikasi_rules')->where('kategori', 'DIKLAT_BELUM_UPLOAD')->update([
            'kategori' => 'DIKLAT_HUTANG',
        ]);

        DB::statement("ALTER TABLE dashboard_tracker MODIFY COLUMN kategori ENUM('KGB', 'KP_Jafung', 'KP_Struktural', 'KP_Reguler', 'KJ_Jafung', 'UKOM', 'TUBEL', 'DIKLAT_HUTANG', 'DIKLAT_ANOMALI') NOT NULL");
    }
};
