# Alur Proses Uji Kompetensi (UKOM)

## 1. Pemicu UKOM
- Uji Kompetensi (UKOM) merupakan prasyarat sebelum seorang pejabat fungsional dapat naik jenjang.
- Pada modul Kenaikan Jenjang (`KenaikanJenjangService.php`), jika pegawai memenuhi kriteria angka kredit atau masa kerja untuk naik jenjang, sistem awalnya mendeteksi bahwa mereka siap untuk KJ.
- Namun, sebelum bisa naik jenjang, status awal diatur untuk mengecek kewajiban UKOM.

## 2. Pemindahan ke Modul UKOM
- Di halaman Dashboard, pada bagian Kenaikan Jenjang, admin dapat memilih pegawai yang statusnya mendesak atau menunggu UKOM dan mengklik tombol "Kirim ke Modul UKOM".
- Tombol ini memanggil `moveToUkom` di `DashboardController.php`.
- Kategori tracker untuk pegawai tersebut diubah menjadi `UKOM` dengan status `Usulan`.
- Sebuah notifikasi email otomatis dikirim ke pegawai yang bersangkutan bahwa mereka didaftarkan Uji Kompetensi.

## 3. Proses UKOM dan Penentuan Kelulusan
- Di halaman Dashboard, admin kini melihat pegawai tersebut di bawah antrean UKOM (bukan lagi KJ biasa).
- Proses cetak surat usul pendaftaran UKOM dikerjakan langsung secara eksternal (di luar dashboard).
- Setelah proses UKOM di dunia nyata selesai, admin memperbarui status kelulusan melalui dashboard yang memanggil `setKelulusanUkom`.
- Jika dipilih **Lulus**:
  - Kategori tracker diubah kembali menjadi `KJ_Jafung` dengan status `Usulan`.
  - Pegawai melanjutkan proses Kenaikan Jenjang secara normal.
- Jika dipilih **Tidak Lulus**:
  - Tracker tetap di kategori `UKOM` namun keterangan diperbarui menjadi "Tidak Lulus UKOM" dan proses KJ dihentikan sementara.
