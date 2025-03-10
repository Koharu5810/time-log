<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected array $adminData;

    protected function setUp(): void
    {
        parent::setUp();

        // 共通の管理者データを用意
        $this->adminData = [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ];
    }

    // 管理者登録を実行する共通メソッド
    protected function registerAdmin(array $overrideData = []): \Illuminate\Testing\TestResponse
    {
        // オーバーライドデータがあればマージ
        $data = array_merge($this->adminData, $overrideData);

        return $this->post('/admin/register', $data);
    }

    private function openLoginPage()
    {
        return $this->get('/admin/login')
            ->assertStatus(200)
            ->assertSee('管理者ログインする')
            ->assertSee('<form', false);
    }
    private function assertValidationError($data, $expectedErrors)
    {
        $response = $this->post(route('admin.login'), $data);
        $response->assertStatus(302);
        $response->assertSessionHasErrors($expectedErrors);
    }

// メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_email_validation_error_when_email_is_missing()
    {
        $this->openLoginPage();

        Admin::create([
            'name' => 'admin1',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $data = [
            'email' => '',
            'password' => 'password123',
        ];
        $expectedErrors = ['email' => 'メールアドレスを入力してください'];

        $this->assertValidationError($data, $expectedErrors);
    }
// パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_password_validation_error_when_password_is_missing()
    {
        $this->openLoginPage();

        Admin::create([
            'name' => 'admin1',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $data = [
            'email' => 'admin@example.com',
            'password' => '',
        ];
        $expectedErrors = ['password' => 'パスワードを入力してください'];

        $this->assertValidationError($data, $expectedErrors);
    }
    public function test_login_fails_with_invalid_credentials()
    {
        $this->openLoginPage();

        Admin::create([
            'name' => 'admin1',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $data = [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ];

        $response = $this->post(route('admin.login'), $data);
        $response->assertStatus(302)
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('admin_error', 'ログイン情報が登録されていません');

        // ユーザーが認証されていないことを確認
        $this->assertGuest();
    }
}
