<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-cleanup notifikasi lama (Umur > 30 Hari) tiap jam 12 malam
Schedule::call(function () {
    DB::table('notifications')
        ->where('created_at', '<', now()->subDays(30))
        ->delete();
})->daily();

// Penjadwalan Periodik (Triwulan & Tahunan)
Schedule::command('notify:periodic')->dailyAt('07:00');

// Sinkronisasi data e-HRM sebulan sekali setiap tanggal 1 jam 02:00
Schedule::command('ehrm:sync')->monthlyOn(1, '02:00');

// Recalculate tracker sebulan sekali setelah sync e-HRM
Schedule::command('tracker:run')->monthlyOn(1, '03:00');
