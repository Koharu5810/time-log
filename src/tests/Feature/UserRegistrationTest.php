<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function openRegisterPage()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('登録する');
        $response->assertSee('<form', false);

        return $response;
    }
    private function getRegisterData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'TEST USER',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }
    private function assertValidationError(array $data, array $expectedErrors)
    {
        $response = $this->postJson('/register', $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors($expectedErrors);
    }

    public function test_username_validation_error_when_username_is_missing()
    {
        $this->openRegisterPage();

        $data = $this->getRegisterData(['name' => '']);
        $this->assertValidationError($data, ['name' => 'お名前を入力してください']);
    }
    public function test_email_validation_error_when_email_is_missing()
    {
        $this->openRegisterPage();

        $data = $this->getRegisterData(['email' => '']);
        $this->assertValidationError($data, ['email' => 'メールアドレスを入力してください']);
    }
    public function test_password_validation_error_when_password_is_missing()
    {
        $this->openRegisterPage();

        $data = $this->getRegisterData(['password' => '']);
        $this->assertValidationError($data, ['password' => 'パスワードを入力してください']);
    }
    public function test_password_validation_error_when_password_is_too_short()
    {
        $this->openRegisterPage();

        $data = $this->getRegisterData(['password' => 'short12']);
        $this->assertValidationError($data, ['password' => 'パスワードは8文字以上で入力してください']);
    }
    public function test_password_validation_error_when_password_confirmation_does_not_match()
    {
        $this->openRegisterPage();

        $data = $this->getRegisterData(['password' => 'password456']);
        $this->assertValidationError($data, ['password' => 'パスワードと一致しません']);
    }

// 全ての項目が入力されている場合、会員情報が登録され勤怠登録画面に遷移
    public function test_user_can_register_and_redirect_to_attendance_crate()
    {
        $this->openRegisterPage();

        $data = $this->getRegisterData();
        $response = $this->post(route('registration'), $data);

        // パスワードがハッシュ化されて保存されていることを確認
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->actingAs($user);

        $response->assertStatus(302);   // ステータスコード302を確認（リダイレクト）
        $response->assertRedirect(route('create'));  // 登録後に勤怠管理画面へリダイレクト確認

        // データベースにユーザーが作成されたことを確認
        $this->assertDatabaseHas('users', [
            'name' => 'TEST USER',
            'email' => 'test@example.com',
        ]);

        $this->assertTrue(Hash::check('password123', $user->password));
    }
}
