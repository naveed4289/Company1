<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Jobs\SendPasswordResetEmail;
use App\Models\PasswordReset;
use App\Models\UserToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Resources\AuthResponse;

class PasswordResetController extends Controller
{
    /**
     * Handle Forgot Password request
     * - Generate token
     * - Save token in DB
     * - Dispatch reset email
     * - Return success response
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        // ✅ User already validated in request
        $user = $request->user_model;

        // ✅ Generate secure random token
        $token = Str::random(60);

        // ✅ Save / Update token in password_resets table
        PasswordReset::generateToken($user->email, $token);

        // ✅ Dispatch job for sending password reset email
        SendPasswordResetEmail::dispatch(
            $user,
            route('password.reset', [
                'token' => $token,
                'email' => $user->email
            ])
        );

        // ✅ Return standard success response
        return AuthResponse::passwordResetLinkSent();
    }

    /**
     * Handle Reset Password request
     * - Update user password
     * - Remove used password reset token
     * - Logout from all devices (delete user tokens)
     * - Return success response
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        // ✅ User already validated in request
        $user = $request->user_model;

        // ✅ Update user password (hashed)
        $user->update([
            'password' => Hash::make($request->password)
        ]);
        
        // ✅ Delete password reset record for this email
        PasswordReset::where('email', $user->email)->delete();

        // ✅ Delete all user tokens (logout from all devices)
        UserToken::where('user_id', $user->id)->delete();

        // ✅ Return standard success response
        return AuthResponse::passwordResetSuccess();
    }
}
