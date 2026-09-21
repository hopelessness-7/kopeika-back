<?php

namespace App\Application\Services\Calendar;

use App\Application\Auth\CurrentUserResolver;
use App\Application\Finance\ObligationSchedule;
use App\Application\Services\BaseService;
use App\Application\Support\Money;
use App\Domain\Contracts\Repositories\IncomeRepositoryInterface;
use App\Domain\Contracts\Repositories\ObligationRepositoryInterface;
use App\Models\Income;
use Carbon\Carbon;

final class CalendarService extends BaseService
{
    public function __construct(
        CurrentUserResolver $currentUser,
        private readonly ObligationRepositoryInterface $obligations,
        private readonly IncomeRepositoryInterface $incomes,
        private readonly ObligationSchedule $schedule,
    ) {
        parent::__construct($currentUser);
    }

    public function forRange(string $from, string $to, ?int $userId = null): array
    {
        $userId ??= $this->currentUserId();
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();

        if ($end->lt($start)) {
            return ['days' => []];
        }

        $obligations = $this->obligations->listActiveForUser($userId);
        $oneOffIncomes = $this->incomes->listForUser($userId);
        $recurringIncomes = $this->incomes->listRecurringActiveForUser($userId);

        $daysByDate = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->toDateString();
            $obligationItems = [];
            $obligationTotal = '0.00';

            foreach ($this->schedule->paymentsOnDate($obligations, $date) as $payment) {
                $amount = $payment['amount'];
                $obligationTotal = bcadd($obligationTotal, $amount, 2);
                $obligationItems[] = [
                    'id' => $payment['obligation']->id,
                    'title' => $payment['obligation']->title,
                    'amount' => Money::toApiNumber($amount),
                    'type' => $payment['obligation']->type->value,
                ];
            }

            $incomeItems = [];
            $incomeTotal = '0.00';

            foreach ($recurringIncomes as $income) {
                if ($income->day_of_month === $date->day) {
                    $amount = (string) $income->amount;
                    $incomeTotal = bcadd($incomeTotal, $amount, 2);
                    $incomeItems[] = $this->incomeEvent($income, $amount, true);
                }
            }

            foreach ($oneOffIncomes as $income) {
                if (! $income->is_recurring && $income->received_at->toDateString() === $key) {
                    $amount = (string) $income->amount;
                    $incomeTotal = bcadd($incomeTotal, $amount, 2);
                    $incomeItems[] = $this->incomeEvent($income, $amount, false);
                }
            }

            if ($obligationItems !== [] || $incomeItems !== []) {
                $daysByDate[$key] = [
                    'date' => $key,
                    'obligations' => $obligationItems,
                    'incomes' => $incomeItems,
                    'obligation_total' => Money::toApiNumber($obligationTotal),
                    'income_total' => Money::toApiNumber($incomeTotal),
                    'total' => Money::toApiNumber($obligationTotal),
                ];
            }
        }

        ksort($daysByDate);

        return ['days' => array_values($daysByDate)];
    }

    private function incomeEvent(Income $income, string $amount, bool $recurring): array
    {
        return [
            'id' => $income->id,
            'title' => $income->title,
            'amount' => Money::toApiNumber($amount),
            'recurring' => $recurring,
        ];
    }
}
