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
}
