<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\VerifyEmailRequest;
use App\Mail\VerifyEmailMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    public function sendVerificationEmail(User $user)
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        Mail::to($user->email)->send(new VerifyEmailMail($verificationUrl, $user));
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        $user = $request->user_model; // Coming from middleware
        
        $user->update(['email_verified_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Email verified successfully. You can now login.'
        ]);
    }
}