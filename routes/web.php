<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogAktivitasController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DataPegawaiController;
use App\Http\Controllers\KonfigurasiPesanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SuratPengajuanController;
use App\Http\Controllers\DatabaseBackupController;

// 1. Halaman Depan (Redirect ke Login aja)
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Rute Authentication (Login/Logout)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Forgot Password Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/validate-reset-link/{token}', [AuthController::class, 'validateAndRedirect'])->name('password.validate');
Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
Route::view('/password-reset-success', 'auth.reset_success')->name('password.success');

// Change Password Routes (Authenticated Custom)
Route::middleware(['auth'])->group(function () {
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change-password.update');
    Route::get('/backup-database', [DatabaseBackupController::class, 'download'])->name('database.backup');

    // Force Change Password Routes
    Route::get('/force-change-password', function() {
        return view('auth.force_change_password');
    })->name('password.force-change');
    Route::post('/force-change-password', [AuthController::class, 'forceChangePasswordUpdate'])->name('password.force-change.update');
});

// 3. Rute Halaman Admin (Harus Login Dulu)
Route::middleware(['auth', \App\Http\Middleware\ForcePasswordChange::class])->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/tracker/{id}/confirm', [DashboardController::class, 'confirmTracker'])->name('tracker.confirm');
    Route::post('/tracker/{id}/move-to-ukom', [DashboardController::class, 'moveToUkom'])->name('tracker.move-to-ukom');
    Route::post('/tracker/{id}/set-kelulusan-ukom', [DashboardController::class, 'setKelulusanUkom'])->name('tracker.set-kelulusan-ukom');
    Route::post('/sync-now', [DashboardController::class, 'syncData'])->middleware('throttle:1,1')->name('sync.now'); // Maks 1x per menit
    Route::get('/sync-progress', [DashboardController::class, 'syncProgress'])->name('sync.progress');
    Route::get('/dashboard/diklat-detail/{nip}/{kategori}', [DashboardController::class, 'diklatDetail'])->name('dashboard.diklat-detail');
    Route::get('/dashboard/cetak-surat-kj/{id}', [DashboardController::class, 'cetakSuratKj'])->name('dashboard.cetak-surat-kj');
    
    // Log Aktivitas
    Route::get('/log-aktivitas', [LogAktivitasController::class, 'index'])->name('log-aktivitas');
    Route::get('/log-aktivitas/export-pdf', [LogAktivitasController::class, 'exportPdf'])->name('log-aktivitas.export-pdf');

    // Daftar Admin
    Route::get('/daftar-admin', [AdminController::class, 'index'])->name('daftar-admin');
    Route::post('/daftar-admin', [AdminController::class, 'store'])->name('daftar-admin.store'); // Tambah ini
    Route::put('/daftar-admin/{id}/update-role', [AdminController::class, 'updateRole'])->name('daftar-admin.update-role');
    Route::delete('/daftar-admin/{id}', [AdminController::class, 'destroy'])->middleware('throttle:10,1')->name('daftar-admin.destroy'); // Maks 10x per menit

    // Data Pegawai
    Route::get('/data-pegawai', [DataPegawaiController::class, 'index'])->name('data-pegawai');
    Route::get('/data-pegawai/{nip}', [DataPegawaiController::class, 'show'])->name('data-pegawai.show');
    Route::delete('/data-pegawai/{nip}', [DataPegawaiController::class, 'destroy'])->middleware('throttle:10,1')->name('data-pegawai.destroy'); // Maks 10x per menit
    Route::post('/data-pegawai/{nip}/send-manual', [DataPegawaiController::class, 'sendManualNotification'])->middleware('throttle:5,1')->name('data-pegawai.send-manual'); // Maks 5 email per menit

    // Konfigurasi Pesan
    Route::get('/konfigurasi-pesan', [KonfigurasiPesanController::class, 'index'])->name('konfigurasi-pesan');
    Route::post('/konfigurasi-pesan', [KonfigurasiPesanController::class, 'store'])->name('konfigurasi-pesan.store');
    Route::put('/konfigurasi-pesan/{id}', [KonfigurasiPesanController::class, 'update'])->name('konfigurasi-pesan.update');
    Route::delete('/konfigurasi-pesan/{id}', [KonfigurasiPesanController::class, 'destroy'])->name('konfigurasi-pesan.destroy');

    // Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-read');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Surat Pengajuan
    Route::get('/surat-pengajuan/preview/{kategori}', [SuratPengajuanController::class, 'preview'])->name('surat-pengajuan.preview');
    Route::post('/surat-pengajuan/generate', [SuratPengajuanController::class, 'generate'])->name('surat-pengajuan.generate');
    Route::post('/surat-pengajuan/konfirmasi', [SuratPengajuanController::class, 'konfirmasiUsulan'])->name('surat-pengajuan.konfirmasi');

});