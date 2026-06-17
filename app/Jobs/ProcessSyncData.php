<?php

namespace App\Jobs;

use App\Helpers\ActivityLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ProcessSyncData implements ShouldQueue
{
    use Queueable;

    /**
     * Set the time limit for the job.
     */
    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // 1. Sync E-HRM (API)
            Artisan::call('ehrm:sync');

            // UPDATE CACHE (96%) - Memproses Seeder
            $currentCache = Cache::get('sync_status', []);
            $currentCache['progress'] = 96;
            $currentCache['step_4_status'] = 'done';
            $currentCache['detail_text'] = 'Menjalankan pembaruan data struktural...';
            Cache::put('sync_status', $currentCache, now()->addMinutes(15));

            // 2. Seeder Manual (UpdateTmtManualSeeder)
            Artisan::call('db:seed', [
                '--class' => 'UpdateTmtManualSeeder',
            ]);

            // UPDATE CACHE (98%) - Recalculate Tracker
            $currentCache = Cache::get('sync_status', []);
            $currentCache['progress'] = 98;
            $currentCache['detail_text'] = 'Mengkalkulasi ulang status Tracker Dashboard...';
            Cache::put('sync_status', $currentCache, now()->addMinutes(15));

            // 3. Recalculate Tracker (Force Notification)
            Artisan::call('tracker:run', [
                '--force' => true,
            ]);

            // UPDATE CACHE TO 100% DONE
            $currentCache = Cache::get('sync_status', []);
            $currentCache['progress'] = 100;
            $currentCache['step_4_status'] = 'done';
            $currentCache['detail_text'] = 'Menyelesaikan sinkronisasi... Selesai!';
            Cache::put('sync_status', $currentCache, now()->addMinutes(15));

            ActivityLogger::logSystem('Background Sync Selesai (E-HRM -> Seeder -> Tracker)');

        } catch (\Throwable $e) {
            $currentCache = Cache::get('sync_status', []);
            $currentCache['detail_text'] = 'Terjadi kesalahan sistem saat sinkronisasi (Gagal).';
            Cache::put('sync_status', $currentCache, now()->addMinutes(15));
            ActivityLogger::logSystem('Background Sync GAGAL: '.$e->getMessage());
        }
    }

    /**
     * Tangani skenario di mana job di-terminate paksa (contoh: timeout atau kehabisan memori).
     * Laravel queue worker memanggil metode ini sebelum mendepak job.
     */
    public function failed(\Throwable $exception): void
    {
        $currentCache = Cache::get('sync_status', []);
        $currentCache['detail_text'] = 'Terjadi kesalahan sistem (waktu habis/timeout)';
        // Reset progress stat ke error
        $currentCache['step_4_status'] = 'error';
        Cache::put('sync_status', $currentCache, now()->addMinutes(15));

        ActivityLogger::logSystem('Background Sync Kritis/TIMEOUT: '.$exception->getMessage());
    }
}
