<?php

namespace Tests\Unit\Auth;

use App\Application\Services\Auth\AuthService;
use App\DTO\Auth\RegisterData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_persists_user(): void
    {
        $service = app(AuthService::class);

        $result = $service->register(new RegisterData(
            name: 'Test User',
            email: 'unit@kopeika.local',
            password: 'password123',
        ));

        $this->assertSame('unit@kopeika.local', $result['user']['email']);
        $this->assertDatabaseHas('users', ['email' => 'unit@kopeika.local']);
    }
}
