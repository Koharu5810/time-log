<?php

namespace Tests\Helpers;

use App\Models\User;

class TestHelper
{
    public static function userLogin()
    {
        // 事前にユーザーを作成
        $user = User::factory()->create();

        return $user;
    }
}
