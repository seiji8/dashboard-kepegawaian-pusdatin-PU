# Dashboard Kepegawaian & Notifikasi KGB

Dashboard ini berfungsi untuk memonitoring Kenaikan Gaji Berkala (KGB) pegawai dan mengirimkan notifikasi otomatis via Email kepada pegawai yang bersangkutan.

## 🚀 Fitur Utama
1. **Sync Data Pegawai**: Mengambil data terbaru dari API e-HRM PU.
2. **Tracker KGB Otomatis**: Menghitung jadwal KGB berikutnya berdasarkan TMT terakhir.
3. **Notifikasi Cerdas**:
   - **Mendekati (H-2 Bulan)**: Notifikasi di Dashboard Admin.
   - **Usulan (H-0 Bulan)**: Kirim **EMAIL** otomatis ke Pegawai & Notifikasi Dashboard.
4. **Email Custom**: Menggunakan template HTML dengan kop surat resmi.

---

## 🛠️ Instalasi & Setup

Jika baru pertama kali install di komputer lain:

1. **Clone Repo & Install Dependencies**
   ```bash
   git clone <repo_url>
   cd dashboard-kepegawaian
   composer install
   npm install && npm run build
   ```

2. **Setup Environment (.env)**
   Copy file `.env.example` menjadi `.env`, lalu atur konfigurasi berikut:
   
   **Database:**
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=db_kepegawaian
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   
   **Email (SMTP Gmail):**
   ```ini
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=email_anda@gmail.com
   MAIL_PASSWORD="app_password_anda"
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=email_anda@gmail.com
   MAIL_FROM_NAME="Admin Kepegawaian"
   ```
   *Catatan: Password adalah "App Password" Google, bukan password login biasa.*

   **PENTING: Queue Connection**
   Agar email langsung terkirim tanpa delay/worker, set:
   ```ini
   QUEUE_CONNECTION=sync
   ```

3. **Generate Key & Migrasi Database**
   ```bash
   php artisan key:generate
   php artisan migrate:fresh --seed
   ```
   *Perintah di atas akan mereset database dan mengisi data dummy (admin & pegawai).*

---

## ⚡ Command Wajib (Sering Digunakan)

Berikut adalah perintah-perintah yang **WAJIB** Anda ketahui untuk operasional harian:

### 1. Menjalankan Tracker & Kirim Notifikasi (Paling Penting!)
Perintah ini akan mengecek status KGB semua pegawai. Jika ada yang masuk masa "Usulan", sistem akan otomatis mengirim email.
```bash
php artisan tracker:run
```
Disarankan untuk menjalankan perintah ini **setiap hari** (bisa diset di cronjob server) atau dijalankan manual saat ingin update status.

### 2. Sinkronisasi Data Pegawai (API e-HRM)
Mengambil data terbaru dari server pusat (Update Data, Jabatan, Pangkat, dll).
```bash
php artisan ehrm:sync
```

### 3. Reset & Isi Ulang Database (TMT Manual)
Jika data kacau dan ingin reset bersih, gunakan perintah ini. Ini juga otomatis menjalankan `UpdateTmtManualSeeder` untuk memperbaiki data TMT dummy.
```bash
php artisan migrate:fresh --seed
```

### 4. Menjalankan Server Lokal
```bash
php artisan serve
```

---

## 📤 Logika Notifikasi

- **Status "Aman"**: Jarak ke KGB > 2 bulan. (Tidak ada notifikasi).
- **Status "Mendekati"**: Jarak ke KGB <= 2 bulan. (Notifikasi masuk ke Dashboard Admin).
- **Status "Usulan"**: Bulan KGB sudah tiba atau lewat. (Email terkirim ke Pegawai + Notifikasi Dashboard).

**Catatan Email:**
- Email dikirim ke alamat yang terdaftar di database `pegawai`.
- Pengirim otomatis menggunakan nama "Admin Kepegawaian" (bisa diubah di `.env`).
- Template email ada di `resources/views/emails/kgb_notification.blade.php`.

---

## ❓ Troubleshooting

**Q: Email tidak masuk?**
1. Cek folder **Spam** di email tujuan.
2. Pastikan `QUEUE_CONNECTION=sync` di `.env`.
3. Pastikan `MAIL_PASSWORD` di `.env` sudah benar (jangan pakai password login Gmail, pakai App Password).
4. Cek log error di: `storage/logs/laravel.log`.

**Q: Data Pegawai di Dashboard tidak muncul?**
Coba jalankan:
```bash
php artisan ehrm:sync
```
Lalu jalankan tracker lagi:
```bash
php artisan tracker:run
```

---
*Dibuat oleh Tim Pengembang - 2026*
