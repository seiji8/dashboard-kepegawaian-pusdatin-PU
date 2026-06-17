<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemAlertNotification extends Notification
{
    use Queueable;

    public $pegawai;

    public $subjectLine;

    public $content;

    public $pdfUrl;

    public $pdfData;

    public $dbContent;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pegawai, $subject, $content, $pdfUrl = null, $pdfData = null, $dbContent = null)
    {
        $this->pegawai = $pegawai;
        $this->subjectLine = $subject;
        $this->content = $content;
        $this->pdfUrl = $pdfUrl;
        $this->pdfData = $pdfData;
        $this->dbContent = $dbContent ?? $content; // Gunakan dbContent jika ada, jika tidak fallback ke content email
    }

    /**
     * Tentukan channel pengiriman (Email & DB Lonceng)
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Kirim Email Menggunakan Template manual_notification (Desain Baru Biru)
     */
    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject($this->subjectLine)
            ->view('emails.manual_notification', [
                'subjectLine' => $this->subjectLine,
                'pegawai' => $this->pegawai,
                'content' => $this->content,
                'pdfUrl' => $this->pdfUrl,
                'pdfData' => $this->pdfData,
            ]);

        if ($this->pdfData) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('emails.rekap_usulan_pdf', ['data' => $this->pdfData]);
            $mail->attachData($pdf->output(), 'Rekap_Usulan_Kepegawaian.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => '📄 '.$this->subjectLine,
            'message' => $this->dbContent,
            'type' => 'info',
        ];
    }
}
