<?php

namespace App\Http\Middleware;

use App\Models\Channel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelOwnerMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * This middleware ensures that only the channel creator or company owner
     * can perform management operations on a channel
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Get channel ID from route parameter
        $channelId = $request->route('id');
        
        if (!$channelId) {
            return response()->json(['message' => 'Channel ID is required'], 400);
        }

        $channel = Channel::with('company')->find($channelId);
        
        if (!$channel) {
            return response()->json(['message' => 'Channel not found'], 404);
        }

        // Check if user can manage this channel
        if (!$channel->canBeManaged($user)) {
            return response()->json([
                'message' => 'Forbidden: You can only manage channels you created or channels in your company'
            ], 403);
        }

        // Check if user belongs to the same company
        if (!$channel->isUserInSameCompany($user)) {
            return response()->json([
                'message' => 'Forbidden: You can only access channels from your company'
            ], 403);
        }

        // Add channel to request for controller use
        $request->attributes->add(['channel' => $channel]);

        return $next($request);
    }
}
