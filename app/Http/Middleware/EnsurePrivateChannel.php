<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePrivateChannel
{
	public function handle(Request $request, Closure $next)
	{
		$channel = $request->attributes->get('channel');
		if ($channel->type !== 'private') {
			return response()->json(['message' => 'Can only modify members for private channels'], 400);
		}
		return $next($request);
	}
}


