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
                'template_pesan' => "Berdasarkan data pada sistem E-HRM, Anda diharapkan untuk segera memperbarui dokumen SKP triwulan periode ini.\n\n- NIP: {nip}\n- Batas Waktu: {deadline}\n\nSilakan unggah dokumen melalui tautan berikut: [Link E-HRM]\n\nPesan ini dikirimkan secara otomatis oleh Sistem Notifikasi Pusdatin.",
                'interval_hari' => 90, // 3 Bulan
                'is_active' => true,
            ]
        );

        // 2. Notifikasi Tahunan
        NotifikasiRules::updateOrCreate(
            ['kategori' => 'Notifikasi Tahunan'],
            [
                'template_pesan' => "Mohon segera melengkapi Laporan Kinerja Tahunan Anda dan menyusun SKP untuk tahun berikutnya.\n\n- NIP: {nip}\n- Periode: {tahun}\n\nDokumen yang lengkap akan memperlancar proses administrasi kepegawaian Anda.",
                'interval_hari' => 365, // 1 Tahun
                'is_active' => true,
            ]
        );

        // 3. Template SKP (Manual)
        NotifikasiRules::updateOrCreate(
            ['kategori' => 'Template SKP'],
            [
                'template_pesan' => "Ini adalah pengingat manual untuk melengkapi berkas SKP Anda yang masih kurang lengkap. Mohon segera dicek kembali.",
                'interval_hari' => 0, // Manual
                'is_active' => true,
            ]
        );
        
        // 4. Kenaikan Pangkat (Update existing or create new)
        NotifikasiRules::updateOrCreate(
            ['kategori' => 'Info Kenaikan Pangkat'],
            [
                'template_pesan' => "Angka Kredit Anda ({poin}) sudah mencukupi untuk proses Kenaikan Pangkat ke {next_pangkat}. Mohon persiapkan berkas fisik dan serahkan ke bagian Tata Usaha paling lambat tanggal {deadline}.",
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

        // 6. KGB Upload Dokumen (Status: Upload E-HRM → Notif ke Pegawai untuk upload)
        NotifikasiRules::updateOrCreate(
            ['kategori' => 'KGB Upload Dokumen'],
            [
                'template_pesan' => "Berdasarkan data pada sistem E-HRM, masa KGB Anda telah tiba per tanggal {deadline}.\n\nMohon segera mengunggah dokumen yang diperlukan:\n- SK KGB Terakhir\n\nSilakan unggah dokumen melalui tautan pada sistem E-HRM.",
                'interval_hari' => 1, // Default: Setiap 1 Hari (Sampai dokumen lengkap)
                'is_active' => true,
            ]
        );

        // 7. Pesan Otomatis (Triggered Once)
        $autoCategories = [
            'KGB' => "Anda telah mendekati jadwal Kenaikan Gaji Berkala (KGB). Dalam 2 bulan ke depan Anda akan memasuki masa KGB.\n\nStatus KGB Anda saat ini adalah 'Usulan' dan akan diproses lebih lanjut oleh Admin Kepegawaian.",
            'KP_Reguler' => "Masa pangkat Anda telah memenuhi syarat Kenaikan Pangkat (KP Reguler). Status KP Anda saat ini adalah 'Usulan'.\n\nSiapkan dokumen dokumen berikut:\n- SK Pangkat Terakhir\n- SKP 2 Tahun Terakhir\n- Ijazah Terakhir\n\nHarap dipersiapkan agar dapat diproses oleh Admin Kepegawaian.",
            'KP_Struktural' => "Masa pangkat Anda telah memenuhi syarat Kenaikan Pangkat (KP Struktural). Status KP Anda saat ini adalah 'Usulan'.\n\nSiapkan dokumen dokumen berikut:\n- SK Pangkat Terakhir\n- SK Jabatan Struktural\n- SKP 2 Tahun Terakhir\n\nHarap dipersiapkan agar dapat diproses oleh Admin Kepegawaian.",
            'DIKLAT_BELUM_UPLOAD' => "Terdapat kewajiban Diklat yang perlu Anda selesaikan. Status Diklat Anda saat ini adalah 'Usulan'.\n\nSiapkan dokumen dokumen berikut:\n- Sertifikat Diklat/Pelatihan\n- Surat Tugas/Izin Belajar\n\nHarap dipersiapkan agar dapat diproses oleh Admin Kepegawaian.",
            'KJ_Jafung' => "Angka Kredit / Syarat Anda telah mencukupi untuk Kenaikan Jenjang. Status KJ Jafung Anda saat ini adalah 'Usulan'.\n\nSiapkan dokumen dokumen berikut:\n- SK Jabatan Fungsional Terakhir\n- SKP 2 Tahun Terakhir\n\nHarap dipersiapkan agar dapat diproses oleh Admin Kepegawaian.",
            'KP_Jafung' => "Angka Kredit / Syarat Anda telah mencukupi untuk Kenaikan Pangkat Fungsional. Status KP Jafung Anda saat ini adalah 'Usulan'.\n\nSiapkan dokumen dokumen berikut:\n- SK Pangkat Terakhir\n- PAK (Penetapan Angka Kredit) Asli\n- SKP 2 Tahun Terakhir\n\nHarap dipersiapkan agar dapat diproses oleh Admin Kepegawaian.",
            'UKOM' => "Anda telah diusulkan untuk Naik Jenjang, namun Anda perlu mengikuti Uji Kompetensi terlebih dahulu. Status UKOM Anda saat ini adalah 'Usulan'.",
        ];

        foreach ($autoCategories as $kategori => $pesan) {
            NotifikasiRules::updateOrCreate(
                ['kategori' => $kategori],
                [
                    'template_pesan' => $pesan,
                    'interval_hari' => 0, // 0 = Kirim sekali by trigger (dan juga aman dimasukkan kategori Template)
                    'is_active' => true,
                ]
            );
        }

        // 8. Upload Dokumen Rules (Status: Upload E-HRM)
        $uploadCategories = [
            'KP_Reguler Upload Dokumen' => "Proses Kenaikan Pangkat Reguler Anda telah selesai.\n\nMohon segera mengunggah dokumen yang diperlukan:\n{missing_documents}\n\nSilakan unggah dokumen melalui tautan pada sistem E-HRM.",
            'KP_Struktural Upload Dokumen' => "Proses Kenaikan Pangkat Struktural Anda telah selesai.\n\nMohon segera mengunggah dokumen yang diperlukan:\n{missing_documents}\n\nSilakan unggah dokumen melalui tautan pada sistem E-HRM.",
            'KP_Jafung Upload Dokumen' => "Proses Kenaikan Pangkat Fungsional Anda telah selesai.\n\nMohon segera mengunggah dokumen yang diperlukan:\n{missing_documents}\n\nSilakan unggah dokumen melalui tautan pada sistem E-HRM.",
            'KJ_Jafung Upload Dokumen' => "Proses Kenaikan Jenjang Fungsional Anda telah selesai.\n\nMohon segera mengunggah dokumen yang diperlukan:\n{missing_documents}\n\nSilakan unggah dokumen melalui tautan pada sistem E-HRM.",
            'DIKLAT Upload Dokumen' => "Mohon segera mengunggah dokumen yang diperlukan:\n{missing_documents}\n\nSilakan unggah dokumen melalui tautan pada sistem E-HRM.",
            'TUBEL Upload Dokumen' => "Mohon segera mengunggah dokumen yang diperlukan:\n{missing_documents}\n\nSilakan unggah dokumen melalui tautan pada sistem E-HRM.",
        ];

        foreach ($uploadCategories as $kategori => $pesan) {
            NotifikasiRules::updateOrCreate(
                ['kategori' => $kategori],
                [
                    'template_pesan' => $pesan,
                    'interval_hari' => 1, // Default: Setiap 1 Hari (Sampai dokumen lengkap)
                    'is_active' => true,
                ]
            );
        }
    }
}
