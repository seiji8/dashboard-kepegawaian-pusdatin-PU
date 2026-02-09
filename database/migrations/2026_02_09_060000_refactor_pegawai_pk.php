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

        // 1. Alter Pegawai Table
        Schema::table('pegawai', function (Blueprint $table) {
            // Drop Primary Key lama (NIP)
            $table->dropPrimary(); 
            
            // Ubah id_pegawai_api jadi Primary Key (dan tidak boleh null)
            $table->string('id_pegawai_api')->nullable(false)->change()->primary();
            
            // Ubah NIP jadi Unique Key biasa
            // Note: NIP sebelumnya primary, sekarang jadi unique
            $table->unique('nip');
        });

        // 2. Alter Dashboard Tracker Table
        Schema::table('dashboard_tracker', function (Blueprint $table) {
            // Drop Foreign Key Explicitly (Penting!)
             $table->dropForeign(['nip']); 
            
            // Hapus kolom NIP lama
             $table->dropColumn('nip');
             
            // Tambahkan pegawai_id (String) yang merujuk ke id_pegawai_api
            $table->string('pegawai_id')->after('id');
            
            $table->foreign('pegawai_id')
                  ->references('id_pegawai_api')
                  ->on('pegawai')
                  ->onDelete('cascade');
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
        });

        // Rollback Pegawai Table (Agak ribet karena balikin ID autoincrement)
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropUnique(['nip']);
            // Balikin id_pegawai_api jadi nullable biasa
            // Drop Primary Key
            $table->dropPrimary(['id_pegawai_api']);
        });

        Schema::table('pegawai', function (Blueprint $table) {
            // Balikin NIP jadi Primary
            $table->primary('nip');
            $table->string('id_pegawai_api')->nullable()->change();
        });

        Schema::enableForeignKeyConstraints();
    }
};
