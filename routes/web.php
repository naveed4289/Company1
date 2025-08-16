<?php

use App\Http\Controllers\EmailVerificationController;
use App\Http\Requests\VerifyEmailRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::get('/reset-password', function (Request $request) {
    return view('auth.reset-password', [
        'token' => $request->query('token'),
        'email' => $request->query('email')
    ]);
})->name('password.reset');

// Route::get('/email/verify/{id}/{hash}', function (VerifyEmailRequest $request) {
//     return app(EmailVerificationController::class)->verifyEmail($request);
// })->name('verification.verify');

// Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verifyEmail'])
//     ->middleware(['validate.email.verification'])
//     ->name('verification.verify');