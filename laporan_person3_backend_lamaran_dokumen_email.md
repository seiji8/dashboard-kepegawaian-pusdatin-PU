# Laporan Teknis: Person 3 - Backend Kelengkapan Dokumen, Cetak Surat, & Email Notifikasi
**SISTEM DASHBOARD KEPEGAWAIAN PUSDATIN KEMENPUPR**

Dokumen ini memuat laporan teknis mendalam mengenai sistem manajemen kelengkapan berkas fisik pegawai, mekanisme pembuatan surat dinas otomatis (PDF Generator & PDF Merger), serta mesin notifikasi email otomatis. Laporan ini disusun untuk **Developer 3 (Person 3 - Backend Dokumen & Email)**.

---

## 📁 1. Sistem Manajemen Kelengkapan Dokumen

Aplikasi melacak kelengkapan berkas fisik pendukung untuk setiap usulan kepegawaian melalui tabel `kelengkapan_dokumen`.

### A. Alur Validasi Dokumen Persyaratan

Setiap kategori usulan tracker memiliki berkas wajib yang berbeda. Validasi dilakukan di dalam tracker service:
1.  **KGB**: Membutuhkan **1 dokumen** (SK KGB Terakhir / SK KGB Baru).
2.  **KP Reguler**: Membutuhkan **1 dokumen** (SK Pangkat Terakhir).
3.  **TUBEL**: Membutuhkan **1 dokumen** (SK Tugas Belajar).
4.  **KJ Jafung / KP Jafung**: Membutuhkan **2 dokumen** (SK Jabatan Fungsional Terakhir dan SK PAK / Angka Kredit Terakhir).

Sistem secara dinamis mendeteksi keberadaan file-file tersebut di dalam tabel riwayat pegawai (seperti tabel `riwayat_jabatan` atau `riwayat_tubel` hasil sinkronisasi API). Jika file terdeteksi, kolom `is_uploaded` pada tabel `kelengkapan_dokumen` disinkronisasikan menjadi `1` secara otomatis tanpa intervensi manual admin.

---

## 🖨️ 2. Sistem Pembuatan & Penggabungan Berkas PDF (Cetak Surat)

Mekanisme cetak surat otomatis dipusatkan pada kelas **[SuratPengajuanController](file:///c:/laragon/www/dashboard-kepegawaian/app/Http/Controllers/SuratPengajuanController.php)**.

### A. Generator PDF Nota Dinas (DomPDF)
*   Sistem men-generate dokumen Nota Dinas resmi Pusdatin PUPR berdasarkan template HTML Blade. Salah satu template yang krusial adalah **`surat_pengajuan_tubel_pdf.blade.php`** (Nota Dinas Pengaktifan Kembali Tugas Belajar).
*   Data penerima kop surat, nomor surat dinas, data diri pegawai (NIP, pangkat, jabatan), serta keterangan kustom (seperti input "pendidikan terakhir") dirender langsung secara dinamis ke dalam format file PDF menggunakan library **Barryvdh DomPDF**.

### B. Penggabungan Lampiran Berkas (PDF Merger - FPDI)
*   Sistem mendukung penggabungan otomatis (*merge*) file lampiran pendukung fisik yang diunggah oleh admin (seperti file ijazah atau SK pangkat) di belakang PDF Nota Dinas utama.
*   **Implementasi FPDI**: Menggunakan library **FPDI (Setasign)** untuk membuka halaman demi halaman berkas PDF lampiran, membaca isinya, dan menyalinnya ke halaman ekor PDF utama yang baru digenerate:
    ```php
    use Setasign\Fpdi\Fpdi;
    
    $pdf = new Fpdi();
    // 1. Tulis halaman Nota Dinas utama hasil DomPDF
    // 2. Baca file lampiran fisik yang diunggah dari storage
    $pageCount = $pdf->setSourceFile($filePathLampiran);
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $templateId = $pdf->importPage($pageNo);
        $pdf->AddPage();
        $pdf->useTemplate($templateId);
    }
    $pdf->Output('F', $outputPathBundle);
    ```
*   Admin dapat mengunduh berkas lengkap usulan yang telah terbundel dalam 1 file PDF siap cetak/TTE.

---

## ✉️ 3. Mesin Notifikasi Email Otomatis (SMTP & Queue)

Sistem mengirimkan pemberitahuan otomatis ke alamat email pegawai yang bersangkutan pada saat status kepegawaiannya bergeser ke fase penting (seperti `Usulan` KGB, `Proses Pengaktifan` TUBEL, atau `Upload E-HRM`).

### A. Pengiriman Pesan Dinamis & Notifikasi Template

Templat kalimat notifikasi disimpan secara dinamis di database dalam tabel `notifikasi_rules` agar dapat disesuaikan oleh administrator melalui halaman konfigurasi tanpa mengubah kode program.

*   **Pemicu Notifikasi**:
    Sistem memanggil kelas notification bawaan Laravel: **`SystemAlertNotification`**.
*   **Proses Penggantian Placeholders**:
    Sebelum email dikirim, penanda placeholder di dalam template digantikan secara dinamis dengan profil asli pegawai:
    ```php
    $rule = NotifikasiRules::where('kategori', 'KGB Upload Dokumen')->first();
    $pesan = str_replace(
        ['{nama}', '{nip}', '{deadline}', '{missing_documents}'],
        [$pegawai->nama, $pegawai->nip, $tmt->format('d-m-Y'), $missingDocsStr],
        $rule->template_pesan
    );
    ```
*   **Template Email Fisik**:
    Menggunakan HTML dengan kop resmi instansi dan tata letak premium yang disimpan di file `resources/views/emails/kgb_notification.blade.php`.

### B. Konfigurasi Antrean & SMTP (.env)

Untuk performa tinggi dan menghindari penundaan (*delay*) bagi pengguna admin, antrean pengiriman email disetel menggunakan koneksi **`sync`** pada server lokal (di mana pengiriman diproses instan), tetapi siap dialihkan menggunakan driver **`redis`** atau **`database`** queue pada server produksi skala besar.

Konfigurasi mail server memanfaatkan jalur SMTP Gmail App Password atau SMTP Kedinasan:
```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=admin.kepegawaian@pu.go.id
MAIL_PASSWORD="app_password_google"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=admin.kepegawaian@pu.go.id
MAIL_FROM_NAME="Admin Kepegawaian Pusdatin"
```
Dengan konfigurasi ini, email peringatan KGB dan kelengkapan dokumen terkirim tepat waktu langsung ke kotak masuk pegawai.
