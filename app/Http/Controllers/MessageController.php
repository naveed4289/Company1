<?php

namespace App\Http\Controllers;

use App\Http\Resources\MessageResponse;
use App\Models\Message;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Fetch messages for a specific channel.
     * Uses the static method in the Message model to get paginated messages.
     */
    public function getMessages(Request $request, $channelId)
    {
        // Get the resolved channel from request attributes (middleware)
        $channel = request()->attributes->get('channel');

        // Number of messages per page, default 50
        $perPage = $request->get('per_page', 50);

        // Fetch messages using static method in Message model
        $messages = Message::fetchForChannel($channel, $perPage);

        // Return formatted response using resource
        return MessageResponse::messagesFetched($messages);
    }

    /**
     * Send a new message to a channel, optionally with attachments.
     * Uses the Message model static method to handle creation and attachments.
     */
    public function sendMessage(\App\Http\Requests\SendMessageRequest $request)
    {
        // Authenticated user
        $user = Auth::user();

        // Get the resolved channel from request attributes (middleware)
        $channel = request()->attributes->get('channel');

        // Create the message and handle attachments using static method
        $message = Message::sendToChannel($user, $channel, $request->content, $request->file('attachments'));

        // Return formatted response using resource
        return MessageResponse::messageSent($message);
    }

    /**
     * Delete a message from a channel.
     * Only the sender or authorized users can delete.
     */
    public function deleteMessage($messageId)
    {
        // Get the message from request attributes (middleware)
        $message = request()->attributes->get('message');

        // Delete the message
        $message->delete();

        // Return formatted response
        return MessageResponse::messageDeleted();
    }
}
