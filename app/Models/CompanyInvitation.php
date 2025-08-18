<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CompanyInvitation extends Model
{
    protected $fillable = [
        'company_id',
        'email',
        'token',
        'accepted_at',
        'generated_password',
        'user_id'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ✅ Invitation create karne ka short helper
     */
    public static function createForCompany(Company $company, User $user, ?string $generatedPassword = null): self
    {
        return self::create([
            'company_id'         => $company->id,
            'email'              => $user->email,
            'token'              => Str::random(40),
            'generated_password' => $generatedPassword,
            'user_id'            => $user->id,
        ]);
    }

    // CompanyInvitation.php
    public static function generateLoginDetails(CompanyInvitation $invitation)
    {
        if (!$invitation->generated_password) {
            return null;
        }

        return [
            'email'    => $invitation->user->email,
            'password' => $invitation->generated_password,
            'note'     => 'Please change your password after logging in'
        ];
    }
}
