<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [];

        for ($i = 1; $i <= 10; $i++) {
            $users[] = [
                'name' => "スタッフ{$i}",
                'email' => "staff{$i}@test.com",
                'password' => 'password',
            ];
        }

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
