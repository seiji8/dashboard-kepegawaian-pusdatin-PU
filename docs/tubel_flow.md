# Alur Proses Tugas Belajar (TUBEL)

## 1. Sinkronisasi Data (E-HRM -> Sistem)
- Sistem mengambil data riwayat tugas belajar dari API E-HRM melalui `SyncEhrmData.php`.
- Data riwayat tugas belajar yang relevan disimpan ke dalam tabel `riwayat_tubels`.

## 2. Pengecekan Tracker (`TubelService.php`)
- `TubelService` (yang dipanggil saat sinkronisasi harian/bulanan di `RecalculateTracker.php`) mengecek apakah ada riwayat tubel yang sedang aktif.
- Syarat aktif: tanggal hari ini berada di antara `tanggal_mulai` dan `tanggal_selesai` (atau tanggal perpanjangan).
- Jika aktif, sistem membuat/mengupdate `DashboardTracker` dengan kategori `TUBEL` dan status `Sedang Tubel`.
- Jika sisa hari <= 60 hari dari target selesai, status berubah menjadi `Proses Pengaktifan`.
- Dokumen kelengkapan otomatis dibuat di tabel `kelengkapan_dokumen` dengan target 3 dokumen: Surat Pengantar Unit Kerja, SK Tugas Belajar, dan Ijazah / Transkrip Nilai.
- Sistem mengirimkan email notifikasi ke admin (`SystemAlertNotification`) untuk segera menyiapkan pengaktifan kembali.

## 3. Aksi Admin (Dashboard)
- Admin melihat daftar pegawai yang sedang atau akan selesai tugas belajar di bagian Akordion "Tugas Belajar dan Pengaktifan Kembali Tubel".
- Admin melengkapi checklist dokumen.
- Admin mengklik tombol "Cetak Surat Pengajuan" yang menghasilkan PDF menggunakan template `surat_pengajuan_pdf.blade.php`.
- Admin mengkonfirmasi bahwa proses selesai dengan mengklik tombol *checklist*, yang memanggil `confirmTracker` dan mengubah status menjadi `Selesai` (lalu hilang dari tracker jika sudah diproses penuh).
