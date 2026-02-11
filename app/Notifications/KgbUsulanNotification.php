<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\NotifikasiRules;

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

    /**
     * Ambil template pesan dari tabel notifikasi_rules (kategori: KGB Upload Dokumen)
     * dan ganti placeholder {nama}, {nip}, {deadline}
     */
    private function buildMessage()
    {
        $rule = NotifikasiRules::where('kategori', 'KGB Upload Dokumen')
                               ->where('is_active', true)
                               ->first();

        $pegawai  = $this->tracker->pegawai;
        $deadline = $this->tracker->tanggal_target;

        if ($rule) {
            return str_replace(
                ['{nama}', '{nip}', '{deadline}'],
                [$pegawai->nama, $pegawai->nip, \Carbon\Carbon::parse($deadline)->format('d-m-Y')],
                $rule->template_pesan
            );
        }

        // Fallback jika rule belum ada di database
        return "Selamat! Waktunya proses KGB. Segera upload SK Terakhir & SKP Anda sekarang.";
    }

    public function toMail($notifiable)
    {
        // $message = $this->buildMessage(); // Kita ganti logicnya untuk ikut desain baru
        
        $tmt = \Carbon\Carbon::parse($this->tracker->tanggal_target);
        $bulanTahun = $tmt->isoFormat('MMMM Y'); // Contoh: Februari 2026

        return (new MailMessage)
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('🔔 Notifikasi KGB: SK KGB Sudah Terbit')
                    ->view('emails.kgb_notification', [
                        'tracker' => $this->tracker,
                        'bulanTahun' => $bulanTahun,
                        // 'pesanTemplate' => $message, // Tidak dipakai dulu karena custom layout
                    ]);
    }

    public function toDatabase($notifiable)
    {
        $message = $this->buildMessage();

        // Ambil baris pertama sebagai title, sisanya sebagai message body
        $lines = explode("\n", $message);
        $title = trim($lines[0]);
        $body  = trim(implode("\n", array_slice($lines, 1)));

        return [
            'title'   => '📄 ' . $title,
            'message' => $body,
            'type'    => 'info',
        ];
    }
}