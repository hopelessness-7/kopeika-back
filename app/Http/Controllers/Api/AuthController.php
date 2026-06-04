<?php

namespace App\Http\Controllers\Api;

use App\Application\Services\Auth\AuthService;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class AuthController extends BaseApiController
{
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        return $this->created($this->auth->register($request->getDto())['user']);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return $this->success($this->auth->login($request->validated())['user']);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout();

        return $this->noContent();
    }

    public function user(Request $request): JsonResponse
    {
        return $this->success($this->auth->userPayload(Auth::user()));
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->auth->sendPasswordResetLink($request->validated('email'));

        return $this->success([
            'message' => 'Если email зарегистрирован, мы отправили ссылку для сброса пароля.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->auth->resetPassword($request->safe()->only([
            'email',
            'token',
            'password',
            'password_confirmation',
        ]));

        return $this->success([
            'message' => 'Пароль обновлён. Можно войти с новым паролем.',
        ]);
    }
}
