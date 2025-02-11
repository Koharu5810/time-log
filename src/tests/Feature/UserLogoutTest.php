<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserLogoutTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_authUser_can_logout_and_redirect_to_home()
    {
        /** @var */
        $user = User::factory()->create([
            'name' => 'TEST USER',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->assertNotNull($user);
        // ユーザをログイン状態にする
        $this->actingAs($user);

        $response = $this->get(route('create'));
        $response->assertSee('ログアウト');
        $response->assertStatus(200);

        $response = $this->post(route('user.logout'));
        $response->assertStatus(302);
        $response->assertRedirect(route('user.login'));

        $this->assertGuest();
    }
}
