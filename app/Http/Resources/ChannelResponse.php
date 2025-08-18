<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResponse extends JsonResource
{
    protected $message;

    public function __construct($resource = null, $message = null)
    {
        parent::__construct($resource);
        $this->message = $message;
    }

    public function toArray($request)
    {
        return [
            'status' => 'success',
            'message' => $this->message,
            'data' => $this->resource,
        ];
    }

    // ✅ Static methods for common channel responses

    public static function channelsFetched($channels)
    {
        return new self($channels, 'Channels fetched successfully');
    }

    public static function channelCreated($channel)
    {
        return new self($channel, 'Channel created successfully');
    }

    public static function channelUpdated($channel)
    {
        return new self($channel, 'Channel updated successfully');
    }

    public static function channelDeleted($channelName)
    {
        return response()->json([
            'status' => 'success',
            'message' => "Channel '{$channelName}' deleted successfully"
        ]);
    }

    public static function memberAdded($user)
    {
        return response()->json([
            'status' => 'success',
            'message' => "Member '{$user->first_name} {$user->last_name}' added successfully"
        ]);
    }

    public static function memberRemoved($user)
    {
        return response()->json([
            'status' => 'success',
            'message' => "Member '{$user->first_name} {$user->last_name}' removed successfully"
        ]);
    }
}
