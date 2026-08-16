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

Route::get('/internships', function () {
    return view('internships.browse');
});

Route::get('/internships/{internship}', function ($internship) {
    return view('internships.show', ['internship' => $internship]);
});

Route::get('/employer/internships', function () {
    return view('employer.internships');
});

Route::get('/employer/internships/{internship}/applications', function ($internship) {
    return view('employer.applications', ['internship' => $internship]);
});

Route::get('/student/applications', function () {
    return view('student.applications');
});

Route::get('/student/bookmarks', function () {
    return view('student.bookmarks');
});

/*
|--------------------------------------------------------------------------
| Module 3.2 — pages
|--------------------------------------------------------------------------
*/
Route::view('/admin/dashboard', 'admin.dashboard')->name('admin.dashboard');

Route::view('/evaluations', 'evaluations.index')->name('evaluations.index');
Route::view('/evaluations/create', 'evaluations.create')->name('evaluations.create');
Route::view('/performance', 'evaluations.dashboard')->name('performance.dashboard');
Route::view('/admin/evaluations', 'admin.evaluations.review')->name('admin.evaluations.review');
Route::view('/notifications', 'evaluations.notifications')->name('notifications.index');
Route::view('/admin/criteria', 'admin.evaluations.criteria')->name('admin.criteria');
Route::view('/evaluations/{evaluation}', 'evaluations.show')->name('evaluations.show');
Route::view('/admin/evaluations/{evaluation}', 'evaluations.show')->name('admin.evaluations.show');