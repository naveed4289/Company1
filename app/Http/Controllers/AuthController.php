<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginCredentialsRequest;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => strtolower($request->email),
            'password'   => Hash::make($request->password),
        ]);

        // Create company for this user
        $companyName = $user->first_name . ' ' . $user->last_name . ' Company';
        $user->company()->create(['name' => $companyName]);

        // Email verification
        app(EmailVerificationController::class)->sendVerificationEmail($user);

        return response()->json([
            'message' => 'Account created. Please verify your email to activate your account.'
        ], 201);
    }

    public function login(LoginCredentialsRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('company');

        $token = uniqid('tok_', true);
        UserToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'device_name' => $request->header('User-Agent')
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful.',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'company_name' => $user->company ? $user->company->name : null,
            ],
            'token' => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        $userToken = $request->user_token_model;
        $userToken->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out from this device.'
        ], 200);
    }
}