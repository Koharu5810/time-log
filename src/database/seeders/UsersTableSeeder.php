<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users=[
            ['id' => 1, 'name' => '西 怜奈', 'email' => 'reina.n@coachtech.com'],
            ['id' => 2, 'name' => '山田 太郎', 'email' => 'taro.y@coachtech.com'],
            ['id' => 3, 'name' => '増田 一世', 'email' => 'issei.m@coachtech.com'],
            ['id' => 4, 'name' => '山本 敬吉', 'email' => 'keikichi.y@coachtech.com'],
            ['id' => 5, 'name' => '秋田 朋美', 'email' => 'tomomi.a@coachtech.com'],
            ['id' => 6, 'name' => '中西 教夫', 'email' => 'norio.n@coachtech.com'],
            ['id' => 7, 'name' => '山田 花子', 'email' => 'hanako.y@coachtech.com'],
            ['id' => 8, 'name' => '松本 四郎', 'email' => 'shiro.m@coachtech.com'],
            ['id' => 9, 'name' => '小川 七美', 'email' => 'nanami.o@coachtech.com'],
            ['id' => 10, 'name' => '鈴木 歌子', 'email' => 'utako.s@coachtech.com'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'email_verified_at' => now(),
                    'password' => 'password',
                ],
            );
        }
    }
}
