<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->authenticateDemoUser();
    }

    public function test_dashboard_returns_core_fields(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'balance',
                'balance_updated_at',
                'incomes' => [
                    'summary' => [
                        'total_this_month',
                        'count_this_month',
                        'total_all_time',
                        'last_received_at',
                    ],
                    'recent',
                ],
                'savings' => [
                    'summary' => [
                        'total_balance',
                        'total_monthly_contribution',
                        'accounts_count',
                    ],
                    'accounts',
                ],
                'zone',
                'free_after_obligations',
                'anchors' => [
                    'primary',
                    'salary',
                    'import',
                ],
                'primary_daily_limit',
                'obligations_until_salary_total',
                'check_in_due',
                'import_due',
                'import_overdue',
                'streak',
            ]);

        $this->assertContains($response->json('zone'), ['green', 'yellow', 'red']);
        $this->assertGreaterThan(0, $response->json('balance'));
        $this->assertGreaterThan(0, $response->json('incomes.summary.total_all_time'));
        $this->assertGreaterThan(0, $response->json('savings.summary.total_balance'));
        $this->assertNotEmpty($response->json('incomes.recent'));
        $this->assertNotEmpty($response->json('savings.accounts'));
    }
}
