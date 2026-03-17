<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $messageText;
    public $url;

    public function __construct($title, $messageText, $url = null)
    {
        $this->title = $title;
        $this->messageText = $messageText;
        $this->url = $url;
    }

    public function build()
    {
        return $this->subject($this->title)
            ->view('emails.project_notification')
            ->with([
                'title' => $this->title,
                'messageText' => $this->messageText,
                'url' => $this->url
            ]);
    }
}
