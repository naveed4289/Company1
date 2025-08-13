<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HandleLoginEmailVerification
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Check email verification for non-auto-created users
        if (is_null($user->email_verified_at) && !$user->is_auto_created) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please verify your email to activate your account.'
            ], 403);
        }

        // Auto-verify auto-created users
        if ($user->is_auto_created && is_null($user->email_verified_at)) {
            $user->update(['email_verified_at' => now()]);
        }

        return $next($request);
    }
}
