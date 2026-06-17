# Laporan Teknis — Person 3
## Backend: PDF Generation, Attachment Management & Email Notifications

**Project:** Sistem Dashboard Kepegawaian & Notifikasi PUSDATIN  
**Institusi:** Pusat Data dan Teknologi Informasi (PUSDATIN) — Kementerian Pekerjaan Umum  
**Tim:** Tim IT H2ER Devs — Universitas Negeri Semarang  
**Repository:** `seiji8/dashboard-kepegawaian`  
**Tech Stack:** Laravel 12, PHP 8.2+, DomPDF ^3.1, FPDI ^2.6

---

## 1. Gambaran Umum Tanggung Jawab

Person 3 bertanggung jawab atas:
- **PDF Generation**: Pembuatan Surat Pengajuan Kenaikan Jenjang (KJ) dan Pengaktifan Kembali Tugas Belajar (TUBEL) secara otomatis berbasis HTML-to-PDF menggunakan DomPDF, serta penggabungan PDF dinamis menggunakan FPDI.
- **Attachment Management (Lampiran)**: Manajemen berkas lampiran yang akan ditempelkan pada dokumen utama (unggah, kompresi gambar berbasis GD Library, pengurutan/reorder, rename, hapus fisik & database).
- **Email Notifications**: Sistem notifikasi email (dan lonceng database) berkala untuk pegawai dan admin, termasuk notifikasi usulan otomatis harian, notifikasi periodik (triwulanan & tahunan), serta notifikasi manual per pegawai/kategori.

---

## 2. PDF Generation & FPDI Merge (Penggabungan Lampiran)

### 2.1 Preview & Filter Kategori Usulan
**Controller:** `app/Http/Controllers/SuratPengajuanController.php`  
**Method:** `preview(string $kategori)`  
**Route:** `GET /surat-pengajuan/preview/{kategori}`

Fungsi ini dipanggil oleh UI (modal AJAX) untuk menampilkan daftar pegawai yang usulannya siap dicetak/dikonfirmasi berdasarkan kategori. Data dikelompokkan berdasarkan periode (`tanggal_target` dengan format Tahun-Bulan).

Kategori yang didukung oleh controller beserta mapping-nya:
- `KGB` (Kenaikan Gaji Berkala)
- `KP` (Kenaikan Pangkat - gabungan sub-kategori `KP_Jafung`, `KP_Struktural`, `KP_Reguler`)
- `KJ_Jafung` (Kenaikan Jenjang Fungsional)
- `UKOM` (Uji Kompetensi)
- `TUBEL` (Pengaktifan Kembali Tubel)

**Aturan Status Pengambilan Data:**
- Kategori Umum (`KGB`, `KP`, `KJ_Jafung`, `UKOM`): Status tracker yang diambil adalah `Usulan`, `Mendekati`, atau `Proses`.
- Kategori `TUBEL`: Status tracker yang diambil adalah `Sedang Tubel`, `Proses Pengaktifan Kembali`, atau `Proses Pengaktifan`.

### 2.2 Konfirmasi Usulan Tanpa Cetak Surat
**Method:** `konfirmasiUsulan(Request $request)`  
**Route:** `POST /surat-pengajuan/konfirmasi`

Mengubah status tracker secara massal dari `Usulan` / `Mendekati` menjadi `Proses` (untuk persiapan TTE di E-HRM). Berlaku untuk kategori `KGB`, `KP`, `TUBEL` (untuk Tubel, status tetap diubah tanpa mengisi `dikonfirmasi_at` hingga selesai). Menggunakan audit logging `logActivity`.

### 2.3 Cetak Surat & Auto-Update Status
**Method:** `generate(Request $request)`  
**Route:** `POST /surat-pengajuan/generate`

Berfungsi menghasilkan PDF final (surat pengantar + gabungan lampiran).
- **Auto-Update Status**: Mengubah status tracker pegawai secara otomatis (Usulan → Proses untuk umum; Sedang Tubel → Proses Pengaktifan Kembali untuk TUBEL).
- **Pembersihan Lampiran**: Jika request bukan pratinjau (`is_preview = 0`), sistem otomatis menghapus seluruh file lampiran fisik dari penyimpanan lokal dan record-nya di DB setelah PDF berhasil dikirim ke pengguna.
- **Service Layer**: Menggunakan `App\Services\SuratPengajuanService` untuk merender PDF.

