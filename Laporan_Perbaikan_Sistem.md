# Laporan Teknis: Perbaikan & Pengembangan Sistem Dashboard Kepegawaian
**JF PUSDATIN KEMENTERIAN PUPR**

Laporan ini merangkum seluruh perubahan, perbaikan bug, dan penambahan fitur pada sistem **Dashboard Kepegawaian**. Dokumen ini disusun per bagian (Backend, Frontend, dan Database) untuk memudahkan pemahaman bagi tim pengembang.

---

## 1. BAGIAN: BACKEND (Console, Services, & Controller)

### A. Sinkronisasi Data e-HRM (`SyncEhrmData.php`)
*   **Pembaruan Kolom Eselon**: Menambahkan pengisian kolom `kd_eselon` ke dalam model `RiwayatJabatan` saat mengambil data Jabatan Terakhir dari API Tahap 5. Ini krusial untuk pencocokan eselon aktif pada kenaikan pangkat struktural.
*   **Pemetaan Berkas Otomatis**:
    *   File SK Jabatan Terakhir dari API (`arsip` pada Jabatan) dipetakan otomatis menjadi `is_uploaded = true` pada tabel `KelengkapanDokumen` untuk kategori **KJ_Jafung** dan **KP_Struktural** sekaligus.
    *   File SK Tugas Belajar dari API (`arsip_izin_belajar` pada Tubel) dipetakan otomatis ke tabel `KelengkapanDokumen` untuk kategori **TUBEL**.
*   **Sinkronisasi Pendidikan**: Menyimpan field `pendidikan` dari API e-HRM ke kolom `jenjang_pendidikan` pada tabel `pegawai`.

### B. Tracker Service & Validasi Pangkat/Jenjang
1.  **Tubel Service (`TubelService.php`)**:
    *   Membatasi total dokumen persyaratan TUBEL menjadi **1 dokumen saja (SK Tugas Belajar)**.
    *   Membersihkan otomatis dokumen fisik manual lainnya (Surat Pengantar & Ijazah) dari database karena tidak didukung unggah lokal.
    *   **Pencegahan Reset Status**: Menambahkan kondisi agar tracker yang berstatus **"Selesai"** (telah dikonfirmasi admin) tetap bertahan pada status tersebut dan tidak ter-reset kembali menjadi `"Sedang Tubel"` pada kalkulasi berikutnya.
    *   **Fallback Validasi**: Jika berkas SK Tugas Belajar terdeteksi di riwayat tubel tapi belum ada di kelengkapan dokumen, sistem otomatis menyinkronkan statusnya menjadi lengkap (`is_uploaded = true`).
2.  **Kenaikan Jenjang Service (`KenaikanJenjangService.php`)**:
    *   **Fallback SK Jabatan Terakhir**: Sistem secara otomatis memindai tabel `riwayat_jabatan` untuk mencari file SK jabatan terbaru. Jika ditemukan, status kelengkapan dokumen `SK Jabatan Terakhir` otomatis disinkronisasikan menjadi lengkap.
3.  **Kenaikan Pangkat Service (`KenaikanPangkatService.php`)**:
    *   **Batasan Pendidikan KP Reguler**: Mengimplementasikan pengecekan pangkat tertinggi (*ceiling pangkat*) berdasarkan ijazah terakhir pegawai pada jalur reguler (Pelaksana).
    *   Jika pangkat saat ini sudah mentok (misal lulusan D-III di golongan III/c, atau SLTA di golongan III/b), tracker `KP_Reguler` akan dilewati dan dihapus otomatis dari dashboard.

### C. Controller Details (`DataPegawaiController.php`)
*   **Fallback Validasi Modal Detail**: Menambahkan logika fallback di method `show` agar pop-up detail pegawai menampilkan status **Lengkap (Centang Hijau)** untuk `SK Jabatan Terakhir` dan `SK Tugas Belajar` apabila file fisiknya terdeteksi ada di riwayat database, meskipun status sinkronisasi e-HRM belum selesai diperbarui.

---

## 2. BAGIAN: FRONTEND (Views & JavaScript)

