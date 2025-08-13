<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Get messages for a specific channel
     */
    public function getMessages(Request $request, $channelId)
    {
        $channel = request()->attributes->get('channel');

        $perPage = $request->get('per_page', 50);
        $messages = $channel->messages()
            ->with(['user:id,first_name,last_name,email','attachments'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(['data' => $messages]);
    }

    /**
     * Send a message to a channel
     */
    public function sendMessage(\App\Http\Requests\SendMessageRequest $request)
    {
        $user = Auth::user();
        $channel = request()->attributes->get('channel');

        $message = Message::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'content' => $request->content
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $stored = $file->store('attachments', 'public');
                $message->attachments()->create([
                    'path' => $stored,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'original_name' => $file->getClientOriginalName(),
                    'type' => str_starts_with($file->getMimeType(), 'image/') ? 'image' : (str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'file'),
                ]);
            }
        }

        $message->load(['user:id,first_name,last_name,email','attachments']);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message
        ], 201);
    }

    /**
     * Delete a message (only message sender or channel creator/company owner can delete)
     */
    public function deleteMessage($messageId)
    {
        $message = request()->attributes->get('message');
        $message->delete();
        return response()->json(['message' => 'Message deleted successfully']);
    }
}