### 2.4 Service Pengolah PDF & Layout Dokumen
**Service:** `app/Services/SuratPengajuanService.php`  
**Method:** `generateSurat(array $requestData, Collection $trackers, array $kategoriLabels)`

- **Guard Validation**: Membatasi pembuatan surat `KP` dan `KGB` dari dashboard (kedua kategori ini harus dicetak langsung dari sistem utama E-HRM).
- **Template Rendering**: Menggunakan Laravel DomPDF (`Barryvdh\DomPDF\Facade\Pdf`) untuk memuat Blade template ke memori.
  - Template `surat.surat_pengajuan_tubel_pdf` digunakan untuk kategori `TUBEL`.
  - Template `surat.surat_pengajuan_pdf` digunakan untuk kategori umum lainnya.
- **Fungsi Terbilang Mandiri**: Method `terbilang($x)` dan sub-method rekursif `_terbilang($x)` digunakan untuk mengonversi angka gaji (pada usulan KGB, jika digunakan) ke kata-kata bahasa Indonesia secara dinamis tanpa package eksternal.

### 2.5 Penggabungan File Lampiran Cerdas (Smart PDF & Image Merger)
**Method:** `appendLampiran(string $basePdfPath, $lampirans, string $nomorSurat, string $tanggalSurat)`

Menggunakan **FPDI** (`setasign\Fpdi\Fpdi`) untuk menyisipkan lembar lampiran ke belakang surat pengantar utama. Fitur cerdas penggabungan lampiran:
1. **Pendeteksian Tipe & Layout**: Memisahkan file PDF dan file Gambar (`jpeg`, `jpg`, `png`).
2. **Dynamic Scaling (Menjaga Aspek Rasio)**:
   - Halaman PDF yang disisipkan diskalakan menggunakan faktor skala optimal (`min(($boxW * marginRatio) / width, ($boxH * marginRatio) / height)`) agar tetap pas berada di dalam batas margin aman A4 (lebar 166mm) dan diberi border hitam tipis (lebar garis 0.2mm).
3. **Grid Layout 3-dalam-1 (`drawGrid3In1`)**:
   - Jika terdapat tepat 3 lampiran media dalam satu grup halaman cetak, sistem akan membagi halaman menjadi 2 baris: Baris atas berisi 2 media bersebelahan (masing-masing lebar ~80.5mm), baris bawah berisi 1 media penuh (lebar 166mm). Tinggi baris kedua dihitung secara dinamis berbasis sisa tinggi kertas rill.
4. **Layout Bersebelahan (Mode Jejeran untuk 2 Gambar)**:
   - Jika terdapat 2 gambar pada grup halaman yang sama, gambar dirender sejajar secara horizontal (kiri-kanan) di dalam sebuah kotak bingkai luar yang tebal (0.4mm).
5. **Mode Normal (1 Gambar per Halaman)**:
   - Gambar diposisikan rata atas dengan skala optimal sesuai ukuran aslinya.
6. **Blok Referensi Lampiran**:
   - Di pojok kanan atas halaman lampiran pertama, dicetak blok referensi Nota Dinas dinamis yang berisi Nama Instansi Penerbit, Nomor Surat, dan Tanggal Surat menggunakan method `drawLampiranReference` dan `drawLampiranHeader`.

### 2.6 Cetak Surat Usul Kenaikan Jenjang (KJ) & Bundle KJ
**Controller:** `app/Http/Controllers/DashboardController.php`  
**Method:** `cetakSuratKj(...)` & `generateBundleKj(...)`  
**Route:** `GET /surat-kj/{id}/generate-bundle`

- **Cetak Surat KJ**: Menghasilkan PDF surat usulan kenaikan jenjang tunggal pegawai menggunakan template `surat.surat_usul_kj_pdf` berukuran A4 Portrait.
- **Generate Bundle KJ**: Mengubah status usulan pegawai menjadi `Proses`, menghasilkan cover PDF usul KJ, memanggil `SuratPengajuanService@appendLampiran` untuk memaketkan seluruh lampiran dokumen pendukung (seperti SK, Pak, Ijazah, dll) yang diunggah pegawai menjadi satu file PDF utuh yang terintegrasi.

---

## 3. Attachment Management (Manajemen Lampiran)

**Controller:** `app/Http/Controllers/LampiranController.php`  
**Model:** `app/Models/LampiranCetakSurat.php`  
**Penyimpanan Fisik:** `storage/app/public/lampiran/{nip}/`

