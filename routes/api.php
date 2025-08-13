<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\EmailVerificationController; 
use App\Http\Controllers\CompanyInvitationController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\MessageController;

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
    
    // Channel management routes
    Route::get('/channels', [ChannelController::class, 'index']); // Get all user's channels
    Route::post('/channels', [ChannelController::class, 'store']); // Create channel
    
    // Channel member management
    Route::post('/channels/{channelId}/members', [ChannelController::class, 'addMember']); // Add member to private channel
    Route::delete('/channels/{channelId}/members', [ChannelController::class, 'removeMember']); // Remove member from private channel
    
    // Message routes
    Route::get('/channels/{channelId}/messages', [MessageController::class, 'getMessages']); // Get channel messages
    Route::post('/channels/{channelId}/messages', [MessageController::class, 'sendMessage']); // Send message
    Route::delete('/messages/{messageId}', [MessageController::class, 'deleteMessage']); // Delete message
    
    // Routes that require channel ownership (creator or company owner)
    Route::middleware(App\Http\Middleware\ChannelOwnerMiddleware::class)->group(function () {
        Route::patch('/channels/{id}', [ChannelController::class, 'update']);  // Update channel
        Route::delete('/channels/{id}', [ChannelController::class, 'destroy']); // Remove channel
    });
});

// Public routes
Route::get('/company-invitation/accept/{token}', [CompanyInvitationController::class, 'acceptInvitation']);