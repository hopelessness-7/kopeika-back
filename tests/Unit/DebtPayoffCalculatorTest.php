<?php

namespace Tests\Unit;

use App\Application\Finance\DebtPayoffCalculator;
use PHPUnit\Framework\TestCase;

class DebtPayoffCalculatorTest extends TestCase
{
    private DebtPayoffCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new DebtPayoffCalculator;
    }

    public function test_linear_payoff_without_interest(): void
    {
        $estimate = $this->calculator->calculate('10000.00', '2500.00', null);

        $this->assertFalse($estimate->neverCloses);
        $this->assertSame(4, $estimate->months);
        $this->assertSame('10000.00', $estimate->totalToPay);
        $this->assertSame('0.00', $estimate->totalInterest);
    }

    public function test_interest_increases_months_and_total(): void
    {
        $estimate = $this->calculator->calculate('100000.00', '10000.00', 12.0);

        $this->assertFalse($estimate->neverCloses);
        $this->assertGreaterThan(10, $estimate->months);
        $this->assertGreaterThan(100000.0, (float) $estimate->totalToPay);
        $this->assertGreaterThan(0.0, (float) $estimate->totalInterest);
    }

    public function test_payment_below_interest_never_closes(): void
    {
        $estimate = $this->calculator->calculate('1000000.00', '5000.00', 12.0);

        $this->assertTrue($estimate->neverCloses);
        $this->assertNull($estimate->months);
        $this->assertNotNull($estimate->minPayment);
        $this->assertGreaterThan(5000.0, (float) $estimate->minPayment);
    }

    public function test_zero_remaining_is_immediate(): void
    {
        $estimate = $this->calculator->calculate('0.00', '10000.00', 12.0);

        $this->assertFalse($estimate->neverCloses);
        $this->assertSame(0, $estimate->months);
    }
}
