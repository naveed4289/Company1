<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\VerifyEmailMail;

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

    public function verifyEmail(Request $request, $id, $hash)  // Changed from verify() to verifyEmail()
    {
        if (! $request->hasValidSignature()) {
            return response()->json(['message' => 'Invalid or expired verification link.'], 403);
        }

        $user = User::findOrFail($id);

        if (sha1($user->email) !== $hash) {
            return response()->json(['message' => 'Invalid verification data.'], 403);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        $user->email_verified_at = now();
        $user->save();

        return response()->json(['message' => 'Email verified successfully. You can now login.']);
    }
}