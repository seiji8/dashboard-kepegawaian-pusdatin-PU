<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogAktivitasController;
use App\Http\Controllers\AdminController;

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
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// 3. Rute Halaman Admin (Harus Login Dulu)
Route::middleware(['auth'])->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Log Aktivitas
    Route::get('/log-aktivitas', [LogAktivitasController::class, 'index'])->name('log-aktivitas');
    Route::delete('/log-aktivitas/{id}', [LogAktivitasController::class, 'destroy'])->name('log-aktivitas.destroy');

    // Daftar Admin
    Route::get('/daftar-admin', [AdminController::class, 'index'])->name('daftar-admin');
    Route::put('/daftar-admin/{id}/update-role', [AdminController::class, 'updateRole'])->name('daftar-admin.update-role');
    Route::delete('/daftar-admin/{id}', [AdminController::class, 'destroy'])->name('daftar-admin.destroy');

    // Data Pegawai
    Route::get('/data-pegawai', [\App\Http\Controllers\DataPegawaiController::class, 'index'])->name('data-pegawai');
    Route::get('/data-pegawai/{nip}', [\App\Http\Controllers\DataPegawaiController::class, 'show'])->name('data-pegawai.show');
    Route::delete('/data-pegawai/{nip}', [\App\Http\Controllers\DataPegawaiController::class, 'destroy'])->name('data-pegawai.destroy');

    // Konfigurasi Pesan
    Route::get('/konfigurasi-pesan', [\App\Http\Controllers\KonfigurasiPesanController::class, 'index'])->name('konfigurasi-pesan');
    Route::post('/konfigurasi-pesan', [\App\Http\Controllers\KonfigurasiPesanController::class, 'store'])->name('konfigurasi-pesan.store');
    Route::put('/konfigurasi-pesan/{id}', [\App\Http\Controllers\KonfigurasiPesanController::class, 'update'])->name('konfigurasi-pesan.update');
    Route::delete('/konfigurasi-pesan/{id}', [\App\Http\Controllers\KonfigurasiPesanController::class, 'destroy'])->name('konfigurasi-pesan.destroy');

});