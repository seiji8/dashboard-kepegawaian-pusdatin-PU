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
        Schema::table('riwayat_angka_kredit', function (Blueprint $table) {
            $table->string('id_pegawai_api')->nullable()->after('id');
            // Menghapus nip apabila sudah di-migrate dan terisi, jika perlu diisi data baru
        });

        // Kita eksekusi query lanjutan karena SQLite / MySQL mungkin rewel dengan drop column ber-foreign-key
        // Namun kita asumsikan NIP sebelumnya bukan strict foreign key constraint, jadi:
        Schema::table('riwayat_angka_kredit', function (Blueprint $table) {
            $table->dropForeign(['nip']);
            $table->dropColumn('nip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_angka_kredit', function (Blueprint $table) {
            $table->string('nip')->nullable()->after('id_pegawai_api');
        });
        Schema::table('riwayat_angka_kredit', function (Blueprint $table) {
            $table->dropColumn('id_pegawai_api');
        });
    }
};
