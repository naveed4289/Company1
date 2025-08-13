<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    /**
     * Get messages for a specific channel
     */
    public function getMessages(Request $request, $channelId)
    {
        try {
            $user = auth()->user();
            $channel = Channel::findOrFail($channelId);

            // Check if user can access this channel
            if (!$channel->canUserAccess($user)) {
                return response()->json([
                    'message' => 'You do not have access to this channel'
                ], 403);
            }

            $perPage = $request->get('per_page', 50);
            $messages = $channel->messages()
                ->with(['user:id,first_name,last_name,email'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'data' => $messages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a message to a channel
     */
    public function sendMessage(Request $request, $channelId)
    {
        try {
            $request->validate([
                'content' => 'required|string|max:1000'
            ]);

            $user = auth()->user();
            $channel = Channel::findOrFail($channelId);

            // Check if user can access this channel
            if (!$channel->canUserAccess($user)) {
                return response()->json([
                    'message' => 'You do not have access to this channel'
                ], 403);
            }

            $message = Message::create([
                'channel_id' => $channelId,
                'user_id' => $user->id,
                'content' => $request->content
            ]);

            $message->load(['user:id,first_name,last_name,email']);

            return response()->json([
                'message' => 'Message sent successfully',
                'data' => $message
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error sending message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a message (only message sender or channel creator/company owner can delete)
     */
    public function deleteMessage($messageId)
    {
        try {
            $user = auth()->user();
            $message = Message::with('channel')->findOrFail($messageId);

            // Check if user can delete this message
            $canDelete = false;
            
            // Message sender can delete
            if ($message->user_id === $user->id) {
                $canDelete = true;
            }
            
            // Channel creator can delete
            if ($message->channel->created_by === $user->id) {
                $canDelete = true;
            }
            
            // Company owner can delete
            if ($message->channel->company->user_id === $user->id) {
                $canDelete = true;
            }

            if (!$canDelete) {
                return response()->json([
                    'message' => 'You do not have permission to delete this message'
                ], 403);
            }

            $message->delete();

            return response()->json([
                'message' => 'Message deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting message',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
