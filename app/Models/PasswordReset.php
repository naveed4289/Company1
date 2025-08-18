<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordReset extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'email',
        'token',
        'created_at'
    ];

    /**
     * Create or replace password reset token
     */
    public static function generateToken($email, $token)
    {
        // Purane records delete kardo
        self::where('email', $email)->delete();

        // Naya record insert kardo
        return self::create([
            'email' => strtolower($email),
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
    }
}
