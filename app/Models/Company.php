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

    // Employees fetch karne ka static method
    public static function fetchEmployees(Company $company)
    {
        return $company->employees()
            ->select('users.id', 'first_name', 'last_name', 'email', 'is_auto_created', 'users.created_at')
            ->get();
    }

    // Pending invitations fetch karne ka static method
    public static function fetchPendingInvitations(Company $company)
    {
        return $company->invitations()
            ->whereNull('accepted_at')
            ->select('id', 'email', 'created_at')
            ->get();
    }
}
