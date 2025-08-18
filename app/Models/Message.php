<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'user_id',
        'content'
    ];

    /**
     * Get the channel that owns the message
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Get the user who sent the message
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }

    /**
     * Scope for messages in a specific channel
     */
    public function scopeInChannel($query, $channelId)
    {
        return $query->where('channel_id', $channelId);
    }

    /**
     * Scope for recent messages
     */
    public function scopeRecent($query, $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
    /**
     * Static method: Fetch messages for a channel with pagination
     */
    public static function fetchForChannel($channel, $perPage = 50)
    {
        return $channel->messages()
            ->with(['user:id,first_name,last_name,email', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Static method: Send a message with optional attachments
     */
    public static function sendToChannel($user, $channel, $content, $attachments = null)
    {
        $message = self::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'content' => $content
        ]);

        if ($attachments) {
            foreach ($attachments as $file) {
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

        return $message->load(['user:id,first_name,last_name,email', 'attachments']);
    }
}
