<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class ValidateEmailVerification
{
    public function handle(Request $request, Closure $next)
    {
        // Check if request has valid signature
        if (!$request->hasValidSignature()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired verification link.'
            ], 403);
        }

        $userId = $request->route('id');
        $hash = $request->route('hash');

        $user = User::findOrFail($userId);

        // Validate hash matches user email
        if (sha1($user->email) !== $hash) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid verification data.'
            ], 403);
        }

        // Check if email is already verified
        if ($user->email_verified_at) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email already verified.'
            ], 200);
        }

        // Add user to request for use in controller
        $request->merge(['user_model' => $user]);

        return $next($request);
    }
}
