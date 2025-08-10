<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyInvitation extends Model
{
    protected $fillable = ['company_id', 'email', 'token', 'accepted_at', 'generated_password', 'user_id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}