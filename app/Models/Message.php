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
}
