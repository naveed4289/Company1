<?php

namespace App\Http\Middleware;


use Closure;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthToken
{
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $userToken = UserToken::where('token', $token)->first();

        if (!$userToken) {
            return response()->json(['message' => 'Invalid token.'], 401);
        }

        // Set authenticated user in Laravel's Auth facade
        auth()->setUser($userToken->user);
        
        // Also attach user to request for backwards compatibility
        $request->merge(['auth_user' => $userToken->user]);

        return $next($request);
    }
}
