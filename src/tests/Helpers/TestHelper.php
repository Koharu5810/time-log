<?php

namespace Tests\Helpers;

use App\Models\User;

class TestHelper
{
    public static function userLogin()
    {
        // 事前にユーザーを作成
        $user = User::factory()->create();

        /** @var \App\Models\User $user */   // $userの型解析ツールエラーが出るため追記
        auth()->login($user);

        return $user;
    }
}
