<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest; 
use App\Http\Requests\LoginRequest;
use App\Models\CompanyInvitation;
use App\Mail\CompanyInvitationMail;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\Mail\VerifyEmailMail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    
public function register(RegisterRequest $request)
{
    try {
        $data = $request->validated();

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => strtolower($data['email']),
            'password'   => Hash::make($data['password']),
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

    } catch (\Throwable $e) {
        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function login(LoginRequest $request)
{
    try {
        $credentials = [
            'email' => strtolower($request->email),
            'password' => $request->password
        ];

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials.'
            ], 401);
        }

        $user = Auth::user();
        $user->load('company'); // eager load company relation

        // Allow auto-created users to login even if email_verified_at is somehow null
        if (is_null($user->email_verified_at) && !$user->is_auto_created) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please verify your email to activate your account.'
            ], 403);
        }

        // If auto-created user doesn't have email_verified_at set, set it now
        if ($user->is_auto_created && is_null($user->email_verified_at)) {
            $user->email_verified_at = now();
            $user->save();
        }

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

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}


    public function logout(Request $request)
    {
        //check token provided in header
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token not provided.'
            ], 400);
        }
        //delete user token to logout
        $deleted = UserToken::where('token', $token)->delete();

        if ($deleted) {
            return response()->json([
                'status' => 'success',
                'message' => 'Logged out from this device.'
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid token.'
        ], 400);
    }
    
public function sendInvitation(Request $request)
{
    try {
        // Validate request - remove exists validation to allow non-existing users
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        // Get authenticated user
        $user = auth()->user();
        
        // Check company exists
        $company = $user->company;
        if (!$company) {
            return response()->json(['message' => 'You do not have a company'], 403);
        }

        // Check if user is already an employee
        $isEmployee = $company->employees()->where('email', $request->email)->exists();
        if ($isEmployee) {
            return response()->json(['message' => 'User is already an employee'], 400);
        }

        // Check for existing pending invitations
        $existingInvitation = CompanyInvitation::where('company_id', $company->id)
            ->where('email', $request->email)
            ->whereNull('accepted_at')
            ->first();

        if ($existingInvitation) {
            return response()->json(['message' => 'Invitation already sent'], 400);
        }

        // Check if user exists
        $existingUser = User::where('email', $request->email)->first();
        $generatedPassword = null;
        $createdUser = null;

        if (!$existingUser) {
            // Create user account with generated password
            $generatedPassword = Str::random(12);
            $createdUser = User::create([
                'first_name' => 'New',
                'last_name' => 'Employee',
                'email' => $request->email,
                'password' => Hash::make($generatedPassword),
                'is_auto_created' => true,
                'email_verified_at' => now(), // Auto-verify for invited users
            ]);
            
            // Double-check that email_verified_at was set
            $createdUser->refresh();
            if (is_null($createdUser->email_verified_at)) {
                // Force set it if it wasn't saved properly
                $createdUser->email_verified_at = now();
                $createdUser->save();
            }
        }

        // Create invitation
        $token = Str::random(40);
        $invitation = CompanyInvitation::create([
            'company_id' => $company->id,
            'email' => $request->email,
            'token' => $token,
            'generated_password' => $generatedPassword,
            'user_id' => $createdUser ? $createdUser->id : $existingUser->id,
        ]);

        // Send email
        try {
            Mail::to($request->email)->send(new CompanyInvitationMail($invitation));
        } catch (\Exception $mailException) {
            // If email fails and we created a user, delete it
            if ($createdUser) {
                $createdUser->delete();
            }
            $invitation->delete();
            return response()->json(['message' => 'Failed to send invitation email'], 500);
        }

        return response()->json([
            'message' => 'Invitation sent successfully',
            'user_type' => $existingUser ? 'existing' : 'new',
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred: ' . $e->getMessage()
        ], 500);
    }
}
}
