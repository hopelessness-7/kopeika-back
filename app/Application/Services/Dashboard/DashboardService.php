<?php

namespace App\Application\Services\Dashboard;

use App\Application\Auth\CurrentUserResolver;
use App\Application\Finance\CashflowForecast;
use App\Application\Finance\DebtPayoffCalculator;
use App\Application\Finance\GoalSavingsPlanner;
use App\Application\Finance\IncomeAnchorBuilder;
use App\Application\Finance\ObligationProgress;
use App\Application\Finance\ObligationSchedule;
use App\Application\Finance\ZoneResolver;
use App\Application\Services\BaseService;
use App\Application\Support\Money;
use App\Domain\Contracts\Repositories\BalanceSnapshotRepositoryInterface;
use App\Domain\Contracts\Repositories\GoalRepositoryInterface;
use App\Domain\Contracts\Repositories\IncomeRepositoryInterface;
use App\Domain\Contracts\Repositories\ObligationPaymentRepositoryInterface;
use App\Domain\Contracts\Repositories\ObligationRepositoryInterface;
use App\Domain\Contracts\Repositories\SavingRepositoryInterface;
use App\Domain\Contracts\Repositories\UserSettingsRepositoryInterface;
use App\Models\Goal;
use App\Models\Income;
use App\Models\Saving;
use App\Models\UserSetting;
use Carbon\CarbonInterface;

final class DashboardService extends BaseService
{
    public function __construct(
        CurrentUserResolver $currentUser,
        private readonly UserSettingsRepositoryInterface $settings,
        private readonly BalanceSnapshotRepositoryInterface $balances,
        private readonly ObligationRepositoryInterface $obligations,
        private readonly ObligationPaymentRepositoryInterface $obligationPayments,
        private readonly IncomeRepositoryInterface $incomes,
        private readonly SavingRepositoryInterface $savings,
        private readonly GoalRepositoryInterface $goals,
        private readonly ObligationSchedule $obligationSchedule,
        private readonly IncomeAnchorBuilder $incomeAnchors,
        private readonly GoalSavingsPlanner $goalPlanner,
        private readonly ZoneResolver $zoneResolver,
        private readonly CashflowForecast $forecast,
        private readonly DebtPayoffCalculator $debtPayoff,
    ) {
        parent::__construct($currentUser);
    }

    public function build(?int $userId = null): array
    {
        $userId ??= $this->currentUserId();
        $today = now()->startOfDay();
        $userSettings = $this->settings->findOrCreateForUser($userId);
        $balanceSnapshot = $this->balances->latestForUser($userId);
        $balance = $balanceSnapshot !== null
            ? Money::normalize((string) $balanceSnapshot->amount)
            : '0.00';
        $obligations = $this->obligations->listActiveForUser($userId);
        $paidDueDates = $this->obligationPayments->paidDueDateKeysByObligationForUser($userId);

        $buffer = $userSettings->buffer_amount !== null
            ? Money::normalize((string) $userSettings->buffer_amount)
            : null;

        $anchorIncomes = $this->incomes->listSpendingAnchorsForUser($userId);
        $anchorResult = $this->incomeAnchors->build(
            $anchorIncomes,
            $obligations,
            $balance,
            $today,
            $paidDueDates,
            $buffer,
        );

        $primaryAnchor = $anchorResult['primary'];
        $primaryDailyLimit = $primaryAnchor !== null
            ? ($primaryAnchor['daily_limit'] ?? 0)
            : 0;

        $freeAfterObligations = $primaryAnchor !== null && isset($primaryAnchor['_free'])
            ? $primaryAnchor['_free']
            : $balance;

        $nextPayment = $this->obligationSchedule->findNextUnpaid($obligations, $today, $paidDueDates);
        $nextObligation = $this->formatNextObligation($nextPayment, $balance, $today, $paidDueDates);

        $zone = $this->zoneResolver->resolve(
            $freeAfterObligations,
            $balance,
            $nextPayment['amount'] ?? null,
            $nextPayment['due_date'] ?? null,
            $today,
        );

        $primaryHorizon = $primaryAnchor !== null
            ? ['next_date' => $primaryAnchor['next_date']]
            : null;

        $forecast = $this->buildForecast(
            $userId,
            $obligations,
            $balance,
            $today,
            $primaryHorizon,
            $paidDueDates,
        );

        $obligationsUntilPrimary = '0.00';
        if ($primaryAnchor !== null) {
            $anchorDate = \Carbon\Carbon::parse($primaryAnchor['next_date']);
            $obligationsUntilPrimary = $this->obligationSchedule->totalDueUntil(
                $obligations,
                $anchorDate,
                $today,
                $paidDueDates,
            );
        }

        return [
            'balance' => Money::toApiNumber($balance),
            'balance_updated_at' => $balanceSnapshot?->recorded_at?->toIso8601String(),
            'incomes' => $this->buildIncomesSection($userId, $today),
            'savings' => $this->buildSavingsSection($userId),
            'zone' => $zone->value,
            'free_after_obligations' => Money::toApiNumber($freeAfterObligations),
            'anchors' => [
                'primary_income_id' => $primaryAnchor['income_id'] ?? null,
                'items' => $anchorResult['items'],
            ],
            'primary_daily_limit' => $primaryDailyLimit,
            'next_obligation' => $nextObligation,
            'obligations_until_primary_anchor_total' => Money::toApiNumber($obligationsUntilPrimary),
            'forecast' => $forecast,
            'goals' => $this->buildGoalsSection(
                $userId,
                $freeAfterObligations,
                $primaryAnchor['days_remaining'] ?? null,
            ),
            'check_in_due' => $this->isCheckInDue($userSettings, $today),
            'streak' => [
                'check_in_weeks' => (int) ($userSettings->check_in_streak_weeks ?? 0),
            ],
            'notification_mode' => $userSettings->notification_mode->value,
        ];
    }

