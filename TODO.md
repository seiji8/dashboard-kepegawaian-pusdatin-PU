# 📋 TODO — DashboardAlert Kepegawaian

> Dokumen ini berisi daftar pekerjaan yang **wajib diselesaikan** sebelum sistem di-deploy ke production dan/atau sebelum *Security Assessment* dilakukan.
> 
> **Terakhir diperbarui:** 28 April 2026

---

## 🔴 KRITIS — Wajib Sebelum Deploy Production

### 1. [x] Aktifkan Pengiriman Email Reset Password
- **File:** `app/Http/Controllers/AuthController.php` (line 122-125)
- **Status:** **SELESAI** — Blok `Mail::send(...)` sudah di-uncomment dan template email premium `emails.password_reset` sudah terverifikasi aktif.
- **Yang sudah dilakukan:**
  - Uncomment blok `Mail::send(...)` di method `sendResetLinkEmail()`
  - Buat/verifikasi template email `emails.password_reset`
  - *Catatan:* Konfigurasi SMTP di `.env` (MAIL_HOST, MAIL_PORT, dst.) tetap wajib dikonfigurasi ke server email sungguhan di environment target.

### 2. Set `APP_DEBUG=false` di Production
- **File:** `.env` di server production (Reminder saja, tidak diubah di local dev)
- **Status:** Saat ini `APP_DEBUG=true` di local dev (benar untuk development, BAHAYA untuk production)
- **Yang harus dilakukan saat deploy:**
  ```env
  APP_ENV=production
  APP_DEBUG=false
  ```
- **Risiko jika lupa:** Setiap error akan menampilkan stack trace lengkap ke browser (termasuk path file, DB credentials, environment variables)

### 3. [x] Ganti `env()` dengan `config()` di Controller & Command
- **Status:** **SELESAI** — Parameter eksternal sudah dipindahkan ke `config/ehrm.php` dan semua controller/command telah diubah menggunakan `config()`.
- **Yang sudah dilakukan:**
  - Mengubah `DatabaseBackupController.php` ke `config('database.connections.mysql.database')`.
  - Mengubah `SyncEhrmData.php` untuk mengambil base_url, api_key, dll dari config.
  - Membuat berkas `config/ehrm.php` baru.
  - *Manfaat:* Menghindari malfungsi database backup dan API sinkronisasi setelah menjalankan command `php artisan config:cache` di production.

---

## ⚠️ PENTING — Sangat Disarankan Sebelum Security Assessment

### 4. Force Change Password untuk Admin Baru
- **File yang terdampak:**
  - `app/Http/Controllers/AdminController.php` — password default admin baru = NIP pegawai (data publik)
- **Yang harus dilakukan:**
  - Buat middleware baru `ForcePasswordChange`
  - Logika: cek `Hash::check($user->username, $user->password)` — jika true, berarti password masih = NIP
  - Redirect ke halaman ganti password dengan pesan peringatan
  - Daftarkan middleware ini ke route group `auth`
- **Risiko jika lupa:** Akun admin baru yang belum mengganti password rentan diakses oleh siapapun yang mengetahui NIP-nya

### 5. Hapus File Development dari Production
- **Folder:** `storage/app/dev_scripts/`
- **Yang harus dilakukan:**
  - Pastikan folder `dev_scripts` tidak ikut ter-deploy ke production
  - Tambahkan ke `.gitattributes`: `storage/app/dev_scripts/ export-ignore`
  - Atau hapus manual setelah deploy

---

## 🟡 CHECKLIST DEPLOYMENT

Jalankan semua perintah berikut di server production **setelah** code terbaru di-pull:

```bash
# 1. Install dependencies (tanpa dev)
composer install --no-dev --optimize-autoloader

# 2. Set environment
#    Pastikan .env sudah dikonfigurasi dengan benar:
#    - APP_ENV=production
#    - APP_DEBUG=false
#    - DB_*, MAIL_*, EHRM_* sudah terisi

# 3. Generate application key (HANYA jika belum ada)
php artisan key:generate

# 4. Jalankan migrasi database
php artisan migrate --force

# 5. Cache konfigurasi (WAJIB setelah fix env() → config())
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Set permission folder storage
chmod -R 775 storage bootstrap/cache
```

---

## 🟢 FITUR YANG SUDAH SELESAI (Referensi)

- [x] Login / Logout dengan Rate Limiter (anti brute force)
- [x] Session regeneration setelah login
- [x] CSRF protection di semua form
- [x] RBAC (Super Admin vs Admin Pegawai) di halaman Daftar Admin
- [x] Activity Logging untuk semua aksi admin
- [x] Backup Database (Super Admin only) + auto-delete temp file
- [x] Export PDF Log Aktivitas (dengan filter cerdas)
- [x] Token reset password dihapus dari response browser
- [x] Anti user enumeration di halaman Forgot Password
- [x] Otorisasi Super Admin di fitur Backup Database diaktifkan

---

## 📌 FITUR PENDING (Menunggu Dependensi)

| Fitur | Menunggu Apa | PIC |
|---|---|---|
| Template Surat Pengajuan (yang kurang) | Template resmi dari atasan | - |
| Integrasi API E-HRM | Teman sedang mengerjakan API | Teman |

---

## 🔵 PENGEMBANGAN FITUR — Kebutuhan Fungsional

### 1. Menambahkan Jumlah Terlampir di Surat Usulan
- **Modul:** Surat Pengajuan
- **Deskripsi:** Menampilkan **jumlah PNS yang terlampir** di dalam dokumen surat usulan.
- **Detail:**
  - Tambahkan field "Jumlah Terlampir" di header/body surat
  - Tampilkan daftar jumlah PNS per kategori yang ikut terlampir
  - Pastikan angka ini sinkron dengan data pegawai yang di-*checklist* saat generate surat

