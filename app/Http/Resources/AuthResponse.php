<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthResponse extends JsonResource
{
    protected $message;
    protected $token;

    public function __construct($resource = null, $token = null, $message = null)
    {
        parent::__construct($resource);
        $this->token = $token;
        $this->message = $message;
    }

    public function toArray($request)
    {
        return [
            'status' => 'success',
            'message' => $this->message,
            'data' => $this->when($this->resource, [
                'user' => [
                    'id' => $this->id,
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'email' => $this->email,
                    'company_name' => $this->company ? $this->company->name : null,
                ],
                'token' => $this->token,
            ]),
        ];
    }

    // ✅ Auth Responses
    public static function registered($user)
    {
        return new self($user, null, 'Account created. Please verify your email to activate your account.');
    }

    public static function loggedIn($user, $token)
    {
        return new self($user, $token, 'Login successful.');
    }

    public static function loggedOut()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully.'
        ]);
    }

    // ✅ Password Reset Responses
    public static function passwordResetLinkSent()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Password reset link has been sent to your email.'
        ]);
    }

    public static function passwordResetSuccess()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Password has been reset successfully.'
        ]);
    }
}
