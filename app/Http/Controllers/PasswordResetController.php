<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\PasswordReset;
use App\Models\User;
use App\Models\UserToken;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = $request->user_model;
        $token = Str::random(60);
        
        // Delete old tokens and create new one
        PasswordReset::where('email', $user->email)->delete();
        PasswordReset::create([
            'email' => strtolower($user->email),
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
        
        // Send reset email
        Mail::to($user->email)->send(
            new ResetPasswordMail(
                route('password.reset', ['token' => $token, 'email' => $user->email]),
                $user
            )
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset link has been sent to your email.'
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = $request->user_model;
        
        // Update password and clean up
        $user->update(['password' => Hash::make($request->password)]);
        PasswordReset::where('email', $user->email)->delete();
        UserToken::where('user_id', $user->id)->delete();

        return response()->json([
            'status' => 'success', 
            'message' => 'Password has been reset successfully.'
        ]);
    }
}