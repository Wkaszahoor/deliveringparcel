<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Generic mailable for admin-templated emails. Subject/body are already
 * placeholder-rendered by EmailService before they get here. Body is
 * admin-authored HTML rendered inside a simple branded shell.
 */
class SystemEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $renderedSubject;
    public string $renderedBody;

    public function __construct(string $subject, string $body)
    {
        $this->renderedSubject = $subject;
        $this->renderedBody = $body;
    }

    public function build()
    {
        return $this
            ->subject($this->renderedSubject)
            ->view('emails.system', ['body' => $this->renderedBody]);
    }
}
