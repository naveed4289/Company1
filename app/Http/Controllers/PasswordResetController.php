<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordResetService;

class PasswordResetController extends Controller
{
    protected PasswordResetService $passwordResetService;
    
    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }
    
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = $request->user_model; // Coming from middleware
        
        $response = $this->passwordResetService->sendResetLink($user);
        
        return response()->json($response);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = $request->user_model; // Coming from middleware
        $resetRecord = $request->reset_record; // Coming from middleware
        
        $response = $this->passwordResetService->resetUserPassword($user, $request->password, $resetRecord);
        
        return response()->json($response);
    }
}