<?php

namespace App\Application\Finance;

use App\Domain\Enums\ImportIntervalDays;
use Carbon\CarbonInterface;

final class AnchorDateCalculator
{
    public function nextSalaryDate(int $salaryDayOfMonth, CarbonInterface $from): CarbonInterface
    {
        $from = $from->copy()->startOfDay();
        $candidate = $this->dayInMonth($from, $salaryDayOfMonth);

        if ($candidate->lt($from)) {
            $nextMonth = $from->copy()->addMonthNoOverflow()->startOfMonth();

            return $this->dayInMonth($nextMonth, $salaryDayOfMonth);
        }

        return $candidate;
    }

    public function nextImportDue(
        ?CarbonInterface $lastImportAt,
        ImportIntervalDays $interval,
        CarbonInterface $from,
    ): CarbonInterface {
        $from = $from->copy()->startOfDay();

        if ($lastImportAt === null) {
            return $from->copy()->addDays($interval->value);
        }

        return $lastImportAt->copy()->startOfDay()->addDays($interval->value);
    }

    public function daysRemaining(CarbonInterface $anchorDate, CarbonInterface $from): int
    {
        $days = $from->copy()->startOfDay()->diffInDays($anchorDate->copy()->startOfDay(), false);

        return max(1, (int) $days);
    }

    private function dayInMonth(CarbonInterface $month, int $dayOfMonth): CarbonInterface
    {
        $day = min($dayOfMonth, $month->daysInMonth);

        return $month->copy()->day($day)->startOfDay();
    }
}
