<?php

namespace App\Application\Services\Auth;

use App\Domain\Contracts\Repositories\UserRepositoryInterface;
use App\Domain\Contracts\Repositories\UserSettingsRepositoryInterface;
use App\DTO\Auth\RegisterData;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly UserSettingsRepositoryInterface $settings,
    ) {}

    public function register(RegisterData $data): array
    {
        if ($this->users->findByEmail($data->email) !== null) {
            throw ValidationException::withMessages([
                'email' => ['Пользователь с таким email уже зарегистрирован.'],
            ]);
        }

        $user = User::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
        ]);

        $this->settings->findOrCreateForUser($user->id);

        Auth::login($user);
        $this->regenerateSession();

        return ['user' => $this->userPayload($user)];
    }

    public function login(array $credentials): array
    {
        $remember = (bool) ($credentials['remember'] ?? false);

        if (! Auth::attempt([
            'email' => strtolower($credentials['email']),
            'password' => $credentials['password'],
        ], $remember)) {
            throw ValidationException::withMessages([
                'email' => ['Неверный email или пароль.'],
            ]);
        }

        $this->regenerateSession();

        return ['user' => $this->userPayload(Auth::user())];
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();

        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }
    }

    public function sendPasswordResetLink(string $email): void
    {
        Password::sendResetLink(['email' => strtolower($email)]);
    }

    public function resetPassword(array $payload): void
    {
        $status = Password::reset(
            [
                'email' => strtolower($payload['email']),
                'password' => $payload['password'],
                'password_confirmation' => $payload['password_confirmation'] ?? $payload['password'],
                'token' => $payload['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    private function regenerateSession(): void
    {
        if (request()->hasSession()) {
            request()->session()->regenerate();
        }
    }

    public function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
        ];
    }
}
