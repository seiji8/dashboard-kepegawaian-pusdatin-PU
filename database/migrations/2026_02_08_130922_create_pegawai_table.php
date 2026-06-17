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
        Schema::create('pegawai', function (Blueprint $table) {
            // 1. Identitas Utama
            // Kita pakai NIP sebagai Primary Key, bukan ID auto-increment
            $table->string('nip', 20)->primary();
            $table->string('nama');
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();

            // 2. Jabatan & Pangkat Saat Ini (Penting untuk Matriks JF)
            $table->string('jabatan_saat_ini')->nullable(); // Jenjang Fungsional
            $table->string('pangkat_saat_ini')->nullable(); // Pangkat/Golongan
            $table->string('golongan')->nullable();

            // 3. Tanggal-tanggal Krusial (Untuk Logic KGB & KP)
            $table->date('tmt_pangkat_terakhir')->nullable();
            $table->date('tmt_cpns')->nullable();        // Acuan KGB jika pegawai baru
            $table->date('tmt_kgb_terakhir')->nullable(); // Acuan KGB jika pegawai lama
            $table->date('kgb_terakhir')->nullable();    // Tanggal SK KGB-nya

            // 4. Data SK & Referensi (Dokumen Pendukung)
            $table->string('sk_pangkat_terakhir')->nullable();
            $table->string('sk_cpns')->nullable();
            $table->string('sk_struktural')->nullable();
            $table->date('tmt_struktural')->nullable();
            $table->string('nomor_sk_kp')->nullable();
            $table->date('tmt_sk_kp')->nullable();

            // 5. Data Pendidikan & Izin Belajar
            $table->string('jenjang_pendidikan')->nullable();
            $table->date('tgl_mulai_izin_belajar')->nullable();
            $table->date('tgl_selesai_izin_belajar')->nullable();

            // 6. Link File Dokumen (Dari e-HRM)
            $table->string('link_sk_lulus')->nullable();
            $table->string('link_ijazah')->nullable();
            $table->string('link_transkrip_nilai')->nullable();

            // Kinerja (Opsional, jika data ini ditarik juga)
            $table->float('nilai_kinerja_tahunan')->nullable();
            $table->year('tahun_skp_terakhir')->nullable();

            $table->timestamps(); // Created_at & Updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
