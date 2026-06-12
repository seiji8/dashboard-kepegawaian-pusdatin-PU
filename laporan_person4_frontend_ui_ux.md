# Laporan Teknis: Person 4 - Frontend UI/UX & Client-Side Interactivity
**SISTEM DASHBOARD KEPEGAWAIAN PUSDATIN KEMENPUPR**

Dokumen ini memuat laporan teknis mendalam mengenai arsitektur antarmuka (UI), kerangka tata letak, implementasi desain responsif premium (Tailwind CSS v4 + Modular CSS), serta logika skrip interaktivitas client-side. Laporan ini disusun untuk **Developer 4 (Person 4 - Frontend UI/UX & Client JS)**.

---

## 🎨 1. Arsitektur Tata Letak & Desain (CSS & Tailwind v4)

Aplikasi dibangun menggunakan kerangka **Blade Templates** dengan sistem styling berbasis **Tailwind CSS v4** yang diintegrasikan secara modular bersama stylesheet kustom (Vanilla CSS).

### A. Modular CSS Structure (Aset Stylesheet)

Aset stylesheet disimpan di folder `resources/css` dan digabungkan di **[app.css](file:///c:/laragon/www/dashboard-kepegawaian/resources/css/app.css)** menggunakan direktif `@import`:

1.  **[layout.css](file:///c:/laragon/www/dashboard-kepegawaian/resources/css/layout.css)**:
    Mengatur pembagian grid sistem utama dashboard.
    *   `.app-container`: Mengatur layout grid utama (Sidebar + Main Content).
    *   `.sidebar`: Sidebar navigasi sebelah kiri (lebar tetap, berperekat/sticky).
    *   `.main-content`: Pembungkus navigasi atas dan area konten dinamis.
2.  **[components.css](file:///c:/laragon/www/dashboard-kepegawaian/resources/css/components.css)**:
    Mengatur kelas UI terstandardisasi (design tokens):
    *   *Cards*: Panel ringkasan data statistik dengan bayangan halus (*box-shadow*) dan efek melayang (*hover transitions*).
    *   *Accordions*: Panel tracker kepegawaian yang bisa ciut-kembang (collapsible) dengan indikator panah dinamis.
    *   *Badges & Labels*: Label status berwarna harmonis (merah untuk Usulan, kuning untuk Proses, hijau untuk Aman).
3.  **Styles Spesifik Halaman (Folder `pages/`)**:
    Menyimpan gaya khusus per halaman, seperti:
    *   `dashboard.css` (gaya grafik/kartu).
    *   `data-pegawai.css` (gaya pencarian, list, detail modal).
    *   `log-aktivitas.css` (gaya lini masa/timeline aktivitas).

---

## 🖥️ 2. Halaman Utama (Views & Blades)

Kerangka halaman diatur dalam folder `resources/views`:

1.  **[app.blade.php](file:///c:/laragon/www/dashboard-kepegawaian/resources/views/layouts/app.blade.php) (Layout Induk)**:
    Memuat pustaka font Google (*Inter*), berkas CSS terkompilasi melalui Vite (`@vite`), Font Ikon (*Phosphor Icons*), SweetAlert2, Driver.js, dan membungkus komponen navigasi Sidebar & Navbar.
2.  **`dashboard/index.blade.php` (Dashboard Utama)**:
    Menyajikan ringkasan metrik statistik pegawai, serta bilah accordion pelacak untuk status KGB, Kenaikan Pangkat, Kenaikan Jenjang, Ukom, dan Tugas Belajar.
3.  **`data_pegawai/` (Manajemen Profil & Berkas Pegawai)**:
    *   Menampilkan daftar seluruh pegawai dinas dengan fitur pencarian real-time dan penyaringan (*filtering*) berdasarkan tipe jabatan atau golongan.
    *   Menyediakan tombol detail untuk memunculkan modal profil pegawai dan status kelengkapan dokumen fisiknya.
4.  **`log_aktivitas/index.blade.php` (Audit Trail)**:
    Menampilkan tabel log audit sistem (aktivitas admin, riwayat sync API) dengan opsi unduhan laporan PDF.

---

## ⚡ 3. Logika Client-Side & Interaktivitas JavaScript

Sistem menggunakan JavaScript modern murni (Vanilla JS) untuk interaksi dinamis tanpa membebani browser.

### A. Bypass Cache Pratinjau PDF (PDF Preview Reload)

Saat admin memperbarui dokumen atau mengunggah lampiran baru, browser kerap menampilkan dokumen lama karena caching iframe. Logika di **`dashboard-surat.js`** mengatasi ini dengan mengkloning elemen iframe dan menambahkan timestamp query parameter saat memuat ulang pratinjau:

```javascript
function reloadPdfPreview(iframeId, pdfUrl) {
    const iframe = document.getElementById(iframeId);
    if (iframe) {
        const timestampUrl = pdfUrl + '?t=' + new Date().getTime();
        const clone = iframe.cloneNode(true);
        clone.src = timestampUrl;
        iframe.parentNode.replaceChild(clone, iframe);
    }
}
```

---

### B. Driver.js (Panduan Tur Interaktif Pengguna)

Untuk membantu administrator baru memahami cara kerja sistem, diintegrasikan pustaka **Driver.js** pada saat login pertama kali:
*   Membimbing pengguna dari kartu statistik utama, accordion tracker kepegawaian, tombol sinkronisasi API e-HRM, hingga ke menu pencarian pegawai.
*   Konfigurasi langkah tur dimuat secara terpisah menggunakan `@yield('tour')` di bagian bawah master blade.

---

### C. SweetAlert2 & Konfirmasi Tindakan Sensitif

SweetAlert2 digunakan untuk memberikan pengalaman konfirmasi yang intuitif dan aman bagi administrator:
1.  **Konfirmasi Hapus Admin**:
    Memunculkan modal konfirmasi dengan validasi ketat sebelum menembak rute penghapusan admin (`DELETE /daftar-admin/{id}`).
2.  **Konfirmasi checklist / Kelulusan UKOM**:
    Menampilkan modal pop-up yang mengonfirmasi data kelulusan pegawai secara tepat, menyajikan nama lengkap pegawai (bukan ID) untuk menghindari salah checklist data.
3.  **Animasi Sinkronisasi e-HRM**:
    Saat tombol "Sinkronisasi Sekarang" diklik, JavaScript memicu pemunculan *Sync Loading Overlay* yang menutupi layar dan mencegah admin menekan tombol berkali-kali selama proses tarik data API berlangsung.
4.  **Verifikasi Backup Database**:
    Menampilkan animasi sukses yang halus begitu file ekspor database SQL siap diunduh oleh admin.
