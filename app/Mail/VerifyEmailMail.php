<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public $url;
    public $user;

    public function __construct($url, $user)
    {
        $this->url = $url;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Verify Your Email Address')
                    ->markdown('emails.verify');
    }
}