### A. Cetak Surat & Preview Nota Dinas TUBEL
*   **Template Resmi Nota Dinas**: Membuat file template PDF `surat_pengajuan_tubel_pdf.blade.php` sesuai format resmi Nota Dinas Pengaktifan Kembali Pusdatin PUPR.
*   **Input Pendidikan Kustom**: Menambahkan field kustom **Pendidikan Terakhir** pada modal cetak TUBEL (`#suratTubelPendidikan` di `modal_surat.blade.php` & `dashboard-surat.js`). Admin bisa mengisi secara kustom (misal: "tugas belajar S2") yang akan langsung tercetak di PDF.
*   **Bypass Preview Cache**: Di `dashboard-surat.js`, iframe pratinjau dikloning ulang sebelum memuat URL PDF baru. Ini memaksa browser membuang cache preview lama dan langsung memuat PDF terupdate secara instan.
*   **Penggabungan Lampiran Fisik (Merge PDF)**: Mengintegrasikan panel **"Langkah 03 / Kelola Dokumen Lampiran"** pada kategori TUBEL (menggunakan library FPDI di backend). Admin dapat mengunggah file lampiran fisik yang akan digabungkan otomatis ke halaman belakang PDF utama.

### B. Perbaikan Bug Konfirmasi Nama Pegawai
*   **Masalah**: Tombol konfirmasi checklist di dashboard sebelumnya menampilkan ID angka (seperti `73`) alih-alih Nama Lengkap pegawai pada modal konfirmasi.
*   **Penyebab**: Kesalahan pemanggilan parameter di `index.blade.php` yang mengirimkan argumen `this` di posisi pertama:
    `onclick="openConfirmModal(this, this.dataset.id, this.dataset.nama)"`
    Sementara di Javascript hanya menerima dua parameter:
    `function openConfirmModal(trackerId, pegawaiName)`
*   **Perbaikan**: Menghapus parameter `this` di seluruh tombol pemicu `openConfirmModal` dan `openUkomModal` di `index.blade.php`. Sekarang nama pegawai terender dengan benar.

---

## 3. BAGIAN: DATABASE (Schema & Reference)

### A. Tabel `pegawai`
*   Kolom **`jenjang_pendidikan`** sekarang aktif digunakan untuk menyimpan data tingkat pendidikan terakhir pegawai yang ditarik dari field `pendidikan` API e-HRM.

### B. Skema Pembersihan Tracker Otomatis
*   Saat recalculate tracker berjalan (`php artisan tracker:run`), sistem akan otomatis menghapus (delete) baris tracker lama pada tabel `dashboard_tracker` jika pegawai bersangkutan:
    1.  Sudah menyelesaikan masa tugas belajarnya (status `Selesai`).
    2.  Sudah mentok pangkatnya berdasarkan tingkat pendidikannya (KP Reguler).
    3.  Data tracker lamanya berstatus `Mendekati` (dibersihkan agar tidak menumpuk di DB).

---

## 4. SKEMA MATRIKS BATASAN PENDIDIKAN (KP REGULER)

Berikut adalah aturan matriks batasan yang ditanamkan ke dalam backend tracker reguler untuk menentukan kelayakan kenaikan pangkat:

| Tingkat Pendidikan (e-HRM) | Golongan Terendah | Golongan Tertinggi (Ceiling) |
|----------------------------|-------------------|------------------------------|
| SD                         | I/a               | II/a                         |
| SLTP / SMP                 | I/c               | II/c                         |
| SLTA / SMA / SMK           | II/a              | III/b                        |
| Diploma II (D-II)          | II/b              | III/b                        |
| D-III / Sarjana Muda       | II/c              | III/c                        |
| S-1 / Diploma IV (D-IV)    | III/a             | III/d                        |
| S-2 / Magister             | III/b             | IV/a                         |
| S-3 / Doktor               | III/c             | IV/b                         |

---

## 5. INSTRUKSI DEPLOYMENT & TESTING

Setiap kali ada perubahan kode atau database di lokal, jalankan rentetan perintah berikut secara berurutan agar aman dan tidak merusak data pegawai yang sudah ada:

```bash
# 1. Update aturan notifikasi baru ke database (Tanpa migrate:fresh data pegawai)
docker-compose exec -T app php artisan db:seed --class=NotifikasiSeeder

# 2. Update data dummy testing (Opsional untuk testing lokal)
docker-compose exec -T app php artisan db:seed --class=DummyKGBSeeder
docker-compose exec -T app php artisan db:seed --class=DummyGlobalTestingSeeder

# 3. Bersihkan cache konfigurasi aplikasi
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear

# 4. Tarik data pendidikan & jabatan terbaru dari API e-HRM
docker-compose exec -T app php artisan ehrm:sync

# 5. Jalankan kalkulasi ulang seluruh tracker
docker-compose exec -T app php artisan tracker:run
```
