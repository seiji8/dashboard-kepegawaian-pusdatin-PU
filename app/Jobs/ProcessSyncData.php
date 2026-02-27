<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use App\Helpers\ActivityLogger;

class ProcessSyncData implements ShouldQueue
{
    use Queueable;

    /**
     * Set the time limit for the job.
     */
    public $timeout = 600;

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
            
            // 2. Seeder Manual (UpdateTmtManualSeeder)
            Artisan::call('db:seed', [
                '--class' => 'UpdateTmtManualSeeder'
            ]);

            // 3. Recalculate Tracker (Force Notification)
            Artisan::call('tracker:run', [
                '--force' => true
            ]);

            // UPDATE CACHE TO 100% DONE
            $currentCache = Cache::get('sync_status', []);
            $currentCache['progress'] = 100;
            $currentCache['step_4_status'] = 'done';
            $currentCache['detail_text'] = 'Menyelesaikan sinkronisasi... Selesai!';
            Cache::put('sync_status', $currentCache, now()->addMinutes(15));

            ActivityLogger::logSystem("Background Sync Selesai (E-HRM -> Seeder -> Tracker)");

        } catch (\Exception $e) {
            $currentCache = Cache::get('sync_status', []);
            $currentCache['detail_text'] = 'Terjadi kesalahan sistem saat sinkronisasi.';
            Cache::put('sync_status', $currentCache, now()->addMinutes(15));
            ActivityLogger::logSystem("Background Sync GAGAL: " . $e->getMessage());
        }
    }
}
