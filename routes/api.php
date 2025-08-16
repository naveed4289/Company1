<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\CompanyInvitationController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\MessageController;

// =============================================
// AuthController - Authentication APIs
// =============================================
// POST /api/register → Register a new user
Route::post('/register', [AuthController::class, 'register']);
// POST /api/login → Login and get token
Route::post('/login', [AuthController::class, 'login'])->middleware(['login.email.verification']);;
// POST /api/logout → Logout current token
Route::post('/logout', [AuthController::class, 'logout'])->middleware(['validate.auth.token']);

// =============================================  
// EmailVerificationController - Email verification APIs
// =============================================
// GET /api/email/verify/{id}/{hash} → Verify email via signed URL
// Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verifyEmail'])
//     ->middleware(['validate.email.verification'])
//     ->name('verification.verify');
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verifyEmail'])
     ->middleware(['signed', 'validate.email.verification'])
    ->name('verification.verify');

// =============================================
// PasswordResetController - Password reset APIs
// =============================================
// POST /api/forgot-password → Send reset link
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])
    ->middleware(['validate.user.exists']);
// POST /api/reset-password → Reset password with token
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
    ->middleware(['validate.password.reset.token']);


// =============================================
// Protected routes (require auth token) 
// =============================================
Route::middleware(['auth.token'])->group(function () {

    // -----------------------------------------
    // CompanyInvitationController - Company APIs
    // -----------------------------------------
    // POST /api/company/invite → Invite user to company (creates user if not exists)
    Route::post('/company/invite', [CompanyInvitationController::class, 'sendCompanyInvitation'])
        ->middleware(['user.has.company', 'prevent.duplicate.invitation']);
    // DELETE /api/company/employee → Remove employee from company
    Route::delete('/company/employee', [CompanyInvitationController::class, 'removeCompanyEmployee'])
        ->middleware(['user.has.company', 'validate.employee.exists']);
    // GET /api/company/data → Get company with employees and pending invitations
    Route::get('/company/data', [CompanyInvitationController::class, 'getCompanyData'])
        ->middleware(['user.has.company']);

    // -----------------------------------------
    // ChannelController - Channel management APIs
    // -----------------------------------------
    // GET /api/channels → Get all channels visible to user (public + user's private)
    Route::get('/channels', [ChannelController::class, 'getChannels'])->middleware(['company.associated']);
    // GET /api/channels/public → Get all public channels in user's company
    Route::get('/channels/public', [ChannelController::class, 'getPublicChannels'])->middleware(['company.associated']);
    // GET /api/channels/private → Get user's private channels (creator or member)
    Route::get('/channels/private', [ChannelController::class, 'getPrivateChannels'])->middleware(['company.associated']);
    // POST /api/channels → Create a channel for a company
    Route::post('/channels', [ChannelController::class, 'createChannels'])->middleware(['company.access']);
    // POST /api/channels/members → Add member to private channel
    Route::post('/channels/members', [ChannelController::class, 'addMember'])
        ->middleware(['channel.load', 'channel.manage', 'channel.private', 'channel.member.same_company']);
    // DELETE /api/channels/members → Remove member from private channel
    Route::delete('/channels/members', [ChannelController::class, 'removeMember'])
        ->middleware(['channel.load', 'channel.manage', 'channel.private', 'channel.member.same_company']);
    // PATCH /api/channels/{id} → Update channel (owner)
    // DELETE /api/channels/{id} → Delete channel (owner)
    Route::middleware(['channel.owner', 'channel.load'])->group(function () {
        Route::patch('/channels/{id}', [ChannelController::class, 'updateChannels']);
        Route::delete('/channels/{id}', [ChannelController::class, 'removeChannels']);
    });

    // -----------------------------------------
    // MessageController - Channel messages APIs
    // -----------------------------------------
    // GET /api/channels/{channelId}/messages → List messages (requires channel access)
    Route::get('/channels/{channelId}/messages', [MessageController::class, 'getMessages'])
        ->middleware(['channel.load', 'channel.access']);
    // POST /api/channels/messages → Send message (channel_id in body, requires channel access)
    Route::post('/channels/messages', [MessageController::class, 'sendMessage'])
        ->middleware(['channel.load', 'channel.access']);
    // DELETE /api/messages/{messageId} → Delete message (sender, channel creator, or company owner)
    Route::delete('/messages/{messageId}', [MessageController::class, 'deleteMessage'])
        ->middleware(['message.delete']);
});

// =============================================
// Public - Accept company invitation
// =============================================
// GET /api/company-invitation/accept/{token} → Accept invitation link
Route::get('/company-invitation/accept/{token}', [CompanyInvitationController::class, 'acceptCompanyInvitation'])
    ->name('company.invitation.accept')
    ->middleware('validate.invitation.token');
