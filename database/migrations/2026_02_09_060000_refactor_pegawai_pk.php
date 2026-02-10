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
        Schema::disableForeignKeyConstraints();

        // 0. Drop Foreign Keys on dependent tables (to allow altering PK)
        Schema::table('riwayat_angka_kredit', function (Blueprint $table) { $table->dropForeign(['nip']); });
        Schema::table('riwayat_diklat', function (Blueprint $table) { $table->dropForeign(['nip']); });
        Schema::table('kelengkapan_dokumen', function (Blueprint $table) { $table->dropForeign(['nip']); });
        Schema::table('riwayat_jabatan', function (Blueprint $table) { $table->dropForeign(['nip']); });
        Schema::table('logs', function (Blueprint $table) { $table->dropForeign(['target_nip']); });
        Schema::table('dashboard_tracker', function (Blueprint $table) { $table->dropForeign(['nip']); });

        // 1. Alter Pegawai Table
        Schema::table('pegawai', function (Blueprint $table) {
            // Drop Primary Key lama (NIP)
            $table->dropPrimary(); 
            
            // Ubah id_pegawai_api jadi Primary Key (dan tidak boleh null)
            $table->string('id_pegawai_api')->nullable(false)->change()->primary();
            
            // Ubah NIP jadi Unique Key biasa
            $table->unique('nip');
        });

        // 1.5 Restore Foreign Keys referencing NIP (now Unique)
        Schema::table('riwayat_angka_kredit', function (Blueprint $table) { 
            $table->foreign('nip')->references('nip')->on('pegawai')->cascadeOnDelete(); 
        });
        Schema::table('riwayat_diklat', function (Blueprint $table) { 
            $table->foreign('nip')->references('nip')->on('pegawai')->cascadeOnDelete(); 
        });
        Schema::table('kelengkapan_dokumen', function (Blueprint $table) { 
            $table->foreign('nip')->references('nip')->on('pegawai')->cascadeOnDelete(); 
        });
        Schema::table('riwayat_jabatan', function (Blueprint $table) { 
            $table->foreign('nip')->references('nip')->on('pegawai')->cascadeOnDelete(); 
        });
        Schema::table('logs', function (Blueprint $table) { 
            $table->foreign('target_nip')->references('nip')->on('pegawai')->nullOnDelete(); 
        });

        // 2. Alter Dashboard Tracker Table (Migrate to UUID)
        Schema::table('dashboard_tracker', function (Blueprint $table) {
            // Drop Foreign Key Explicitly (Penting!)
            // SUDAH DI-DROP DI STEP 0
            
            // Hapus kolom NIP lama
             $table->dropColumn('nip');
             
            // Tambahkan pegawai_id (String) yang merujuk ke id_pegawai_api
            $table->string('pegawai_id')->after('id');
            
            $table->foreign('pegawai_id')
                  ->references('id_pegawai_api')
                  ->on('pegawai')
                  ->cascadeOnDelete();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        // Rollback Dashboard Tracker
        Schema::table('dashboard_tracker', function (Blueprint $table) {
            $table->dropForeign(['pegawai_id']);
            $table->dropColumn('pegawai_id');
            $table->string('nip')->nullable();
            $table->foreign('nip')->references('nip')->on('pegawai')->cascadeOnDelete();
        });

        // Drop FKs again to revert PK
        Schema::table('riwayat_angka_kredit', function (Blueprint $table) { $table->dropForeign(['nip']); });
        Schema::table('riwayat_diklat', function (Blueprint $table) { $table->dropForeign(['nip']); });
        Schema::table('kelengkapan_dokumen', function (Blueprint $table) { $table->dropForeign(['nip']); });
        Schema::table('riwayat_jabatan', function (Blueprint $table) { $table->dropForeign(['nip']); });
        Schema::table('logs', function (Blueprint $table) { $table->dropForeign(['target_nip']); });

        // Rollback Pegawai Table
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropUnique(['nip']);
            $table->dropPrimary(['id_pegawai_api']);
        });

        Schema::table('pegawai', function (Blueprint $table) {
            $table->primary('nip');
            $table->string('id_pegawai_api')->nullable()->change();
        });

        // Restore FKs referencing NIP (Primary)
        Schema::table('riwayat_angka_kredit', function (Blueprint $table) { 
            $table->foreign('nip')->references('nip')->on('pegawai')->cascadeOnDelete(); 
        });
        Schema::table('riwayat_diklat', function (Blueprint $table) { 
            $table->foreign('nip')->references('nip')->on('pegawai')->cascadeOnDelete(); 
        });
        Schema::table('kelengkapan_dokumen', function (Blueprint $table) { 
            $table->foreign('nip')->references('nip')->on('pegawai')->cascadeOnDelete(); 
        });
        Schema::table('riwayat_jabatan', function (Blueprint $table) { 
            $table->foreign('nip')->references('nip')->on('pegawai')->cascadeOnDelete(); 
        });
        Schema::table('logs', function (Blueprint $table) { 
            $table->foreign('target_nip')->references('nip')->on('pegawai')->nullOnDelete(); 
        });

        Schema::enableForeignKeyConstraints();
    }
};
