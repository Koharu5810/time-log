<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'name' => '管理者ユーザ1',
            'email' => 'admin1@test.com',
            'password' => 'password',
        ];
        Admin::create($param);

        $param = [
            'name' => '管理者ユーザ2',
            'email' => 'admin2@test.com',
            'password' => 'password',
        ];
        Admin::create($param);
    }
}
