<?php

namespace App\Application\Finance;

use App\Application\Support\Money;
use App\Models\Income;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class IncomeAnchorBuilder
{
    public function __construct(
        private readonly AnchorDateCalculator $anchorDates,
        private readonly ObligationSchedule $obligationSchedule,
        private readonly SafeToSpendCalculator $safeToSpend,
    ) {}

    /**
     * @param  Collection<int, Income>  $anchorIncomes
     * @param  array<int, list<string>>  $paidDueDates
     * @return array{items: list<array<string, mixed>>, primary: ?array<string, mixed>}
     */
    public function build(
        Collection $anchorIncomes,
        $obligations,
        string $balance,
        CarbonInterface $today,
        array $paidDueDates,
        ?string $buffer = null,
    ): array {
        $items = [];

        foreach ($anchorIncomes as $income) {
            if ($income->day_of_month === null) {
                continue;
            }

            $anchorDate = $this->anchorDates->nextDayOfMonthDate($income->day_of_month, $today);
            $daysRemaining = $this->anchorDates->daysRemaining($anchorDate, $today);
            $obligationsUntil = $this->obligationSchedule->totalDueUntil(
                $obligations,
                $anchorDate,
                $today,
                $paidDueDates,
            );
            $limits = $this->safeToSpend->calculate($balance, $obligationsUntil, $daysRemaining, $buffer);

            $items[] = [
                'income_id' => $income->id,
                'title' => $income->title,
                'day_of_month' => $income->day_of_month,
                'next_date' => $anchorDate->toDateString(),
                'days_remaining' => $daysRemaining,
                'daily_limit' => Money::toApiNumber($limits['daily_limit']),
                'free_after_obligations' => Money::toApiNumber($limits['free']),
                '_free' => $limits['free'],
                '_daily_limit_raw' => $limits['daily_limit'],
            ];
        }

        $primary = $this->pickPrimary($items);

        return [
            'items' => array_map(function (array $item) {
                unset($item['_free'], $item['_daily_limit_raw']);

                return $item;
            }, $items),
            'primary' => $primary,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function pickPrimary(array $items): ?array
    {
        if ($items === []) {
            return null;
        }

        $primary = $items[0];

        foreach ($items as $item) {
            if (Money::compare((string) $item['_daily_limit_raw'], (string) $primary['_daily_limit_raw']) < 0) {
                $primary = $item;
            }
        }

        return $primary;
    }
}
