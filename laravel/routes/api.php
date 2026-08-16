<?php

use App\Http\Controllers\Api\EvaluableStudentController;
use App\Http\Controllers\Api\EvaluationController;
use App\Http\Controllers\Api\EvaluationCriterionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PerformanceDashboardController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\EmployerApplicationController;
use App\Http\Controllers\Api\EmployerController;
use App\Http\Controllers\Api\InternshipController;
use App\Http\Controllers\Api\InterviewController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PublicInternshipController;
use App\Http\Controllers\Api\StudentApplicationController;
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

Route::get('/internships', [PublicInternshipController::class, 'index']);
Route::get('/internships/{internship}', [PublicInternshipController::class, 'show']);

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
        Route::get('/student/internships/recommended', [StudentController::class, 'recommended']);

        Route::post('/student/internships/{internship}/apply', [StudentApplicationController::class, 'apply']);
        Route::get('/student/applications', [StudentApplicationController::class, 'index']);
        Route::get('/student/applications/{application}', [StudentApplicationController::class, 'show']);
        Route::patch('/student/applications/{application}/withdraw', [StudentApplicationController::class, 'withdraw']);

        Route::post('/student/internships/{internship}/bookmark', [BookmarkController::class, 'toggle']);
        Route::get('/student/bookmarks', [BookmarkController::class, 'index']);
    });

    // Employer-only
    Route::middleware('role:Employer')->group(function () {
        Route::get('/employer/profile', [EmployerController::class, 'show']);
        Route::put('/employer/profile', [EmployerController::class, 'update']);
        Route::post('/employer/company', [CompanyController::class, 'store']);
        Route::put('/employer/company', [CompanyController::class, 'update']);
        Route::post('/employer/company/logo', [CompanyController::class, 'uploadLogo']);

        Route::get('/employer/internships', [InternshipController::class, 'index']);
        Route::post('/employer/internships', [InternshipController::class, 'store']);
        Route::get('/employer/internships/{internship}', [InternshipController::class, 'show']);
        Route::put('/employer/internships/{internship}', [InternshipController::class, 'update']);
        Route::patch('/employer/internships/{internship}/publish', [InternshipController::class, 'publish']);
        Route::patch('/employer/internships/{internship}/close', [InternshipController::class, 'close']);
        Route::delete('/employer/internships/{internship}', [InternshipController::class, 'destroy']);

        Route::get('/employer/internships/{internship}/applications', [EmployerApplicationController::class, 'index']);
        Route::get('/employer/applications/{application}', [EmployerApplicationController::class, 'show']);
        Route::patch('/employer/applications/{application}/accept', [EmployerApplicationController::class, 'accept']);
        Route::patch('/employer/applications/{application}/reject', [EmployerApplicationController::class, 'reject']);
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
        Route::get('/internships', [AdminController::class, 'listInternships']);
        Route::get('/students', [StudentController::class, 'index']);
        Route::get('/employers', [EmployerController::class, 'index']);
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy']);
    });

    /*
|--------------------------------------------------------------------------
| Module 3.2 — Feedback & Performance Evaluation
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // --- Criteria definition (read: any signed-in user, write: Admin only) ---
    Route::get('evaluable-students', [EvaluableStudentController::class, 'index']);
    Route::get('criteria', [EvaluationCriterionController::class, 'index']);
    Route::get('criteria/{criterion}', [EvaluationCriterionController::class, 'show']);
    Route::post('criteria', [EvaluationCriterionController::class, 'store']);
    Route::put('criteria/{criterion}', [EvaluationCriterionController::class, 'update']);
    Route::delete('criteria/{criterion}', [EvaluationCriterionController::class, 'destroy']);

    // --- Feedback submission + review ---
    Route::get('evaluations', [EvaluationController::class, 'index']);
    Route::post('evaluations', [EvaluationController::class, 'store']);   // save draft or submit
    Route::get('evaluations/{evaluation}', [EvaluationController::class, 'show']);
    Route::delete('evaluations/{evaluation}', [EvaluationController::class, 'destroy']);
    Route::post('evaluations/{evaluation}/review', [EvaluationController::class, 'review']);

    // --- Dashboard and analytics (role-aware payload) ---
    Route::get('performance/dashboard', [PerformanceDashboardController::class, 'index']);

    // --- Notifications and alerts ---
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);

    /*
    |----------------------------------------------------------------------
    | Module 4 — Interview Scheduling & Notification
    |
    | Read (index/show): any signed-in role, scoped in the controller —
    | Student sees only their own, Employer only their company's, Admin all.
    | Write (schedule/reschedule/cancel/complete): Employer (own company) or
    | Admin, also enforced in the controller since it needs the ownership
    | chain through Application -> Internship -> Company -> Employer.
    |----------------------------------------------------------------------
    */
    Route::get('interviews', [InterviewController::class, 'index']);
    Route::get('interviews/{interview}', [InterviewController::class, 'show']);
    Route::post('applications/{application}/interviews', [InterviewController::class, 'store']);
    Route::patch('interviews/{interview}/reschedule', [InterviewController::class, 'reschedule']);
    Route::patch('interviews/{interview}/cancel', [InterviewController::class, 'cancel']);
    Route::patch('interviews/{interview}/complete', [InterviewController::class, 'complete']);

    // Student proposes a new time; Employer/Admin confirms or declines it.
    Route::post('interviews/{interview}/reschedule-request', [InterviewController::class, 'requestReschedule']);
    Route::patch('interviews/{interview}/reschedule-request/approve', [InterviewController::class, 'approveReschedule']);
    Route::patch('interviews/{interview}/reschedule-request/decline', [InterviewController::class, 'declineReschedule']);
});
});
