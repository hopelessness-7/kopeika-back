<?php

namespace App\Application\Finance;

use App\Application\Support\Money;
use App\Models\Income;
use App\Models\Obligation;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class CashflowForecast
{
    public function __construct(
        private readonly ObligationSchedule $schedule,
    ) {}

    /**
     * Возвращает список ожидаемых событий (доход / платёж по долгу) от $from до $until,
     * с накопительным балансом после каждого события.
     *
     * @param  Collection<int, Income>  $oneOffIncomes
     * @param  Collection<int, Income>  $recurringIncomes
     * @param  Collection<int, Obligation>  $obligations
     * @param  array<int, list<string>>  $paidDueDatesByObligation
     * @return list<array<string, mixed>>
     */
    public function project(
        string $balance,
        Collection $oneOffIncomes,
        Collection $recurringIncomes,
        Collection $obligations,
        CarbonInterface $from,
        CarbonInterface $until,
        array $paidDueDatesByObligation = [],
    ): array {
        $events = [];

        $from = $from->copy()->startOfDay();
        $until = $until->copy()->endOfDay();

        foreach ($oneOffIncomes as $income) {
            if ($income->is_recurring) {
                continue;
            }
            $date = $income->received_at?->copy()->startOfDay();
            if ($date === null || $date->lt($from) || $date->gt($until)) {
                continue;
            }

            $events[] = [
                'date' => $date,
                'kind' => 'income',
                'recurring' => false,
                'title' => $income->title,
                'amount' => (string) $income->amount,
                'reference_id' => $income->id,
            ];
        }

        foreach ($recurringIncomes as $income) {
            if ($income->day_of_month === null) {
                continue;
            }

            foreach ($this->datesForDayOfMonth($income->day_of_month, $from, $until) as $date) {
                $events[] = [
                    'date' => $date,
                    'kind' => 'income',
                    'recurring' => true,
                    'title' => $income->title,
                    'amount' => (string) $income->amount,
                    'reference_id' => $income->id,
                ];
            }
        }

        foreach ($obligations as $obligation) {
            $paid = $paidDueDatesByObligation[$obligation->id] ?? [];
            $cursor = $from->copy();
            while ($cursor->lte($until)) {
                $payment = $this->schedule->nextUnpaidPaymentOnOrAfter($obligation, $cursor, $paid);
                if ($payment === null || $payment->gt($until)) {
                    break;
                }

                $events[] = [
                    'date' => $payment,
                    'kind' => 'obligation',
                    'recurring' => true,
                    'title' => $obligation->title,
                    'type' => $obligation->type->value,
                    'amount' => (string) $obligation->payment_amount,
                    'reference_id' => $obligation->id,
                ];

                $cursor = $payment->copy()->addDay();
            }
        }

        usort($events, function (array $a, array $b) {
            $cmp = $a['date']->timestamp <=> $b['date']->timestamp;
            if ($cmp !== 0) {
                return $cmp;
            }

            return $a['kind'] === 'income' ? -1 : 1;
        });

        $running = Money::normalize($balance);
        $projection = [];

        foreach ($events as $event) {
            $signed = $event['kind'] === 'income'
                ? $event['amount']
                : '-'.ltrim($event['amount'], '-');

            $running = Money::add($running, $signed);

            $projection[] = [
                'date' => $event['date']->toDateString(),
                'kind' => $event['kind'],
                'recurring' => $event['recurring'] ?? false,
                'title' => $event['title'],
                'type' => $event['type'] ?? null,
                'amount' => Money::toApiNumber($event['amount']),
                'signed_amount' => Money::toApiNumber($signed),
                'reference_id' => $event['reference_id'] ?? null,
                'running_balance' => Money::toApiNumber($running),
            ];
        }

        return $projection;
    }

    /**
     * @param  list<array<string, mixed>>  $projection
     */
    public function summarizeNextObligation(array $projection, string $balance): ?array
    {
        $balanceRunning = Money::normalize($balance);
        $expectedIncome = '0.00';
        $expectedIncomeEvents = [];

        foreach ($projection as $event) {
            if ($event['kind'] === 'income') {
                $balanceRunning = Money::add($balanceRunning, (string) $event['amount']);
                $expectedIncome = Money::add($expectedIncome, (string) $event['amount']);
                $expectedIncomeEvents[] = $event;

                continue;
            }

            $expected = $balanceRunning;
            $covers = Money::compare($expected, (string) $event['amount']) >= 0;
            $shortfall = $covers
                ? null
                : Money::toApiNumber(Money::sub((string) $event['amount'], $expected));
            $surplus = $covers
                ? Money::toApiNumber(Money::sub($expected, (string) $event['amount']))
                : null;

            return [
                'obligation_id' => $event['reference_id'],
                'title' => $event['title'],
                'due_date' => $event['date'],
                'amount' => $event['amount'],
                'expected_balance_at_due' => Money::toApiNumber($expected),
                'expected_incomes_total' => Money::toApiNumber($expectedIncome),
                'expected_incomes' => array_map(
                    fn (array $row) => [
                        'date' => $row['date'],
                        'title' => $row['title'],
                        'amount' => $row['amount'],
                        'recurring' => $row['recurring'],
                    ],
                    $expectedIncomeEvents,
                ),
                'covers' => $covers,
                'shortfall' => $shortfall,
                'surplus_after' => $surplus,
            ];
        }

        return null;
    }

    /**
     * @return list<CarbonInterface>
     */
    private function datesForDayOfMonth(int $dayOfMonth, CarbonInterface $from, CarbonInterface $until): array
    {
        $result = [];
        $cursor = $from->copy()->startOfMonth();

        while ($cursor->lte($until)) {
            $day = min($dayOfMonth, $cursor->daysInMonth);
            $candidate = $cursor->copy()->day($day)->startOfDay();

            if ($candidate->between($from, $until)) {
                $result[] = $candidate;
            }

            $cursor = $cursor->copy()->addMonthNoOverflow()->startOfMonth();
        }

        return $result;
    }
}
