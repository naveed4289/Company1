<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Models\PasswordReset;
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
        try {
            $email = strtolower($request->email);
            
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Account with this email does not exist.'
                ], 404);
            }
            
            $token = Str::random(60);
            
            PasswordReset::where('email', $email)->delete();
            
            PasswordReset::create([
                'email' => $email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
            
            $resetUrl = route('password.reset', [
                'token' => $token, 
                'email' => $email
            ]);
            
            Mail::to($email)->send(new ResetPasswordMail($resetUrl, $user));
            
            return response()->json([
                'status' => 'success',
                'message' => 'Password reset link has been sent to your email.'
            ]);
            
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $email = strtolower($request->email);
            $token = $request->token;
            
            $resetRecord = PasswordReset::where('email', $email)
                ->where('token', $token)
                ->first();
                
            if (!$resetRecord) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired reset token.'
                ], 400);
            }
            
            $tokenExpired = Carbon::parse($resetRecord->created_at)
                ->addMinutes(60)
                ->isPast();
                
            if ($tokenExpired) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Reset token has expired.'
                ], 400);
            }
            
            $user = User::where('email', $email)->first();
            $user->password = Hash::make($request->password);
            $user->save();
            
            PasswordReset::where('email', $email)->delete();
            
            UserToken::where('user_id', $user->id)->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Password has been reset successfully.'
            ]);
            
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}