<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCreateController;
use App\Http\Controllers\AttendanceRequestController;
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

// 一般ユーザ
Route::middleware('auth')->group(function () {
    // 勤怠登録画面
    Route::get('/attendance', [AttendanceCreateController::class, 'index'])->name('create');
    Route::post('/attendance', [AttendanceCreateController::class, 'store'])->name('attendance.store');
    // 勤怠一覧画面（一般）
    Route::get('/attendance/list', [AttendanceController::class, 'showAttendanceList'])->name('attendance.list');
});

// 管理者
Route::middleware('auth:admin')->group(function () {
    // 勤怠一覧表示
    Route::get('/admin/attendance/list', [AdminController::class, 'showAdminDashBoard'])->name('admin.dashboard');
    // スタッフ一覧画面
    Route::get('/admin/staff/list', [AdminController::class, 'showStaffList'])->name('staff.list');
    // スタッフ別勤怠一覧画面
    Route::get('/admin/attendance/staff/{id}', [AttendanceController::class, 'showAttendanceList'])->name('admin.attendance.list');
});

Route::middleware(['auth:web,admin'])->group(function () {
    // 勤怠詳細画面（一般・管理者）
    Route::get('/attendance/{id}', [AttendanceController::class, 'showAttendanceDetail'])->name('attendance.detail');
    Route::put('/attendance/{id}', [AttendanceRequestController::class, 'updateRequest'])->name('attendance.update');

    // 修正申請一覧（一般・管理者）
    Route::get('/stamp_correction_request/list', [AttendanceRequestController::class, 'showRequestList'])->name('request.list');

    // 修正申請承認（管理者）
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AttendanceRequestController::class, 'showApprove'])->name('show.request.approve');
    Route::patch('/stamp_correction_request/approve/{attendance_correct_request}', [AttendanceRequestController::class, 'approve'])->name('request.approve');
});
