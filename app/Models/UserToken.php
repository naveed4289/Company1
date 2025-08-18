<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'device_name'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔹 Token generate karne ka static method
    public static function generateFor(User $user, string $deviceName)
    {
        $token = uniqid('tok_', true);

        self::create([
            'user_id' => $user->id,
            'token' => $token,
            'device_name' => $deviceName,
        ]);

        return $token;
    }
}
