<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;

// 会員登録画面
Route::get('/user/register', [AuthController::class, 'showRegistrationForm'])->withoutMiddleware(['auth'])->name('user.register.show');
Route::post('/user/register', [AuthController::class, 'register'])->withoutMiddleware(['auth'])->name('user.register');
// 一般ユーザログイン画面
Route::get('/user/login', [AuthController::class, 'showUsersLoginForm'])->name('user.login.show');
Route::post('/user/login', [AuthController::class, 'login'])->name('user.login');

// ログアウト機能
Route::post('/user/logout', [AuthController::class, 'userDestroy'])->name('user.logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('create');
});
