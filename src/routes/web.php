<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;

// 会員登録画面
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->withoutMiddleware(['auth'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->withoutMiddleware(['auth'])->name('registration');
// ログイン画面（一般）
Route::get('/login', [AuthController::class, 'showUsersLoginForm'])->name('login.show');
Route::post('/login', [AuthController::class, 'login'])->name('user.login');
// ログアウト機能
Route::post('/logout', [AuthController::class, 'userDestroy'])->name('user.logout');

Route::middleware('auth')->group(function () {
    // 勤怠登録画面（一般）
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // 勤怠一覧画面（一般）
    Route::get('/attendance/list', [AttendanceController::class, 'showUserAttendanceList'])->name('attendance.list');
    // 勤怠詳細画面（一般）
    Route::get('/attendance/{id}', [AttendanceController::class, 'showAttendanceDetail'])->name('attendance.detail');
    Route::post('/attendance/{id}', [AttendanceController::class, 'updateRequest'])->name('attendance.update');

    // ↓一般・管理者同様パス。認証ミドルウェアで区別を実装
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'showRequestList'])->name('request.list');
});
