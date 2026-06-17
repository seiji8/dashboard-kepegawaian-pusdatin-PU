<?php

namespace App\Helpers;

use App\Models\Logs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Log aktivitas ke database
     *
     * @param  string  $tipe  - Jenis log (API_SYNC, NOTIF_SENT, ADMIN_ACTION, SYSTEM_LOG)
     * @param  string  $deskripsi  - Deskripsi detail aktivitas
     * @param  string|null  $targetNip  - NIP pegawai target (opsional)
     * @return void
     */
    public static function log($tipe, $deskripsi, $targetNip = null)
    {
        Logs::create([
            'tipe' => $tipe,
            'deskripsi' => $deskripsi,
            'target_nip' => $targetNip,
            'user_id' => Auth::id(), // Otomatis ambil user yang sedang login
            'waktu' => Carbon::now(),
        ]);
    }

    /**
     * Log aksi admin
     */
    public static function logAdminAction($deskripsi, $targetNip = null)
    {
        self::log('ADMIN_ACTION', $deskripsi, $targetNip);
    }

    /**
     * Log notifikasi yang dikirim
     */
    public static function logNotification($deskripsi, $targetNip)
    {
        self::log('NOTIF_SENT', $deskripsi, $targetNip);
    }

    /**
     * Log sinkronisasi API
     */
    public static function logApiSync($deskripsi)
    {
        self::log('API_SYNC', $deskripsi);
    }

    /**
     * Log sistem (tanpa user_id)
     */
    public static function logSystem($deskripsi, $targetNip = null)
    {
        Logs::create([
            'tipe' => 'SYSTEM_LOG',
            'deskripsi' => $deskripsi,
            'target_nip' => $targetNip,
            'user_id' => null, // Sistem tidak punya user_id
            'waktu' => Carbon::now(),
        ]);
    }
}
