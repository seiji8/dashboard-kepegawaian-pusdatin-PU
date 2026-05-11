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
        Schema::table('riwayat_jabatan', function (Blueprint $table) {
            $table->string('kd_eselon')->nullable()->after('tipe_jabatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_jabatan', function (Blueprint $table) {
            $table->dropColumn('kd_eselon');
        });
    }
};