Fitur ini memfasilitasi admin untuk mengunggah dan mengelola berkas lampiran yang akan digabungkan ke cetakan PDF.

### 3.1 Alur Manajemen & Operasi CRUD

| Endpoint / Method | Fungsi Teknis |
|-------------------|---------------|
| `GET /lampiran/{tracker_id}` | Mengambil semua item lampiran milik tracker tertentu (mengembalikan data JSON berisi ID, nama, tipe mime, urutan halaman, dan URL preview). |
| `POST /lampiran` | Mengunggah berkas baru. Validasi: file harus berupa `jpg`, `jpeg`, `png`, atau `pdf`, dan ukuran maksimal 10 MB. |
| `DELETE /lampiran/{id}` | Menghapus record lampiran di database dan menghapus file fisiknya dari storage. |
| `DELETE /lampiran/clear/{tracker_id}` | Menghapus seluruh lampiran milik tracker tersebut secara massal (baik record DB maupun file fisiknya). |
| `POST /lampiran/reorder` | Memperbarui urutan cetak lampiran berdasarkan urutan array ID yang dikirim oleh UI (fitur drag-and-drop). |
| `PUT /lampiran/{id}/update-judul` | Mengubah judul lampiran (`judul_lampiran`) yang akan ditampilkan sebagai header pada PDF cetak. |

### 3.2 Kompresi Gambar Otomatis Berbasis GD Library
Untuk menghemat ruang penyimpanan server dan meminimalkan ukuran file PDF final, sistem mengimplementasikan kompresi gambar otomatis pada method `compressImage` sebelum file disimpan ke disk:
- **Deteksi Gambar**: Diterapkan pada file dengan MIME type `image/jpeg` or `image/png` (jika fungsi PHP GD `imagecreatefromjpeg`/`imagecreatefrompng` tersedia).
- **Prosedur Pengolahan**:
  1. Gambar dimuat ke memori.
  2. Jika lebar asli gambar melebihi **1400 piksel**, gambar akan di-resize secara proporsional ke lebar maksimal 1400 piksel menggunakan `imagecopyresampled`.
  3. Transparansi warna pada file PNG dipertahankan menggunakan `imagealphablending` dan `imagesavealpha`.
  4. Gambar disimpan kembali ke format **JPEG** dengan tingkat kualitas **85%** menggunakan `imagejpeg`, lalu disimpan di storage.

---

## 4. Email & Database Notifications Dispatching

Sistem notifikasi pada aplikasi ini dirancang untuk mendistribusikan pemberitahuan secara personal (pegawai) maupun manajerial (rekap untuk admin) melalui kombinasi email SMTP dan notifikasi lonceng database.

### 4.1 Model Aturan Notifikasi
**Model:** `app/Models/NotifikasiRules.php`  
**Tabel:** `notifikasi_rules`

Tabel ini menyimpan aturan pengiriman pesan, yang dikonfigurasi melalui `KonfigurasiPesanController`.
- Kolom kunci: `kategori`, `template_pesan`, `interval_hari`, `is_active`.
- Aturan dengan `interval_hari = 0` digunakan sebagai template pesan manual.

### 4.2 Notifikasi Alert Sistem Berkelompok
**Notification Class:** `app/Notifications/SystemAlertNotification.php`

Class notifikasi serbaguna yang berjalan di dua channel:
1. **`mail` (SMTP Email)**: Merender template `emails.manual_notification` dengan struktur tata letak berwujud kartu berwarna biru profesional PUSDATIN. Jika property `$pdfData` diisi, class ini secara otomatis merender template `emails.rekap_usulan_pdf` ke dalam PDF menggunakan DomPDF dan menyematkannya sebagai attachment (`Rekap_Usulan_Kepegawaian.pdf`).
2. **`database` (Notifikasi Lonceng)**: Menyimpan detail notifikasi ke tabel `notifications` untuk dibaca oleh `NotificationController` di panel admin. JSON format:
   ```json
   {
     "title": "📄 [Subjek Pesan]",
     "message": "[Konten Pesan]",
     "type": "info"
   }
   ```

### 4.3 Pemicu Notifikasi (Trigger Notification)

