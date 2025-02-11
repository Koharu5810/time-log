<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
// 勤怠登録画面表示
    public function index() {
        return view('attendance.create');
    }
}
