<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
    public static function createUser(array $data)
    {
        return self::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => strtolower($data['email']),
            'password'   => \Illuminate\Support\Facades\Hash::make($data['password']),
        ]);
    }

    public static function createDefaultCompany(User $user)
    {
        $companyName = $user->first_name . ' ' . $user->last_name . ' Company';
        return $user->company()->create(['name' => $companyName]);
    }

    /**
     * Agar user exist nahi hai to auto create karega
     * return ['user' => User, 'password' => string, 'type' => 'new'|'existing']
     */
    public static function findOrCreateByEmail(string $email): array
    {
        $existingUser = self::where('email', $email)->first();

        if ($existingUser) {
            return [
                'user'     => $existingUser,
                'password' => null,
                'type'     => 'existing'
            ];
        }

        $generatedPassword = Str::random(12);

        $newUser = self::create([
            'first_name'        => 'New',
            'last_name'         => 'Employee',
            'email'             => $email,
            'password'          => Hash::make($generatedPassword),
            'is_auto_created'   => true,
            'email_verified_at' => now(),
        ]);

        return [
            'user'     => $newUser,
            'password' => $generatedPassword,
            'type'     => 'new'
        ];
    }

    public static function removeFromCompany(User $employee, Company $company): string
    {
        // Pehle detach karo
        $company->employees()->detach($employee->id);

        // Auto created user ka account bhi delete karo
        if ($employee->is_auto_created) {
            $employee->delete();
            return 'Employee removed and account deleted successfully';
        }

        return 'Employee removed from company successfully';
    }
}
