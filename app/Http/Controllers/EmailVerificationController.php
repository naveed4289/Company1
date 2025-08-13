<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\VerifyEmailRequest;
use App\Services\EmailVerificationService;

class EmailVerificationController extends Controller
{
    protected EmailVerificationService $emailVerificationService;
    
    public function __construct(EmailVerificationService $emailVerificationService)
    {
        $this->emailVerificationService = $emailVerificationService;
    }
    
    public function sendVerificationEmail(User $user)
    {
        $this->emailVerificationService->sendVerificationEmail($user);
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        $user = $request->user_model; // Coming from middleware
        
        $response = $this->emailVerificationService->verifyUserEmail($user);
        
        return response()->json($response);
    }
}