<?php

namespace App\Application\Finance;

use App\Models\Obligation;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class ObligationSchedule
{
    private const MAX_UNPAID_SCAN_MONTHS = 36;

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

    /**
     * @param  list<string>  $paidDueDates  YYYY-MM-DD
     */
    public function nextUnpaidPaymentOnOrAfter(
        Obligation $obligation,
        CarbonInterface $from,
        array $paidDueDates = [],
    ): ?CarbonInterface {
        $cursor = $from->copy()->startOfDay();

        for ($i = 0; $i < self::MAX_UNPAID_SCAN_MONTHS; $i++) {
            $candidate = $this->nextPaymentOnOrAfter($obligation, $cursor);

            if (! in_array($candidate->toDateString(), $paidDueDates, true)) {
                return $candidate;
            }

            $cursor = $candidate->copy()->addDay();
        }

        return null;
    }

    /**
     * @param  array<int, list<string>>  $paidDueDatesByObligation
     */
    public function totalDueUntil(
        Collection $obligations,
        CarbonInterface $until,
        CarbonInterface $from,
        array $paidDueDatesByObligation = [],
    ): string {
        $total = '0.00';
        $until = $until->copy()->endOfDay();

        foreach ($obligations as $obligation) {
            $paid = $paidDueDatesByObligation[$obligation->id] ?? [];
            $paymentDate = $this->nextUnpaidPaymentOnOrAfter($obligation, $from, $paid);

            if ($paymentDate !== null && $paymentDate->lte($until)) {
                $total = bcadd($total, (string) $obligation->payment_amount, 2);
            }
        }

        return $total;
    }

    public function findNext(Collection $obligations, CarbonInterface $from): ?array
    {
        return $this->findNextUnpaid($obligations, $from, []);
    }

    /**
     * @param  array<int, list<string>>  $paidDueDatesByObligation
     */
    public function findNextUnpaid(
        Collection $obligations,
        CarbonInterface $from,
        array $paidDueDatesByObligation = [],
    ): ?array {
        $next = null;

        foreach ($obligations as $obligation) {
            $paid = $paidDueDatesByObligation[$obligation->id] ?? [];
            $dueDate = $this->nextUnpaidPaymentOnOrAfter($obligation, $from, $paid);

            if ($dueDate === null) {
                continue;
            }

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
