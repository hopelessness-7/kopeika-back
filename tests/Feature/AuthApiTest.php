<?php

namespace Tests\Feature;

use App\Domain\DemoUser;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_and_session(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Новый пользователь',
            'email' => 'new@kopeika.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('email', 'new@kopeika.local');

        $this->assertDatabaseHas('users', ['email' => 'new@kopeika.local']);
        $this->getJson('/api/user')->assertOk()
            ->assertJsonPath('email', 'new@kopeika.local');
    }

    public function test_login_returns_authenticated_user(): void
    {
        $this->seed();

        $this->postJson('/api/login', [
            'email' => DemoUser::EMAIL,
            'password' => DemoUser::PASSWORD,
        ])->assertOk()
            ->assertJsonPath('email', DemoUser::EMAIL);

        $this->getJson('/api/user')->assertOk();
    }

    public function test_logout_after_login(): void
    {
        $this->seed();

        $this->postJson('/api/login', [
            'email' => DemoUser::EMAIL,
            'password' => DemoUser::PASSWORD,
        ])->assertOk();

        $this->postJson('/api/logout')->assertNoContent();
    }

    public function test_protected_routes_require_auth(): void
    {
        $this->getJson('/api/dashboard')->assertUnauthorized();
    }

    public function test_forgot_password_sends_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'reset@kopeika.local',
        ]);

        $this->postJson('/api/forgot-password', [
            'email' => $user->email,
        ])->assertOk();

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_password_updates_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@kopeika.local',
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::createToken($user);

        $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'new-password-99',
            'password_confirmation' => 'new-password-99',
        ])->assertOk();

        $user->refresh();

        $this->assertTrue(Hash::check('new-password-99', $user->password));
    }
}
