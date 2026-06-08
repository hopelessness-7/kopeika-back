<?php

namespace Tests\Unit;

use App\Application\Finance\ObligationProgress;
use PHPUnit\Framework\TestCase;

class ObligationProgressTest extends TestCase
{
    public function test_progress_from_balance(): void
    {
        $result = ObligationProgress::calculate('100000.00', '53638.00');

        $this->assertNotNull($result);
        $this->assertSame('balance', $result['basis']);
        $this->assertEqualsWithDelta(46.4, $result['progress_percent'], 0.1);
        $this->assertEqualsWithDelta(46362.0, (float) $result['paid_amount'], 0.01);
    }

    public function test_progress_falls_back_to_payments(): void
    {
        $result = ObligationProgress::calculate('100000.00', null, '25000.00');

        $this->assertNotNull($result);
        $this->assertSame('payments', $result['basis']);
        $this->assertSame(25.0, $result['progress_percent']);
    }
}
