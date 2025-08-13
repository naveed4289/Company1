<?php

namespace App\Services;

use App\Models\User;
use App\Mail\VerifyEmailMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailVerificationService
{
    public function sendVerificationEmail(User $user): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        Mail::to($user->email)->send(new VerifyEmailMail($verificationUrl, $user));
    }

    public function verifyUserEmail(User $user): array
    {
        $user->update(['email_verified_at' => now()]);

        return [
            'status' => 'success',
            'message' => 'Email verified successfully. You can now login.'
        ];
    }
}