    private function buildForecast(
        int $userId,
        $obligations,
        string $balance,
        CarbonInterface $today,
        ?array $primaryHorizon,
        array $paidDueDates,
    ): array {
        $oneOff = $this->incomes->listForUser($userId);
        $recurring = $this->incomes->listRecurringActiveForUser($userId);

        $horizonEnd = $today->copy()->addDays(60);
        if ($primaryHorizon !== null) {
            $anchorDate = \Carbon\Carbon::parse($primaryHorizon['next_date']);
            $horizonEnd = $anchorDate->gt($horizonEnd) ? $anchorDate : $horizonEnd;
        }

        $projection = $this->forecast->project(
            $balance,
            $oneOff,
            $recurring,
            $obligations,
            $today,
            $horizonEnd,
            $paidDueDates,
        );

        $nextObligationCoverage = $this->forecast->summarizeNextObligation($projection, $balance);

        $nextIncome = null;
        foreach ($projection as $event) {
            if ($event['kind'] === 'income') {
                $nextIncome = [
                    'date' => $event['date'],
                    'title' => $event['title'],
                    'amount' => $event['amount'],
                    'recurring' => $event['recurring'],
                    'days_until' => max(0, (int) $today->diffInDays(\Carbon\Carbon::parse($event['date']), false)),
                ];
                break;
            }
        }

        $nextCoverageOut = null;
        if ($nextObligationCoverage !== null) {
            $nextCoverageOut = $nextObligationCoverage;
            $dueDate = \Carbon\Carbon::parse($nextObligationCoverage['due_date']);
            $nextCoverageOut['days_until'] = max(0, (int) $today->diffInDays($dueDate, false));
        }

        return [
            'horizon_until' => $horizonEnd->toDateString(),
            'next_income' => $nextIncome,
            'next_obligation_coverage' => $nextCoverageOut,
            'debt_payoff' => $this->buildDebtPayoff($obligations),
            'timeline' => array_slice($projection, 0, 16),
        ];
    }

    private function buildDebtPayoff($obligations): array
    {
        $debtTypes = ['loan', 'installment', 'personal_debt'];
        $result = [];

        foreach ($obligations as $obligation) {
            if (! in_array($obligation->type->value, $debtTypes, true)) {
                continue;
            }

            if ($obligation->remaining_amount === null) {
                continue;
            }

            $remaining = (string) $obligation->remaining_amount;
            if (Money::compare($remaining, '0') <= 0) {
                continue;
            }

            $payment = (string) $obligation->payment_amount;
            if (Money::compare($payment, '0') <= 0) {
                continue;
            }

            $rate = $obligation->interest_rate !== null
                ? (float) $obligation->interest_rate
                : null;

            $estimate = $this->debtPayoff->calculate($remaining, $payment, $rate);

            $result[] = [
                'obligation_id' => $obligation->id,
                'title' => $obligation->title,
                'type' => $obligation->type->value,
                'remaining' => Money::toApiNumber($remaining),
                'payment' => Money::toApiNumber($payment),
                'interest_rate' => $rate,
                'never_closes' => $estimate->neverCloses,
                'months_to_close' => $estimate->months,
                'expected_close_at' => $estimate->months !== null
                    ? now()->copy()->addMonthsNoOverflow($estimate->months)->toDateString()
                    : null,
                'total_to_pay' => $estimate->totalToPay !== null
                    ? Money::toApiNumber($estimate->totalToPay)
                    : null,
                'total_interest' => $estimate->totalInterest !== null
                    ? Money::toApiNumber($estimate->totalInterest)
                    : null,
                'min_payment_to_close' => $estimate->minPayment !== null
                    ? Money::toApiNumber($estimate->minPayment)
                    : null,
            ];
        }

        return $result;
    }

