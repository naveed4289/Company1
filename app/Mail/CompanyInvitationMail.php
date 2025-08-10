<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\CompanyInvitation;

class CompanyInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;

    public function __construct(CompanyInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    public function build()
    {
        $acceptUrl = url('/api/company-invitation/accept/' . $this->invitation->token);

        return $this->subject('Company Invitation - ' . $this->invitation->company->name)
                    ->markdown('emails.company.invitation')
                    ->with([
                        'acceptUrl' => $acceptUrl,
                        'companyName' => $this->invitation->company->name,
                        'hasGeneratedPassword' => !empty($this->invitation->generated_password),
                        'email' => $this->invitation->email,
                        'password' => $this->invitation->generated_password,
                    ]);
    }
}
