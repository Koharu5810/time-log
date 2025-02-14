<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;

// 会員登録画面
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->withoutMiddleware(['auth'])->name('user.register.show');
Route::post('/register', [AuthController::class, 'register'])->withoutMiddleware(['auth'])->name('user.register');
// 一般ユーザログイン画面
Route::get('/login', [AuthController::class, 'showUsersLoginForm'])->name('user.login.show');
Route::post('/login', [AuthController::class, 'login'])->name('user.login');

// ログアウト機能
Route::post('/logout', [AuthController::class, 'userDestroy'])->name('user.logout');

Route::middleware('auth')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    Route::get('/attendance/list', [AttendanceController::class, 'showUserAttendanceList'])->name('user.attendance.list');
    // ↓一般・管理者同様パス。認証ミドルウェアで区別を実装
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'showRequestList'])->name('request.list');
    Route::get('/attendance/{id}', [AttendanceController::class, 'showAttendanceDetail'])->name('request.detail');
});
