<?php

namespace Tests\Feature;

use App\Domain\Enums\ObligationType;
use App\Models\Obligation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObligationPaymentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->authenticateDemoUser();
    }

    public function test_lists_payments_for_obligation(): void
    {
        $obligation = Obligation::query()
            ->where('type', ObligationType::Loan)
            ->firstOrFail();

        $response = $this->getJson("/api/obligations/{$obligation->id}/payments");

        $response->assertOk()
            ->assertJsonIsArray()
            ->assertJsonStructure([
                ['id', 'obligation_id', 'amount', 'status', 'paid_at'],
            ]);

        $this->assertGreaterThanOrEqual(2, count($response->json()));
    }

    public function test_records_payment_and_decreases_remaining_amount(): void
    {
        $obligation = Obligation::query()
            ->where('type', ObligationType::Loan)
            ->firstOrFail();

        $remainingBefore = (float) $obligation->remaining_amount;

        $response = $this->postJson("/api/obligations/{$obligation->id}/payments", [
            'amount' => 24100,
            'paid_at' => now()->toDateString(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'paid');

        $obligation->refresh();
        $this->assertEqualsWithDelta($remainingBefore - 24100, (float) $obligation->remaining_amount, 0.01);
    }

    public function test_close_endpoint_marks_obligation_inactive(): void
    {
        $obligation = Obligation::query()
            ->where('type', ObligationType::PersonalDebt)
            ->firstOrFail();

        $response = $this->postJson("/api/obligations/{$obligation->id}/close");

        $response->assertOk()->assertJsonPath('is_active', false);

        $obligation->refresh();
        $this->assertFalse((bool) $obligation->is_active);
        $this->assertEquals('0.00', $obligation->remaining_amount);
    }

    public function test_payment_does_not_auto_deactivate_obligation(): void
    {
        $obligation = Obligation::query()
            ->where('type', ObligationType::PersonalDebt)
            ->firstOrFail();

        $obligation->remaining_amount = '5000.00';
        $obligation->is_active = true;
        $obligation->save();

        $this->postJson("/api/obligations/{$obligation->id}/payments", [
            'amount' => 5000,
            'paid_at' => now()->toDateString(),
            'due_date' => now()->toDateString(),
        ])->assertCreated();

        $obligation->refresh();
        $this->assertTrue((bool) $obligation->is_active);
        $this->assertEquals('0.00', $obligation->remaining_amount);
    }

    public function test_obligation_progress_uses_remaining_balance(): void
    {
        $obligation = Obligation::query()
            ->where('type', ObligationType::Loan)
            ->firstOrFail();

        $obligation->total_amount = '100000.00';
        $obligation->remaining_amount = '53638.00';
        $obligation->save();

        $response = $this->getJson("/api/obligations/{$obligation->id}?with_summary=1");

        $response->assertOk();
        $this->assertEqualsWithDelta(46.4, $response->json('progress_percent'), 0.1);
        $this->assertEqualsWithDelta(46.4, $response->json('summary.progress_percent'), 0.1);
        $this->assertSame('balance', $response->json('summary.progress_basis'));
    }

    public function test_obligation_show_returns_summary_when_requested(): void
    {
        $obligation = Obligation::query()
            ->where('type', ObligationType::Loan)
            ->firstOrFail();

        $response = $this->getJson("/api/obligations/{$obligation->id}?with_summary=1");

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'summary' => ['total_paid', 'paid_this_month', 'paid_this_year', 'payments_count'],
            ]);

        $this->assertGreaterThan(0, $response->json('summary.total_paid'));
    }
}
