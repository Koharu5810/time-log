<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceCreateController;

// 会員登録画面
Route::get('/user/register', [AuthController::class, 'showRegistrationForm'])->withoutMiddleware(['auth'])->name('user.register.show');
Route::post('/user/register', [AuthController::class, 'register'])->withoutMiddleware(['auth'])->name('user.register');
// 一般ユーザログイン画面
Route::get('/user/login', [AuthController::class, 'showusersLoginForm'])->name('user.login.show');
Route::post('/user/login', [AuthController::class, 'login'])->name('user.login');

Route::middleware('auth')->group(function () {
    Route::get('/', [AttendanceCreateController::class, 'index'])->name('create');
});

Route::get('/', function () {
    return view('user.login');
});
