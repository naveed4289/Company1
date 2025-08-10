<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::get('/reset-password', function (Request $request) {
    return view('auth.reset-password', [
        'token' => $request->query('token'),
        'email' => $request->query('email')
    ]);
})->name('password.reset');