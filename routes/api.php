<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\EmailVerificationController; 
use App\Http\Controllers\CompanyInvitationController;

Route::post('/register', [AuthController::class, 'register']);        
Route::post('/login', [AuthController::class, 'login']);              
Route::post('/logout', [AuthController::class, 'logout']); 

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verifyEmail'])
     ->name('verification.verify'); 

Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']); 



// Protected routes (require authentication)
Route::middleware(App\Http\Middleware\AuthToken::class)->group(function () {
    Route::post('/company/invite', [AuthController::class, 'sendInvitation']);
    Route::delete('/company/employee', [CompanyInvitationController::class, 'removeEmployee']);
    Route::get('/company/data', [CompanyInvitationController::class, 'getCompanyData']);
});

// Public routes
Route::get('/company-invitation/accept/{token}', [CompanyInvitationController::class, 'acceptInvitation']);