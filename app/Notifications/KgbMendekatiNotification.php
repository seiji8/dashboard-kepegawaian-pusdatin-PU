<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage; // PENTING

class KgbMendekatiNotification extends Notification
{
    use Queueable;

    protected $pegawai;

    public function __construct($pegawai)
    {
        $this->pegawai = $pegawai;
    }

    // Tentukan lewat jalur apa notifikasi dikirim (Database saja cukup)
    public function via($notifiable)
    {
        return ['database'];
    }

    // Format data yang disimpan ke database
    public function toDatabase($notifiable)
    {
        return [
            'title'   => '⚠️ Peringatan KGB',
            'message' => "Pegawai a.n {$this->pegawai->nama} akan KGB pada tanggal " . $this->pegawai->tmt_kgb_terakhir->addYears(2)->format('d-m-Y'),
            'url'     => route('dashboard'), // Klik lari ke dashboard
            'icon'    => 'bi-exclamation-triangle-fill text-warning', // Icon kuning
            'type'    => 'warning'
        ];
    }
}