<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceCreateController extends Controller
{
// 勤怠登録画面表示
    public function index() {
        return view('attendance.create');
    }
}
