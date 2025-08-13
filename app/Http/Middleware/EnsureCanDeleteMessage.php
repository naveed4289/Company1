<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;

class EnsureCanDeleteMessage
{
    public function handle(Request $request, Closure $next)
    {
        $messageId = $request->route('messageId');
        $message = Message::with('channel.company')->find($messageId);
        if (!$message) {
            return response()->json(['message' => 'Message not found'], 404);
        }

        $user = Auth::user();

        $canDelete = $message->user_id === $user->id
            || $message->channel->created_by === $user->id
            || $message->channel->company->user_id === $user->id;

        if (!$canDelete) {
            return response()->json([
                'message' => 'You do not have permission to delete this message'
            ], 403);
        }

        $request->attributes->set('message', $message);
        return $next($request);
    }
}


