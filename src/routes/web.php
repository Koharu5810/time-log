<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCreateController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// メール認証ルート設定
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
});

// 会員登録画面
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->withoutMiddleware(['auth'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->withoutMiddleware(['auth'])->name('registration');
// メール認証画面
Route::get('verify-email', function () {
    return view('auth.verify-email');
});
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
    // CSV出力
    Route::get('/admin/attendance/export/{id}', [AttendanceController::class, 'exportMonthlyAttendance'])->name('admin.attendance.export');

    // 修正申請承認（管理者）
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AttendanceRequestController::class, 'showApproveDetail'])->name('show.request.approve');
    Route::patch('/stamp_correction_request/approve/{attendance_correct_request}', [AttendanceRequestController::class, 'approve'])->name('request.approve');
});

Route::middleware(['auth:web,admin'])->group(function () {
    // 勤怠詳細画面（一般・管理者）
    Route::get('/attendance/{id}', [AttendanceRequestController::class, 'showAttendanceDetail'])->name('attendance.detail');
    Route::put('/attendance/{id}', [AttendanceRequestController::class, 'updateRequest'])->name('attendance.update');

    // 修正申請一覧画面表示（一般・管理者）
    Route::get('/stamp_correction_request/list', [AttendanceRequestController::class, 'showRequestList'])->name('request.list');
});
