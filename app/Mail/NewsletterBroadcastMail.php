<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterBroadcastMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $content;

    public function __construct(public string $subjectLine, string $content)
    {
        $this->subject($subjectLine);
        $this->content = $content;
    }

    public function build()
    {
        return $this->view('emails.newsletter.broadcast')
            ->subject($this->subjectLine)
            ->with([
                'content' => $this->content,
            ]);
    }
}

