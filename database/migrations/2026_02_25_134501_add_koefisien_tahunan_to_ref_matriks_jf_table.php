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
        Schema::table('ref_matriks_jf', function (Blueprint $table) {
            $table->float('koefisien_tahunan')->nullable()->after('is_naik_jenjang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ref_matriks_jf', function (Blueprint $table) {
            $table->dropColumn('koefisien_tahunan');
        });
    }
};
