<?php

namespace App\Application\Finance;

use App\Application\Support\Money;

final class SafeToSpendCalculator
{
    /** free = balance − obligations_until(anchor) */
    public function calculate(string $balance, string $obligationsUntil, int $daysRemaining): array
    {
        $free = Money::sub($balance, $obligationsUntil);
        $dailyLimit = bcdiv($free, (string) $daysRemaining, 2);

        return [
            'free' => $free,
            'daily_limit' => $dailyLimit,
        ];
    }
}
