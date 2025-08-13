<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginCredentialsRequest;
use App\Http\Requests\SendInvitationRequest;
use App\Models\CompanyInvitation;
use App\Mail\CompanyInvitationMail;
use App\Models\User;
use App\Models\UserToken;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

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
        $user->company()->create([
            'name' => $companyName
        ]);

        // Email verification
        app(EmailVerificationController::class)->sendVerificationEmail($user);

        return response()->json([
            'message' => 'Account created. Please verify your email to activate your account.'
        ], 201);
    }

    public function login(LoginCredentialsRequest $request)
    {
        if (!Auth::attempt($request->getCredentials())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials.'
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        if (!$this->authService->isEmailVerified($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please verify your email to activate your account.'
            ], 403);
        }

        $this->authService->handleEmailVerification($user);
        $user->load('company');

        $token = $this->authService->createToken($user, $request->header('User-Agent'));

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful.',
            'user' => $this->authService->formatUserResponse($user),
            'token' => $token
        ], 200);
    }


    public function logout(Request $request)
    {
        $userToken = $request->user_token_model; // Coming from middleware
        $userToken->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out from this device.'
        ], 200);
    }

    public function sendInvitation(SendInvitationRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $company = $user->company;

        $existingUser = User::where('email', $request->email)->first();
        $generatedPassword = null;
        $createdUser = null;
        $userType = 'existing';

        if (!$existingUser) {
            $generatedPassword = Str::random(12);
            $createdUser = User::create([
                'first_name' => 'New',
                'last_name' => 'Employee',
                'email' => $request->email,
                'password' => Hash::make($generatedPassword),
                'is_auto_created' => true,
                'email_verified_at' => now(),
            ]);
            $userType = 'new';
        }

        $invitation = CompanyInvitation::create([
            'company_id' => $company->id,
            'email' => $request->email,
            'token' => Str::random(40),
            'generated_password' => $generatedPassword,
            'user_id' => $createdUser ? $createdUser->id : $existingUser->id,
        ]);

        Mail::to($request->email)->send(new CompanyInvitationMail($invitation));

        return response()->json([
            'message' => 'Invitation sent successfully',
            'user_type' => $userType,
        ]);
    }
}
