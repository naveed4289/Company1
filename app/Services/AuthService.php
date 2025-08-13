<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function handleEmailVerification(User $user): void
    {
        // Auto-verify auto-created users
        if ($user->is_auto_created && is_null($user->email_verified_at)) {
            $user->update(['email_verified_at' => now()]);
        }
    }

    public function isEmailVerified(User $user): bool
    {
        // Allow auto-created users to proceed even if email_verified_at is somehow null
        return !is_null($user->email_verified_at) || $user->is_auto_created;
    }

    public function createToken(User $user, string $deviceName = null): string
    {
        $token = uniqid('tok_', true);

        UserToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'device_name' => $deviceName
        ]);

        return $token;
    }

    public function formatUserResponse(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'company_name' => $user->company ? $user->company->name : null,
        ];
    }
}
