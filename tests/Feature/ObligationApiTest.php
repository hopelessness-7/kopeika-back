<?php

namespace Tests\Feature;

use App\Domain\Enums\ObligationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObligationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->authenticateDemoUser();
    }

    public function test_lists_obligations_for_demo_user(): void
    {
        $response = $this->getJson('/api/obligations');

        $response->assertOk()
            ->assertJsonIsArray()
            ->assertJsonStructure([
                ['id', 'title', 'type', 'payment_amount', 'payment_day'],
            ]);

        $this->assertGreaterThanOrEqual(3, count($response->json()));
    }

    public function test_creates_obligation_via_dto_request(): void
    {
        $response = $this->postJson('/api/obligations', [
            'title' => 'Тестовый платёж',
            'type' => ObligationType::Other->value,
            'payment_amount' => '1000.00',
            'payment_day' => 20,
        ]);

        $response->assertCreated()
            ->assertJsonPath('title', 'Тестовый платёж');

        $this->assertDatabaseHas('obligations', [
            'title' => 'Тестовый платёж',
            'payment_amount' => '1000.00',
        ]);
    }
}
