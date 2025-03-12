<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Tests\Helpers\TestHelper;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // CSRF トークン検証を無効化
        $this->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    }

    private function openLoginPage()
    {
        return $this->get('/login')
            ->assertStatus(200)
            ->assertSeeText('ログインする')
            ->assertSee('<form', false);
    }
    private function assertValidationError($data, $expectedErrors)
    {
        $response = $this->post(route('user.login'), $data);
        $response->assertStatus(302);
        $response->assertSessionHasErrors($expectedErrors);
    }

    public function test_email_validation_error_when_email_is_missing()
    {
        $this->openLoginPage();

        $user = TestHelper::userLogin();

        $data = [
            'email' => '',
            'password' => 'password123',
        ];
        $expectedErrors = ['email' => 'メールアドレスを入力してください'];

        $this->assertValidationError($data, $expectedErrors);
    }
    public function test_password_validation_error_when_password_is_missing()
    {
        $this->openLoginPage();

        $user = TestHelper::userLogin();

        $data = [
            'email' => 'TEST USER',
            'password' => '',
        ];
        $expectedErrors = ['password' => 'パスワードを入力してください'];

        $this->assertValidationError($data, $expectedErrors);
    }
    public function test_login_fails_with_invalid_credentials()
    {
        $this->openLoginPage();

        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $data = [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->post(route('user.login'), $data);
        $response->assertStatus(302)
            ->assertRedirect(route('user.login'))
            ->assertSessionHas('auth_error', 'ログイン情報が登録されていません');

        // ユーザーが認証されていないことを確認
        $this->assertGuest();
    }

// 全ての項目が正しく入力されている場合、ログイン処理実行
    public function test_user_can_login_and_redirect_to_home()
    {
        // $user = TestHelper::userLogin();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->post(route('user.login'), $data);
        $response->assertSessionDoesntHaveErrors();
        $response->assertStatus(302);

        // 認証ユーザであることを確認
        $this->assertAuthenticatedAs($user);
    }
}
