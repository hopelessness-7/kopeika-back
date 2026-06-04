<?php

namespace App\Application\Finance;

use App\Application\Support\Money;
use App\Domain\Enums\FinanceZone;
use Carbon\CarbonInterface;

final class ZoneResolver
{
    public function resolve(
        string $freeAfterObligations,
        string $balance,
        ?string $nextPaymentAmount,
        ?CarbonInterface $nextPaymentDate,
        CarbonInterface $today,
    ): FinanceZone {
        if (Money::isNegative($freeAfterObligations)) {
            return FinanceZone::Red;
        }

        if ($nextPaymentAmount !== null && $nextPaymentDate !== null) {
            $daysUntil = (int) $today->diffInDays($nextPaymentDate, false);

            if ($daysUntil >= 0 && $daysUntil <= 3 && Money::compare($balance, $nextPaymentAmount) < 0) {
                return FinanceZone::Red;
            }
        }

        if (Money::compare($freeAfterObligations, '3000') < 0) {
            return FinanceZone::Yellow;
        }

        if ($nextPaymentDate !== null) {
            $daysUntil = (int) $today->diffInDays($nextPaymentDate, false);

            if ($daysUntil >= 0 && $daysUntil <= 5) {
                return FinanceZone::Yellow;
            }
        }

        return FinanceZone::Green;
    }
}