#### A. Notifikasi Hasil Tracker Harian (`RecalculateTracker`)
Setiap kali command schedule `php artisan notify:recalculate` (atau `RecalculateTracker`) dijalankan:
1. **Notifikasi Rekap Admin**:
   - Jika mendeteksi adanya usulan baru dengan status `Usulan`, sistem akan merekap seluruh usulan baru tersebut berdasarkan bidang/kategori.
   - Mengirim notifikasi email tunggal kepada seluruh Administrator yang terdaftar dengan menyertakan lampiran PDF ringkasan daftar usulan pegawai baru (`emails.rekap_usulan_pdf`).
2. **Notifikasi Personal Pegawai**:
   - Sistem mengirim notifikasi email personal ke masing-masing pegawai yang terkena usulan baru.
   - Konten email diambil dari template `notifikasi_rules` berdasarkan kategori usulan tracker tersebut (contoh placeholder: `{nama}`, `{nip}`, `{kategori}` otomatis diganti dengan data pegawai asli). Jika template rules kosong, sistem menggunakan teks cadangan (*fallback message*) di dalam struktur `switch-case`.

#### B. Notifikasi Periodik (`SendPeriodicNotifications`)
Command schedule `notify:periodic` (dijalankan via cron) mengirimkan notifikasi otomatis ke pegawai dengan kriteria jabatan fungsional atau struktural (`tipe_jabatan` dinormalisasi):
- **Notifikasi Tahunan**: Berjalan setiap tanggal **1 Januari**. Menggunakan template dari aturan notifikasi `Notifikasi Tahunan`. Placeholder `{tahun}` diganti dengan tahun berjalan.
- **Notifikasi Triwulanan**: Berjalan setiap tanggal **1 pada bulan Januari, April, Juli, dan Oktober**. Menggunakan template dari aturan notifikasi `Notifikasi Triwulan`. Menyediakan deadline otomatis yaitu akhir bulan berjalan (`{deadline}`).
- **Resolusi Saluran Pengiriman**:
  - Jika pegawai memiliki akun `User` di dashboard (username = NIP), notifikasi dikirimkan ke model User (masuk ke Email + Lonceng Database).
  - Jika tidak ada akun User tetapi email pegawai terisi di profilnya, notifikasi dikirim ke rute email langsung menggunakan `Notification::route('mail', $pegawai->email)`.

#### C. Kirim Notifikasi Manual (`DataPegawaiController`)
Admin dapat mengirim notifikasi manual ke pegawai tertentu dari daftar pegawai:
- **Metode**: `sendNotification(Request $request, string $id)`
- **Alur**: Admin memilih template dari `NotifikasiRules` (atau menulis pesan custom). Sistem mengganti placeholder: `{nama}`, `{nip}`, `{jabatan}`, `{pangkat}`.
- **Dynamic Detail Diklat**: Khusus template diklat, sistem melacak riwayat diklat pegawai yang bermasalah (status diklat = 1 tapi file sertifikat & arsip kosong) melalui model `RiwayatDiklat` dan menuliskannya di placeholder `{detail_diklat}`.
- **Pengiriman**: Menggunakan `Mail::to($pegawai->email)->send(new ManualNotification(...))` dan mencatat log kirim ke tabel `logs` dengan tipe `NOTIF_SENT`.

#### D. Pendaftaran UKOM Manual (`DashboardController`)
Ketika admin memindahkan pegawai ke kategori UKOM (`moveToUkom`), sistem otomatis mengirim pemberitahuan email personal dengan subject "Pemberitahuan Uji Kompetensi (UKOM)" menggunakan mailable `App\Mail\ManualNotification`.

---

## 5. Struktur Database Terkait

### 5.1 Tabel `lampiran_cetak_surat`
Menyimpan informasi berkas lampiran yang diunggah untuk dicetak.
```sql
CREATE TABLE lampiran_cetak_surat (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    dashboard_tracker_id INT NOT NULL,          -- Relasi ke dashboard_tracker
    nip VARCHAR(50) NOT NULL,                    -- NIP pegawai pemilik berkas
    nama_dokumen VARCHAR(255) NOT NULL,          -- Nama file asli/deskripsi default
    judul_lampiran VARCHAR(255) NULL,            -- Judul lampiran yang tampil di cetakan PDF
    file_path VARCHAR(255) NOT NULL,             -- Path relatif file di storage/app/public/
    mime_type VARCHAR(100) NOT NULL,             -- Tipe file (image/jpeg, application/pdf, dll)
    urutan INT DEFAULT 1,                        -- Urutan penempelan halaman lampiran
    halaman_cetak INT DEFAULT 1,                 -- Nomor grup halaman cetak
    ukuran_bytes INT NOT NULL,                   -- Ukuran file dalam bytes
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (dashboard_tracker_id) REFERENCES dashboard_tracker(id) ON DELETE CASCADE
);
```

