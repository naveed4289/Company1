<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Channel;

class LoadChannel
{
    public function handle(Request $request, Closure $next)
    {
        $param = $request->input('channel_id') ?? $request->route('channelId') ?? $request->route('id');
        $channel = Channel::find($param);

		if (!$channel) {
			return response()->json(['message' => 'Channel not found'], 404);
		}

		$request->attributes->set('channel', $channel);
		return $next($request);
	}
}


