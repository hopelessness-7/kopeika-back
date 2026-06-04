<?php

namespace App\Application\Finance;

use App\Models\Obligation;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class ObligationSchedule
{
    public function nextPaymentOnOrAfter(Obligation $obligation, CarbonInterface $from): CarbonInterface
    {
        $from = $from->copy()->startOfDay();
        $candidate = $this->paymentDateInMonth($from, $obligation->payment_day);

        if ($candidate->lt($from)) {
            $nextMonth = $from->copy()->addMonthNoOverflow()->startOfMonth();

            return $this->paymentDateInMonth($nextMonth, $obligation->payment_day);
        }

        return $candidate;
    }

    public function totalDueUntil(Collection $obligations, CarbonInterface $until, CarbonInterface $from): string
    {
        $total = '0.00';
        $until = $until->copy()->endOfDay();

        foreach ($obligations as $obligation) {
            $paymentDate = $this->nextPaymentOnOrAfter($obligation, $from);

            if ($paymentDate->lte($until)) {
                $total = bcadd($total, (string) $obligation->payment_amount, 2);
            }
        }

        return $total;
    }

    public function findNext(Collection $obligations, CarbonInterface $from): ?array
    {
        $next = null;

        foreach ($obligations as $obligation) {
            $dueDate = $this->nextPaymentOnOrAfter($obligation, $from);

            if ($next === null || $dueDate->lt($next['due_date'])) {
                $next = [
                    'obligation' => $obligation,
                    'due_date' => $dueDate,
                    'amount' => (string) $obligation->payment_amount,
                ];
            }
        }

        return $next;
    }

    public function paymentsOnDate(Collection $obligations, CarbonInterface $date): array
    {
        $payments = [];
        $targetDay = min($date->day, $date->daysInMonth);

        foreach ($obligations as $obligation) {
            $paymentDay = min($obligation->payment_day, $date->daysInMonth);

            if ($paymentDay === $targetDay) {
                $payments[] = [
                    'obligation' => $obligation,
                    'amount' => (string) $obligation->payment_amount,
                ];
            }
        }

        return $payments;
    }

    private function paymentDateInMonth(CarbonInterface $month, int $paymentDay): CarbonInterface
    {
        $day = min($paymentDay, $month->daysInMonth);

        return $month->copy()->day($day)->startOfDay();
    }
}
