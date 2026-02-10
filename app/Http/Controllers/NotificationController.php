<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Ambil notifikasi user yang login (10 terbaru).
     */
    public function index()
    {
        $user = Auth::user();

        $notifications = $user->notifications()->latest()->take(10)->get()->map(function ($notif) {
            return [
                'id'      => $notif->id,
                'title'   => $notif->data['title'] ?? 'Notifikasi',
                'message' => $notif->data['message'] ?? '',
                'icon'    => $notif->data['icon'] ?? 'bi-bell-fill',
                'type'    => $notif->data['type'] ?? 'info',
                'url'     => $notif->data['url'] ?? '#',
                'read'    => !is_null($notif->read_at),
                'time'    => $notif->created_at->diffForHumans(),
            ];
        });

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi telah ditandai dibaca.',
        ]);
    }
}
