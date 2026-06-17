<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ManualNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $pegawai;

    public $subjectLine;

    public $content;

    public $pdfUrl;

    public $pdfData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pegawai, $subject, $content, $pdfUrl = null, $pdfData = null)
    {
        $this->pegawai = $pegawai;
        $this->subjectLine = $subject;
        $this->content = $content;
        $this->pdfUrl = $pdfUrl;
        $this->pdfData = $pdfData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject($this->subjectLine)
            ->view('emails.manual_notification')
            ->with([
                'subjectLine' => $this->subjectLine,
                'pegawai' => $this->pegawai,
                'content' => $this->content,
                'pdfUrl' => $this->pdfUrl,
            ]);

        if ($this->pdfData) {
            $pdf = Pdf::loadView('emails.rekap_usulan_pdf', ['data' => $this->pdfData]);
            $mail->attachData($pdf->output(), 'Rekap_Usulan_Kepegawaian.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
