<?php

namespace App\Notifications;

use App\Models\NotifikasiRules;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KgbMendekatiNotification extends Notification
{
    use Queueable;

    protected $pegawai;

    public function __construct($pegawai)
    {
        $this->pegawai = $pegawai;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Ambil template pesan dari tabel notifikasi_rules (kategori: KGB Penjadwalan)
     * dan ganti placeholder {nama}, {nip}, {deadline}
     */
    public function toDatabase($notifiable)
    {
        $rule = NotifikasiRules::where('kategori', 'KGB Penjadwalan')
            ->where('is_active', true)
            ->first();

        $deadline = $this->pegawai->tmt_kgb_terakhir->addYears(2)->format('d-m-Y');

        if ($rule) {
            $message = str_replace(
                ['{nama}', '{nip}', '{deadline}'],
                [$this->pegawai->nama, $this->pegawai->nip, $deadline],
                $rule->template_pesan
            );
        } else {
            // Fallback jika rule belum ada di database
            $message = "Pegawai a.n {$this->pegawai->nama} akan KGB pada tanggal {$deadline}";
        }

        // Ambil baris pertama sebagai title, sisanya sebagai message
        $lines = explode("\n", $message);
        $title = trim($lines[0]);
        $body = trim(implode("\n", array_slice($lines, 1)));

        return [
            'title' => $title,
            'message' => $body,
            'type' => 'warning',
        ];
    }
}
