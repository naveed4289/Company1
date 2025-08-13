<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasCompany
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if (!$user->company) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have a company'
            ], 403);
        }

        return $next($request);
    }
}
