<?php

use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/verify-email', [VerificationController::class, 'show'])->name('verify-email');

Route::get('/profile', function () {
    return view('auth.profile');
})->name('profile');
