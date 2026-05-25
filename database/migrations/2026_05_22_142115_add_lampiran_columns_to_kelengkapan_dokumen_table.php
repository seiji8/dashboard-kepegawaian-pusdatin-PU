<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelengkapan_dokumen', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('link_file');
            $table->string('mime_type')->nullable()->after('file_path');
            $table->string('judul_lampiran')->nullable()->after('mime_type');
            $table->integer('urutan')->default(0)->after('judul_lampiran');
            $table->unsignedBigInteger('ukuran_bytes')->nullable()->after('urutan');
        });
    }

    public function down(): void
    {
        Schema::table('kelengkapan_dokumen', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'mime_type', 'judul_lampiran', 'urutan', 'ukuran_bytes']);
        });
    }
};
