<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCanAccessChannel
{
    public function handle(Request $request, Closure $next)
    {
        $channel = $request->attributes->get('channel');
        $user = Auth::user();

        if (!$channel->canUserAccess($user)) {
            return response()->json([
                'message' => 'You do not have access to this channel'
            ], 403);
        }

        return $next($request);
    }
}


