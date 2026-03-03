<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotifikasiRules;

class NotifikasiSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan data lama
        NotifikasiRules::truncate();

        // 1. Notifikasi Triwulan
        NotifikasiRules::updateOrCreate(
            ['kategori' => 'Notifikasi Triwulan'],
            [
                'template_pesan' => "Yth. Bapak/Ibu {nama},\n\nBerdasarkan data pada sistem E-HRM, Bapak/Ibu diharapkan untuk segera memperbarui dokumen SKP triwulan periode ini.\n\n- NIP: {nip}\n- Batas Waktu: {deadline}\n\nSilakan unggah dokumen melalui tautan berikut: [Link E-HRM]\n\nPesan ini dikirimkan secara otomatis oleh Sistem Notifikasi Pusdatin.",
                'interval_hari' => 90, // 3 Bulan
                'is_active' => true,
            ]
        );

        // 2. Notifikasi Tahunan
        NotifikasiRules::updateOrCreate(
            ['kategori' => 'Notifikasi Tahunan'],
            [
                'template_pesan' => "Yth. Bapak/Ibu {nama},\n\nMohon segera melengkapi Laporan Kinerja Tahunan Anda dan menyusun SKP untuk tahun berikutnya.\n\n- NIP: {nip}\n- Periode: {tahun}\n\nDokumen yang lengkap akan memperlancar proses administrasi kepegawaian Anda. Terima kasih.\n\nSalam,\nAdmin Kepegawaian",
                'interval_hari' => 365, // 1 Tahun
                'is_active' => true,
            ]
        );

        // 3. Template SKP (Manual)
        NotifikasiRules::updateOrCreate(
            ['kategori' => 'Template SKP'],
            [
                'template_pesan' => "Ini adalah pengingat manual untuk melengkapi berkas SKP Anda yang masih kurang lengkap. Mohon segera dicek kembali.\n\nTerima kasih.",
                'interval_hari' => 0, // Manual
                'is_active' => true,
            ]
        );
        
        // 4. Kenaikan Pangkat (Update existing or create new)
        NotifikasiRules::updateOrCreate(
            ['kategori' => 'Info Kenaikan Pangkat'],
            [
                'template_pesan' => "Selamat {nama},\n\nAngka Kredit Anda ({poin}) sudah mencukupi untuk proses Kenaikan Pangkat ke {next_pangkat}. Mohon persiapkan berkas fisik dan serahkan ke bagian Tata Usaha paling lambat tanggal {deadline}.\n\nTerima kasih.",
                'interval_hari' => 1, // Harian Check
                'is_active' => true,
            ]
        );

        // 5. KGB Penjadwalan (Status: Mendekati → Notif ke Admin)
        NotifikasiRules::updateOrCreate(
            ['kategori' => 'KGB Penjadwalan'],
            [
                'template_pesan' => "⚠️ Peringatan KGB\n\nPegawai a.n {nama} akan memasuki masa KGB pada tanggal {deadline}.\n\nMohon segera mempersiapkan proses administrasi KGB yang diperlukan.\n\nPesan ini dikirimkan secara otomatis oleh Sistem Notifikasi Pusdatin.",
                'interval_hari' => 60, // H-2 Bulan sebelum KGB
                'is_active' => true,
            ]
        );

        // 6. KGB Upload Dokumen (Status: Usulan → Notif ke Pegawai, setelah notif Mendekati)
        NotifikasiRules::updateOrCreate(
            ['kategori' => 'KGB Upload Dokumen'],
            [
                'template_pesan' => "Yth. Bapak/Ibu {nama},\n\nBerdasarkan data pada sistem E-HRM, masa KGB Anda telah tiba per tanggal {deadline}.\n\nMohon segera mengunggah dokumen yang diperlukan:\n- SK Terakhir\n- SKP Periode Berjalan\n\nSilakan unggah dokumen melalui tautan pada sistem E-HRM.\n\nTerima kasih.\nAdmin Kepegawaian Pusdatin",
                'interval_hari' => 1, // Default: Setiap 1 Hari (Sampai dokumen lengkap)
                'is_active' => true,
            ]
        );
    }
}