### 5.2 Tabel `notifikasi_rules`
Menyimpan konfigurasi template dan interval notifikasi berkala.
```sql
CREATE TABLE notifikasi_rules (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    kategori VARCHAR(255) NOT NULL,              -- Kategori notifikasi (contoh: KGB, Notifikasi Tahunan, dll)
    template_pesan TEXT NOT NULL,                -- Isi template dengan placeholder seperti {nama}, {nip}
    interval_hari INT DEFAULT 0,                 -- Interval hari (0 = manual/template, >0 = periodik)
    is_active TINYINT(1) DEFAULT 1,              -- Status aktif/tidak aktif aturan
    updated_by BIGINT NULL,                      -- Admin terakhir yang memperbarui aturan ini
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);
```

---

## 6. Struktur File Terkait

```
app/
├── Console/
│   └── Commands/
│       ├── RecalculateTracker.php       ← Pemicu notifikasi usulan baru & rekap admin harian
│       └── SendPeriodicNotifications.php← Pemicu notifikasi otomatis Tahunan & Triwulanan
├── Http/
│   └── Controllers/
│       ├── DashboardController.php      ← Endpoints cetak surat & bundle usul KJ
│       ├── KonfigurasiPesanController.php← CRUD konfigurasi NotifikasiRules
│       ├── LampiranController.php       ← CRUD lampiran & kompresi gambar GD
│       ├── NotificationController.php   ← Notifikasi lonceng DB untuk administrator
│       └── SuratPengajuanController.php ← AJAX preview, konfirmasi usulan & download PDF
├── Mail/
│   └── ManualNotification.php           ← Mailable untuk email manual & alert UKOM
├── Models/
│   ├── DashboardTracker.php             ← Model status pelacak karir pegawai
│   ├── LampiranCetakSurat.php           ← Model database kelengkapan lampiran
│   └── NotifikasiRules.php              ← Model database konfigurasi template email
├── Notifications/
│   └── SystemAlertNotification.php      ← Class notifikasi serbaguna (mail + db lonceng)
└── Services/
    └── SuratPengajuanService.php        ← Jantung pengolah PDF & merge halaman lampiran FPDI

resources/views/
├── emails/
│   ├── manual_notification.blade.php    ← Template HTML email dengan tata letak kartu biru
│   ├── password_reset.blade.php         ← Template email reset password akun
│   └── rekap_usulan_pdf.blade.php       ← Template HTML-to-PDF rekap usulan baru untuk admin
└── surat/
    ├── surat_pengajuan_pdf.blade.php    ← Template HTML-to-PDF pengajuan umum
    ├── surat_pengajuan_tubel_pdf.blade.php← Template HTML-to-PDF pengaktifan kembali TUBEL
    └── surat_usul_kj_pdf.blade.php      ← Template HTML-to-PDF pengajuan kenaikan jenjang (KJ)
```

---

## 7. Dependency yang Digunakan

| Package | Versi | Kegunaan |
|---------|-------|----------|
| `barryvdh/laravel-dompdf` | `^3.1` | Mengonversi template view HTML/Blade menjadi file PDF standar. |
| `setasign/fpdi` | `^2.6` | Mengimpor halaman dari file PDF eksternal yang diunggah untuk digabung/di-merge ke PDF dasar. |
| `setasign/fpdf` | `^1.8` | Engine dasar manipulasi PDF yang dibutuhkan oleh FPDI. |
| `ext-gd` | *Ekstensi PHP* | Library manipulasi gambar bawaan PHP untuk melancarkan kompresi resolusi gambar lampiran. |

---

## 8. Kesimpulan

Pekerjaan yang dilakukan Person 3 mencakup **otomatisasi administrasi fisik dan komunikasi digital** pada sistem. Dengan mengintegrasikan DomPDF dan FPDI, sistem mampu memaketkan surat pengantar dinas beserta seluruh file lampiran pendukungnya (baik berupa gambar maupun PDF terpisah) ke dalam satu file PDF ringkas yang siap dicetak. Proses kompresi gambar bawaan memastikan penyimpanan tetap efisien, sementara sistem penjadwalan notifikasi SMTP menjamin pegawai menerima alert usulan kenaikan pangkat/jabatan tepat waktu secara berkala.
