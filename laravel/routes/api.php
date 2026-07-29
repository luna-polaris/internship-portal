<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\EmployerController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::get('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/password/forgot', [AuthController::class, 'requestPasswordReset']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

Route::get('/companies', [CompanyController::class, 'index']);
Route::get('/companies/search', [CompanyController::class, 'search']);
Route::get('/companies/{company}', [CompanyController::class, 'show']);

// Authenticated (any role)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/password/change', [AuthController::class, 'changePassword']);

    Route::get('/me', [ProfileController::class, 'show']);
    Route::put('/me', [ProfileController::class, 'updateBasicInfo']);
    Route::put('/me/email', [ProfileController::class, 'updateEmail']);
    Route::post('/me/avatar', [ProfileController::class, 'uploadAvatar']);

    // Student-only
    Route::middleware('role:Student')->group(function () {
        Route::get('/student/profile', [StudentController::class, 'show']);
        Route::put('/student/profile', [StudentController::class, 'update']);
        Route::post('/student/resume', [StudentController::class, 'uploadResume']);
    });

    // Employer-only
    Route::middleware('role:Employer')->group(function () {
        Route::get('/employer/profile', [EmployerController::class, 'show']);
        Route::put('/employer/profile', [EmployerController::class, 'update']);
        Route::post('/employer/company', [CompanyController::class, 'store']);
        Route::put('/employer/company', [CompanyController::class, 'update']);
        Route::post('/employer/company/logo', [CompanyController::class, 'uploadLogo']);
    });

    // Admin-only
    Route::middleware('role:Admin')->prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'dashboardStats']);
        Route::get('/users', [AdminController::class, 'listUsers']);
        Route::patch('/users/{user}/activate', [AdminController::class, 'activateUser']);
        Route::patch('/users/{user}/deactivate', [AdminController::class, 'deactivateUser']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);
        Route::patch('/users/{user}/promote', [AdminController::class, 'promoteAdmin']);
        Route::patch('/users/{user}/demote', [AdminController::class, 'demoteAdmin']);
        Route::get('/students', [StudentController::class, 'index']);
        Route::get('/employers', [EmployerController::class, 'index']);
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy']);
    });
});
