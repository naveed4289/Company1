<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserToken;

class ValidateAuthToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token not provided.'
            ], 400);
        }

        // Validate token exists in database for logout
        $userToken = UserToken::where('token', $token)->first();
        if (!$userToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token.'
            ], 400);
        }

        // Add token and user token to request for use in controller
        $request->merge([
            'auth_token' => $token,
            'user_token_model' => $userToken
        ]);

        return $next($request);
    }
}
