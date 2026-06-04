<?php

namespace Tests;

use App\Domain\DemoUser;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    protected function authenticateDemoUser(): User
    {
        $user = User::query()->where('email', DemoUser::EMAIL)->firstOrFail();
        $this->actingAs($user);

        return $user;
    }
}
