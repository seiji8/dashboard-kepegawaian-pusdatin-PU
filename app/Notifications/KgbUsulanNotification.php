<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KgbUsulanNotification extends Notification
{
    use Queueable;

    protected $tracker;

    public function __construct($tracker)
    {
        $this->tracker = $tracker;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
                    ->from(config('mail.from.address'), config('mail.from.name')) 
                    ->subject('🔔 Peringatan KGB: Segera Upload Dokumen')
                    ->view('emails.kgb_notification', ['tracker' => $this->tracker]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'title'   => '📄 Waktunya Upload Dokumen',
            'message' => "Selamat! Waktunya proses KGB. Segera upload SK Terakhir & SKP Anda sekarang.",
            // Nanti arahkan ke halaman upload pegawai, sementara dashboard dulu
            'url'     => route('dashboard'), 
            'icon'    => 'bi-file-earmark-arrow-up-fill text-primary', // Icon biru
            'type'    => 'info'
        ];
    }
}