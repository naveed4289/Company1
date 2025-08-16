<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyEmailRequest;
use App\Jobs\SendVerificationEmail;
use App\Models\User;
use Illuminate\Http\Request;
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

        // Dispatch job with the pre-generated URL
        SendVerificationEmail::dispatch($user, $verificationUrl)
            ->onQueue('verification_emails');
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        // $user = User::findOrFail($request->id);

        $request->user_model->update(['email_verified_at' => now()]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Email verified successfully'
        ]);
    }
}
