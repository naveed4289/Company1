<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
    'last_name',
    'email',
    'password',
    'is_auto_created',
    'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function company()
{
    return $this->hasOne(Company::class);
}
public function companies()
{
    return $this->belongsToMany(Company::class, 'company_user');
}

public function createdChannels()
{
    return $this->hasMany(Channel::class, 'created_by');
}

public function messages()
{
    return $this->hasMany(Message::class);
}

public function channelMemberships()
{
    return $this->belongsToMany(Channel::class, 'channel_members');
}
}
