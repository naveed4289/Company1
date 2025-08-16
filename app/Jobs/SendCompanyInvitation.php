<?php

namespace App\Jobs;

use App\Mail\CompanyInvitationMail;
use App\Models\CompanyInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCompanyInvitation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $invitation;

    public function __construct(CompanyInvitation $invitation)
    {
        $this->invitation = $invitation;
        $this->onQueue('company_invitations'); // optional queue name
    }

    public function handle()
    {
        Mail::to($this->invitation->email)
            ->send(new CompanyInvitationMail($this->invitation));
    }
}
