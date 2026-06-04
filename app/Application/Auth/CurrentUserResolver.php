<?php

namespace App\Application\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class CurrentUserResolver
{
    public function user(): User
    {
        $user = Auth::user();

        if ($user instanceof User) {
            return $user;
        }

        throw new UnauthorizedHttpException('Session', 'Требуется авторизация.');
    }

    public function id(): int
    {
        return $this->user()->id;
    }
}
