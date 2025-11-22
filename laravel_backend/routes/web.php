<?php
// path: laravel_backend/routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Homepage (Redirects based on role)
Route::get('/', [HomeController::class, 'index'])->name('home');

// --- Authentication Routes (for guests) ---
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

// --- Authenticated Routes ---
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('return-to-admin', [AuthController::class, 'returnToAdmin'])->name('return.to.admin');

    // --- REPORTING ROUTES (Available to all logged-in users) ---
    Route::get('report-issue', [ReportController::class, 'create'])->name('report.create');
    Route::post('report-issue', [ReportController::class, 'store'])->name('report.store');

    // --- STUDENT ROUTES ---
    Route::middleware('is.student')->prefix('student')->name('student.')->group(function () {
        Route::get('dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('enrollment', [StudentController::class, 'showEnrollmentPage'])->name('enrollment.page');
        Route::get('enroll-face', [StudentController::class, 'showEnrollForm'])->name('enroll.form');
        Route::post('enroll-face', [AttendanceController::class, 'enrollFace'])->name('enroll.face');
        Route::post('request-face-change', [StudentController::class, 'requestFaceChange'])->name('request.face.change');
        Route::post('find-session', [StudentController::class, 'findSession'])->name('find.session');
        Route::get('attend/session/{referral_code}', [StudentController::class, 'showAttendForm'])->name('attend.form');
        Route::post('mark-attendance', [AttendanceController::class, 'markAttendance'])->name('mark.attendance');
    });

    // --- LECTURER ROUTES ---
    Route::middleware('is.lecturer')->prefix('lecturer')->name('lecturer.')->group(function () {
        Route::get('dashboard', [LecturerController::class, 'dashboard'])->name('dashboard');
        Route::get('analytics', [AnalyticsController::class, 'show'])->name('analytics');

        Route::post('courses', [LecturerController::class, 'createCourse'])->name('course.create');
        Route::post('sessions', [LecturerController::class, 'createSession'])->name('session.create');
        Route::get('sessions/{session}', [LecturerController::class, 'showSession'])->name('session.show');
        Route::delete('sessions/{session}', [LecturerController::class, 'deleteSession'])->name('session.delete');
        Route::get('export/session/{session}', [AttendanceController::class, 'exportAttendance'])->name('attendance.export');
    });

    // --- ADMIN ROUTES ---
    Route::middleware('is.admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('analytics', [AnalyticsController::class, 'show'])->name('analytics');

        // Reports
        Route::get('reports', [AdminController::class, 'showReports'])->name('reports');
        Route::post('reports/{id}/resolve', [AdminController::class, 'resolveReport'])->name('report.resolve');

        // User Management
        Route::post('users', [AdminController::class, 'createUser'])->name('user.create');
        Route::delete('users/{user}', [AdminController::class, 'deleteUser'])->name('user.delete');
        Route::get('users/login-as/{user}', [AdminController::class, 'loginAs'])->name('user.loginas');
        Route::delete('users/{user}/enrollment', [AdminController::class, 'deleteEnrollment'])->name('user.enrollment.delete');

        // Session Management
        Route::delete('sessions/{session}', [AdminController::class, 'deleteSession'])->name('session.delete');
    });
});