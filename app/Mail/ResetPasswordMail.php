<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetUrl;
    public $user;

    public function __construct($resetUrl, User $user)
    {
        $this->resetUrl = $resetUrl;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Reset Your Password')
                    ->view('emails.reset_password');
    }
}