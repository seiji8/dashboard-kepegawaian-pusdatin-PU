<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            // Kolom baru sesuai kebutuhan API
            $table->string('id_pegawai_api')->nullable()->index()->after('nip'); // ID asli dari API (untuk relasi ke AK)
            $table->string('pangkat_golongan')->nullable()->after('jabatan_saat_ini'); // Gabungan Pangkat & Golongan
            $table->string('jenjang')->nullable()->after('pangkat_golongan'); // Jenjang Fungsional
            $table->string('tipe_jabatan')->nullable()->after('jenjang'); // Tipe Jabatan (Fungsional, Struktural, dll)
            
            // Hapus kolom lama yang digantikan (Opsional, tapi biar bersih)
            $table->dropColumn('golongan');
            $table->dropColumn('pangkat_saat_ini');
        });
    }

    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropColumn(['id_pegawai_api', 'pangkat_golongan', 'jenjang', 'tipe_jabatan']);
            $table->string('golongan')->nullable();
            $table->string('pangkat_saat_ini')->nullable();
        });
    }
};