### 2. Generate Template Surat Setiap Kategori
- **Modul:** Surat Pengajuan (`SuratPengajuanService.php`)
- **Deskripsi:** Buat template surat resmi untuk kategori yang belum tersedia.
- **⚠️ PENTING:** Pencetakan surat usulan dari sistem ini **HANYA** untuk 3 kategori berikut:
  - [ ] **Kenaikan Jenjang (KJ)** — template surat usulan kenaikan jenjang jabatan fungsional
  - [ ] **UKOM (Uji Kompetensi)** — template surat pengantar/usulan UKOM
  - [ ] **TUBEL (Tugas Belajar)** — template surat usulan tugas belajar
- **TIDAK PERLU dibuat surat untuk:**
  - ~~Kenaikan Pangkat (KP)~~ → surat langsung dari **E-HRM**
  - ~~Kenaikan Gaji Berkala (KGB)~~ → surat langsung dari **E-HRM**
- **🔴 AKSI: Hapus/Nonaktifkan Template KP & KGB yang Sudah Ada**
  - Saat ini di dashboard **sudah terlanjur ada** fitur generate surat KP dan KGB (dibuat sebelum rapat, akibat miskomunikasi)
  - [ ] Hapus atau nonaktifkan tombol "Generate Surat" untuk kategori KP di dashboard
  - [ ] Hapus atau nonaktifkan tombol "Generate Surat" untuk kategori KGB di dashboard
  - [ ] Bersihkan logic KP/KGB di `SuratPengajuanService.php` (atau tandai deprecated)
  - [ ] Pastikan modal surat di `dashboard/index.blade.php` tidak menampilkan opsi KP & KGB
- **File terkait:**
  - `app/Services/SuratPengajuanService.php` — logic generate PDF
  - `app/Http/Controllers/SuratPengajuanController.php` — controller
  - `resources/views/dashboard/index.blade.php` — modal surat di dashboard
  - `resources/views/surat/` — blade template untuk masing-masing kategori

### 3. Merevisi Integrasi API Baru
- **Modul:** Sinkronisasi E-HRM (`SyncEhrmData.php`)
- **Deskripsi:** Teman sedang mengerjakan API versi baru. Perlu dicek apakah:
  - [ ] API baru **digabung** dengan endpoint lama (extend logic yang sudah ada)
  - [ ] API baru **dipisah** menjadi command/controller terpisah
- **Yang harus dilakukan:**
  - Review dokumentasi/endpoint API baru dari teman
  - Bandingkan struktur response JSON lama vs baru
  - Pastikan logic "Sabuk Pengaman" (proteksi data `null`) di `SyncEhrmData.php` tetap berfungsi
  - Update mapping field jika ada perubahan nama kolom

### 4. Memperbaiki Logic/Rules Setiap Modul
- **Modul:** Semua modul (Dashboard Tracker, Notifikasi, Surat)
- **Deskripsi:** Jika ada penyesuaian aturan baru dari kebijakan kepegawaian, perlu dicek ulang:
  - [ ] Rules di `RecalculateTracker.php` — kapan tracker aktif, kapan selesai
  - [ ] Rules di `NotifikasiRules` — interval pengiriman notifikasi
  - [ ] Rules di `SuratPengajuanService.php` — format dan syarat dokumen per kategori
  - [ ] Validasi status transisi (Usulan → Proses TTE → Upload E-HRM)
- **Catatan:** Setiap perubahan logic wajib dicek dampaknya ke modul lain

### 5. Penyesuaian Penarikan Data Otomatis Jadi 1x Sebulan
- **Modul:** Task Scheduling (`app/Console/Kernel.php`)
- **Deskripsi:** Mengubah frekuensi sinkronisasi data dari API E-HRM menjadi **1 kali per bulan**.
- **Yang harus dilakukan:**
  - [ ] Ubah jadwal di `Kernel.php` (dari `daily()` atau `weekly()` menjadi `monthly()` atau `monthlyOn(1, '02:00')`)
  - [ ] Pastikan command `tracker:run` (RecalculateTracker) juga disesuaikan jadwalnya
  - [ ] Pertimbangkan menambahkan notifikasi ke Super Admin setelah sync bulanan selesai

### 6. Membuat Modul TUBEL (Tugas Belajar) dan Flow Alur Prosesnya
- **Modul:** Fitur baru — TUBEL
- **Deskripsi:** Menambahkan modul lengkap untuk mengelola Tugas Belajar pegawai.
- **Yang harus dilakukan:**
  - [ ] Desain alur proses TUBEL (dari pengajuan sampai selesai)
  - [ ] Buat/update tabel database jika diperlukan (misal: `tubel_trackers`)
  - [ ] Tambahkan kategori TUBEL di Dashboard Tracker
  - [ ] Buat template surat usulan TUBEL (lihat poin #2)
  - [ ] Tambahkan kelengkapan dokumen yang diperlukan untuk TUBEL
  - [ ] Integrasikan dengan sistem notifikasi yang sudah ada

### 7. Munculkan Data Kelengkapan Dokumen (Ada / Tidak Ada + Keterangan)
- **Modul:** Data Pegawai (Modal Detail) & Dashboard Tracker
- **Deskripsi:** Menampilkan status kelengkapan dokumen setiap pegawai secara lengkap.
- **Detail:**
  - [ ] Tampilkan daftar dokumen yang **sudah ada** dan yang **belum ada**
  - [ ] Sertakan kolom **keterangan** untuk setiap dokumen
  - [ ] Status saat ini: **masih dummy** — perlu dihubungkan ke data riil dari API atau input manual
  - [ ] Pertimbangkan fitur upload dokumen oleh admin jika diperlukan
