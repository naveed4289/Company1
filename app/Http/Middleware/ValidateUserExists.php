<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class ValidateUserExists
{
    public function handle(Request $request, Closure $next)
    {
        $email = strtolower($request->email);
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account with this email does not exist.'
            ], 404);
        }

        // Add user to request for use in controller
        $request->merge(['user_model' => $user]);

        return $next($request);
    }
}