    private function formatNextObligation(
        ?array $nextPayment,
        string $balance,
        CarbonInterface $today,
        array $paidDueDates,
    ): ?array {
        if ($nextPayment === null) {
            return null;
        }

        $obligation = $nextPayment['obligation'];
        $covers = Money::compare($balance, $nextPayment['amount']) >= 0;
        $shortfall = $covers ? null : Money::toApiNumber(Money::sub($nextPayment['amount'], $balance));

        $progressData = ObligationProgress::calculate(
            $obligation->total_amount !== null ? (string) $obligation->total_amount : null,
            $obligation->remaining_amount !== null ? (string) $obligation->remaining_amount : null,
        );

        $debtTypes = ['loan', 'installment', 'personal_debt'];
        $isDebt = in_array($obligation->type->value, $debtTypes, true);
        $remaining = $obligation->remaining_amount !== null
            ? (string) $obligation->remaining_amount
            : null;

        $needsClose = $obligation->is_active
            && $isDebt
            && $remaining !== null
            && Money::compare($remaining, '0') <= 0;

        $debtOpen = $isDebt
            && $remaining !== null
            && Money::compare($remaining, '0') > 0;

        $paidForObligation = $paidDueDates[$obligation->id] ?? [];
        $dueKey = $nextPayment['due_date']->toDateString();
        $currentPeriodPaid = in_array($dueKey, $paidForObligation, true);

        return [
            'id' => $obligation->id,
            'title' => $obligation->title,
            'due_date' => $dueKey,
            'amount' => Money::toApiNumber($nextPayment['amount']),
            'days_until' => max(0, (int) $today->diffInDays($nextPayment['due_date'], false)),
            'balance_covers' => $covers,
            'shortfall' => $shortfall,
            'remaining_amount' => $remaining !== null ? Money::toApiNumber($remaining) : null,
            'progress_percent' => $progressData['progress_percent'] ?? null,
            'debt_open' => $debtOpen,
            'needs_close' => $needsClose,
            'current_period_paid' => $currentPeriodPaid,
        ];
    }

    private function isCheckInDue(UserSetting $settings, CarbonInterface $today): bool
    {
        if ($settings->last_check_in_at === null) {
            return true;
        }

        return $settings->last_check_in_at->copy()->startOfDay()->diffInDays($today) >= 7;
    }

    private function buildIncomesSection(int $userId, CarbonInterface $today): array
    {
        $items = $this->incomes->listForUser($userId);
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        $monthTotal = '0.00';
        $monthCount = 0;

        foreach ($items as $income) {
            if ($income->received_at->between($monthStart, $monthEnd)) {
                $monthTotal = Money::add($monthTotal, (string) $income->amount);
                $monthCount++;
            }
        }

        $recent = $items
            ->take(5)
            ->map(fn (Income $income) => $this->incomeItem($income))
            ->values()
            ->all();

        $lastIncome = $items->first();

        return [
            'summary' => [
                'total_this_month' => Money::toApiNumber($monthTotal),
                'count_this_month' => $monthCount,
                'total_all_time' => Money::toApiNumber($this->sumIncomeAmounts($items)),
                'last_received_at' => $lastIncome?->received_at->toDateString(),
            ],
            'recent' => $recent,
        ];
    }

    private function buildGoalsSection(int $userId, string $freeAfterObligations, ?int $daysRemaining): array
    {
        $activeGoals = $this->goals->listActiveForUser($userId);

        $items = $activeGoals
            ->take(2)
            ->map(function (Goal $goal) use ($freeAfterObligations, $daysRemaining) {
                $plan = $this->goalPlanner->plan($goal, $freeAfterObligations, $daysRemaining);

                return [
                    'id' => $goal->id,
                    'title' => $goal->title,
                    'target_amount' => Money::toApiNumber((string) $goal->target_amount),
                    'saved_amount' => Money::toApiNumber((string) $goal->saved_amount),
                    'target_date' => $goal->target_date?->toDateString(),
                    'plan' => $plan,
                ];
            })
            ->values()
            ->all();

        return [
            'active_count' => $activeGoals->count(),
            'items' => $items,
        ];
    }

    private function buildSavingsSection(int $userId): array
    {
        $accounts = $this->savings->listForUser($userId);

        $totalBalance = '0.00';
        $totalMonthly = '0.00';

        foreach ($accounts as $saving) {
            $totalBalance = Money::add($totalBalance, (string) $saving->balance);
            $totalMonthly = Money::add($totalMonthly, (string) $saving->monthly_contribution);
        }

        return [
            'summary' => [
                'total_balance' => Money::toApiNumber($totalBalance),
                'total_monthly_contribution' => Money::toApiNumber($totalMonthly),
                'accounts_count' => $accounts->count(),
            ],
            'accounts' => $accounts
                ->map(fn (Saving $saving) => $this->savingItem($saving))
                ->values()
                ->all(),
        ];
    }

    private function incomeItem(Income $income): array
    {
        return [
            'id' => $income->id,
            'title' => $income->title,
            'description' => $income->description,
            'amount' => Money::toApiNumber((string) $income->amount),
            'received_at' => $income->received_at->toDateString(),
        ];
    }

    private function savingItem(Saving $saving): array
    {
        return [
            'id' => $saving->id,
            'title' => $saving->title,
            'bank' => $saving->bank,
            'balance' => Money::toApiNumber((string) $saving->balance),
            'monthly_contribution' => Money::toApiNumber((string) $saving->monthly_contribution),
        ];
    }

    private function sumIncomeAmounts($items): string
    {
        $total = '0.00';

        foreach ($items as $income) {
            $total = Money::add($total, (string) $income->amount);
        }

        return $total;
    }
}
