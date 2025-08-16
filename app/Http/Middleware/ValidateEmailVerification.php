<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class ValidateEmailVerification
{
    public function handle(Request $request, Closure $next)
    {
        // No need to check signature here - signed middleware already did that
        $user = User::findOrFail($request->route('id'));

        // Validate hash matches user email
        if (!hash_equals(sha1($user->email), $request->route('hash'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid verification data.'
            ], 403);
        }

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
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