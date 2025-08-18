<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginCredentialsRequest;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AuthResponse;

class AuthController extends Controller
{
    /**
     * Handle user registration.
     * - Creates a new user with the provided data
     * - Creates a default company for the user
     * - Sends email verification link
     * - Returns a standardized AuthResponse
     */
    public function register(RegisterRequest $request)
    {
        $user = User::createUser($request->all());
        User::createDefaultCompany($user);
        app(EmailVerificationController::class)->sendVerificationEmail($user);
        return AuthResponse::registered($user);
    }

    /**
     * Handle user login.
     * - Validates login credentials (handled by LoginCredentialsRequest)
     * - Retrieves authenticated user and loads related company
     * - Generates a new token for this session/device
     * - Returns a standardized AuthResponse with user + token
     */
    public function login(LoginCredentialsRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('company');
        $token = UserToken::generateFor($user, $request->header('User-Agent'));
        return AuthResponse::loggedIn($user, $token);
    }

    /**
     * Handle user logout.
     * - Deletes the current user token from database
     * - Returns a standardized AuthResponse with logout message
     */
    public function logout(Request $request)
    {
        $userToken = $request->user_token_model;
        $userToken->delete();
        return AuthResponse::loggedOut();
    }
}
