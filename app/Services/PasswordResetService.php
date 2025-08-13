<?php

namespace App\Services;

use App\Models\User;
use App\Models\PasswordReset;
use App\Models\UserToken;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetService
{
    public function sendResetLink(User $user): array
    {
        $email = strtolower($user->email);
        $token = Str::random(60);
        
        // Delete any existing reset tokens for this email
        PasswordReset::where('email', $email)->delete();
        
        // Create new reset token
        PasswordReset::create([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
        
        // Generate reset URL
        $resetUrl = route('password.reset', [
            'token' => $token, 
            'email' => $email
        ]);
        
        // Send email
        Mail::to($email)->send(new ResetPasswordMail($resetUrl, $user));
        
        return [
            'status' => 'success',
            'message' => 'Password reset link has been sent to your email.'
        ];
    }

    public function resetUserPassword(User $user, string $newPassword, $resetRecord): array
    {
        // Update user password
        $user->update(['password' => Hash::make($newPassword)]);
        
        // Delete the used reset token
        PasswordReset::where('email', $user->email)->delete();
        
        // Logout user from all devices
        UserToken::where('user_id', $user->id)->delete();
        
        return [
            'status' => 'success',
            'message' => 'Password has been reset successfully.'
        ];
    }
}
