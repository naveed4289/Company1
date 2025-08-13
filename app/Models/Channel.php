<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'company_id',
        'created_by'
    ];

    protected $casts = [
        'type' => 'string',
    ];

    /**
     * Get the company that owns the channel
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the channel
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a user can manage this channel
     * Only the creator or company owner can manage
     */
    public function canBeManaged($user)
    {
        // Channel creator can manage
        if ($this->created_by === $user->id) {
            return true;
        }

        // Company owner can manage
        if ($this->company->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user belongs to the same company as the channel
     */
    public function isUserInSameCompany($user)
    {
        // Check if user is company owner
        if ($this->company->user_id === $user->id) {
            return true;
        }

        // Check if user is an employee
        return $this->company->employees()->where('user_id', $user->id)->exists();
    }

    /**
     * Scope for public channels
     */
    public function scopePublic($query)
    {
        return $query->where('type', 'public');
    }

    /**
     * Scope for private channels
     */
    public function scopePrivate($query)
    {
        return $query->where('type', 'private');
    }

    /**
     * Scope for channels by company
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Get all messages for this channel
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get members of this channel (for private channels)
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'channel_members');
    }

    /**
     * Check if a user is a member of this channel
     */
    public function hasMember($user)
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Add a member to this channel
     */
    public function addMember($user)
    {
        if (!$this->hasMember($user)) {
            $this->members()->attach($user->id);
        }
    }

    /**
     * Remove a member from this channel
     */
    public function removeMember($user)
    {
        $this->members()->detach($user->id);
    }

    /**
     * Check if user can access this channel
     */
    public function canUserAccess($user)
    {
        // Check if user belongs to the same company
        if (!$this->isUserInSameCompany($user)) {
            return false;
        }

        // Public channels - all company members can access
        if ($this->type === 'public') {
            return true;
        }

        // Private channels - only members can access
        if ($this->type === 'private') {
            return $this->hasMember($user);
        }

        return false;
    }
}
