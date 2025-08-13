<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Allow auto-created users to proceed even if email_verified_at is somehow null
        if (is_null($user->email_verified_at) && !$user->is_auto_created) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please verify your email to activate your account.'
            ], 403);
        }

        // If auto-created user doesn't have email_verified_at set, set it now
        if ($user->is_auto_created && is_null($user->email_verified_at)) {
            $user->update(['email_verified_at' => now()]);
        }

        return $next($request);
    }
}
