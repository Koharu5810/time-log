<?php

namespace Tests\Helpers;

use App\Models\User;
use App\Models\Admin;

class TestHelper
{
    public static function userLogin()
    {
        // 事前にユーザーを作成
        $user = User::factory()->create();

        return $user;
    }
    public static function adminLogin()
    {
        $admin = Admin::where('email', 'admin1@test.com')->first();

        if (!$admin) {
            $admin = Admin::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        // **管理者ガードでログイン**
        auth()->guard('admin')->login($admin);

        return $admin;
    }
}
