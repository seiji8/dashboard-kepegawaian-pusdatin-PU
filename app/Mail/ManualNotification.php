<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ManualNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $pegawai;
    public $subjectLine;
    public $content;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pegawai, $subject, $content)
    {
        $this->pegawai = $pegawai;
        $this->subjectLine = $subject;
        $this->content = $content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subjectLine)
                    ->view('emails.manual_notification');
    }
}
