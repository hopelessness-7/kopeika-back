<?php

namespace Tests\Feature;

use App\Domain\Enums\ObligationType;
use App\Models\Obligation;
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
                'forecast' => [
                    'horizon_until',
                    'next_income',
                    'next_obligation_coverage',
                    'debt_payoff',
                    'timeline',
                ],
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

    public function test_dashboard_skips_paid_obligation_due_dates(): void
    {
        $rent = Obligation::query()
            ->where('type', ObligationType::Rent)
            ->firstOrFail();

        $schedule = app(\App\Application\Finance\ObligationSchedule::class);
        $today = now()->startOfDay();
        $nextDue = $schedule->nextPaymentOnOrAfter($rent, $today);

        $this->postJson("/api/obligations/{$rent->id}/payments", [
            'amount' => (float) $rent->payment_amount,
            'paid_at' => $nextDue->toDateString(),
            'due_date' => $nextDue->toDateString(),
        ])->assertCreated();

        $response = $this->getJson('/api/dashboard');
        $response->assertOk();

        $next = $response->json('next_obligation');
        $this->assertNotNull($next);
        $this->assertNotSame($nextDue->toDateString(), $next['due_date']);
        $this->assertTrue($rent->fresh()->is_active);
    }

    public function test_dashboard_forecast_includes_recurring_income_and_debt_payoff(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        $forecast = $response->json('forecast');

        $this->assertNotNull($forecast);
        $this->assertNotNull($forecast['next_income']);
        $this->assertGreaterThan(0, count($forecast['debt_payoff']));
        $this->assertGreaterThan(0, count($forecast['timeline']));

        $mortgage = collect($forecast['debt_payoff'])->firstWhere('title', 'Ипотека');
        $this->assertNotNull($mortgage);
        $this->assertFalse($mortgage['never_closes']);
        $this->assertSame(12.0, $mortgage['interest_rate']);
        $this->assertGreaterThan($mortgage['remaining'], $mortgage['total_to_pay']);
        $this->assertGreaterThan(0, $mortgage['total_interest']);
        $this->assertGreaterThan(
            (int) ceil($mortgage['remaining'] / $mortgage['payment']),
            $mortgage['months_to_close'],
        );
    }
}
