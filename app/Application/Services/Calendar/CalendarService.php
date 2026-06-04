<?php

namespace App\Application\Services\Calendar;

use App\Application\Auth\CurrentUserResolver;
use App\Application\Finance\ObligationSchedule;
use App\Application\Services\BaseService;
use App\Application\Support\Money;
use App\Domain\Contracts\Repositories\ObligationRepositoryInterface;
use Carbon\Carbon;

final class CalendarService extends BaseService
{
    public function __construct(
        CurrentUserResolver $currentUser,
        private readonly ObligationRepositoryInterface $obligations,
        private readonly ObligationSchedule $schedule,
    ) {
        parent::__construct($currentUser);
    }

    public function forRange(string $from, string $to, ?int $userId = null): array
    {
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();

        if ($end->lt($start)) {
            return ['days' => []];
        }

        $obligations = $this->obligations->listActiveForUser($userId ?? $this->currentUserId());
        $days = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $payments = $this->schedule->paymentsOnDate($obligations, $date);
            $items = [];
            $total = '0.00';

            foreach ($payments as $payment) {
                $amount = $payment['amount'];
                $total = bcadd($total, $amount, 2);
                $items[] = [
                    'id' => $payment['obligation']->id,
                    'title' => $payment['obligation']->title,
                    'amount' => Money::toApiNumber($amount),
                    'type' => $payment['obligation']->type->value,
                ];
            }

            if ($items !== []) {
                $days[] = [
                    'date' => $date->toDateString(),
                    'obligations' => $items,
                    'total' => Money::toApiNumber($total),
                ];
            }
        }

        return ['days' => $days];
    }
}
