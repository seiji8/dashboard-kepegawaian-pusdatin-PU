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
        Schema::create('ref_matriks_jf', function (Blueprint $table) {
            $table->id();
            $table->string('jabatan_asal'); 
            $table->string('pangkat_asal'); 
            $table->float('target_ak'); 
            $table->integer('syarat_tahun_min')->default(2);
            $table->string('next_pangkat');
            $table->string('next_jenjang')->nullable();
            $table->boolean('is_naik_jenjang')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_matriks_jf');
    }
};
