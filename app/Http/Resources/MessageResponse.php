<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResponse extends JsonResource
{
    protected $messageText;

    public function __construct($resource = null, $messageText = null)
    {
        parent::__construct($resource);
        $this->messageText = $messageText;
    }

    public function toArray($request)
    {
        return [
            'status' => 'success',
            'message' => $this->messageText,
            'data' => $this->resource
        ];
    }

    // ✅ Responses
    public static function messagesFetched($messages)
    {
        return new self($messages, 'Messages fetched successfully.');
    }

    public static function messageSent($message)
    {
        return new self($message, 'Message sent successfully.');
    }

    public static function messageDeleted()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Message deleted successfully.'
        ]);
    }
}
