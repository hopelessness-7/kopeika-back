<?php

namespace Tests\Unit\Finance;

use App\Application\Finance\SafeToSpendCalculator;
use PHPUnit\Framework\TestCase;

class SafeToSpendCalculatorTest extends TestCase
{
    public function test_calculates_free_and_daily_limit(): void
    {
        $calculator = new SafeToSpendCalculator;

        $result = $calculator->calculate('21800.00', '9400.00', 4);

        $this->assertSame('12400.00', $result['free']);
        $this->assertSame('3100.00', $result['daily_limit']);
    }
}
