<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;

class ValidatePasswordResetToken
{
    public function handle(Request $request, Closure $next)
    {
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
        
        // Check if token has expired (60 minutes)
        $tokenExpired = Carbon::parse($resetRecord->created_at)
            ->addMinutes(60)
            ->isPast();
            
        if ($tokenExpired) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reset token has expired.'
            ], 400);
        }

        // Get user for password reset
        $user = User::where('email', $email)->first();
        
        // Add reset record and user to request for use in controller
        $request->merge([
            'reset_record' => $resetRecord,
            'user_model' => $user
        ]);

        return $next($request);
    }
}
