<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function employees()
{
    return $this->belongsToMany(User::class, 'company_user');
}

public function invitations()
{
    return $this->hasMany(CompanyInvitation::class);
}

public function channels()
{
    return $this->hasMany(Channel::class);
}
}
