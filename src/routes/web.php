<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;

// 会員登録画面
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->withoutMiddleware(['auth'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->withoutMiddleware(['auth'])->name('registration');
// ログイン画面（一般）
Route::get('/login', [AuthController::class, 'showUsersLoginForm'])->name('login.show');
Route::post('/login', [AuthController::class, 'login'])->name('user.login');
// ログアウト機能（一般）
Route::post('/logout', [AuthController::class, 'userDestroy'])->name('user.logout');

// ログイン画面（管理者）
Route::get('/admin/login', [AdminController::class, 'showAdminLoginForm'])->name('admin.login.show');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login');
// ログアウト機能（管理者）
Route::post('/admin/logout', [AdminController::class, 'adminDestroy'])->name('admin.logout');

Route::middleware('auth')->group(function () {
    // 勤怠登録画面（一般）
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // 勤怠一覧画面（一般）
    Route::get('/attendance/list', [AttendanceController::class, 'showUserAttendanceList'])->name('attendance.list');
    // 勤怠詳細画面（一般）
    Route::get('/attendance/{id}', [AttendanceController::class, 'showAttendanceDetail'])->name('attendance.detail');
    Route::post('/attendance/{id}', [AttendanceController::class, 'updateRequest'])->name('attendance.update');

    // 勤怠申請一覧（↓一般・管理者同様パス。認証ミドルウェアで区別を実装）
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'showRequestList'])->name('request.list');
});

Route::middleware('auth:admin')->group(function () {
    // 勤怠一覧表示（管理者）
    Route::get('/admin/attendance/list', [AdminDashboardController::class, 'showAdminDashBoard'])->name('admin.dashboard');

    // スタッフ一覧画面（管理者）
    Route::get('/admin/staff/list', [AdminDashboardController::class, 'showStaffList'])->name('staff.list');

    // 勤怠一覧画面（管理者）
    Route::get('/attendance/list', [AdminDashboardController::class, 'showStaffAttendanceList'])->name('staff.attendance.list');
});
