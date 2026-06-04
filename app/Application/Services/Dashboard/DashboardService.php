<?php

namespace App\Application\Services\Dashboard;

use App\Application\Auth\CurrentUserResolver;
use App\Application\Finance\AnchorDateCalculator;
use App\Application\Finance\ObligationSchedule;
use App\Application\Finance\SafeToSpendCalculator;
use App\Application\Finance\ZoneResolver;
use App\Application\Services\BaseService;
use App\Application\Support\Money;
use App\Domain\Contracts\Repositories\BalanceSnapshotRepositoryInterface;
use App\Domain\Contracts\Repositories\IncomeRepositoryInterface;
use App\Domain\Contracts\Repositories\ObligationRepositoryInterface;
use App\Domain\Contracts\Repositories\ReconciliationSettingsRepositoryInterface;
use App\Domain\Contracts\Repositories\SavingRepositoryInterface;
use App\Domain\Contracts\Repositories\UserSettingsRepositoryInterface;
use App\Models\Income;
use App\Models\Saving;
use App\Domain\Enums\ImportIntervalDays;
use App\Domain\Enums\PrimaryAnchor;
use App\Models\ReconciliationSetting;
use App\Models\UserSetting;
use Carbon\CarbonInterface;

final class DashboardService extends BaseService
{
    public function __construct(
        CurrentUserResolver $currentUser,
        private readonly UserSettingsRepositoryInterface $settings,
        private readonly ReconciliationSettingsRepositoryInterface $reconciliationSettings,
        private readonly BalanceSnapshotRepositoryInterface $balances,
        private readonly ObligationRepositoryInterface $obligations,
        private readonly IncomeRepositoryInterface $incomes,
        private readonly SavingRepositoryInterface $savings,
        private readonly AnchorDateCalculator $anchorDates,
        private readonly ObligationSchedule $obligationSchedule,
        private readonly SafeToSpendCalculator $safeToSpend,
        private readonly ZoneResolver $zoneResolver,
    ) {
        parent::__construct($currentUser);
    }

    public function build(?int $userId = null): array
    {
        $userId ??= $this->currentUserId();
        $today = now()->startOfDay();
        $userSettings = $this->settings->findOrCreateForUser($userId);
        $reconciliation = $this->reconciliationSettings->findOrCreateForUser($userId);
        $balanceSnapshot = $this->balances->latestForUser($userId);
        $balance = $balanceSnapshot !== null
            ? Money::normalize((string) $balanceSnapshot->amount)
            : '0.00';
        $obligations = $this->obligations->listActiveForUser($userId);

        $salaryAnchor = $this->buildSalaryAnchor($reconciliation, $obligations, $balance, $today);
        $importAnchor = $this->buildImportAnchor($reconciliation, $obligations, $balance, $today);
        $primaryKey = $this->resolvePrimaryAnchorKey($reconciliation, $salaryAnchor, $importAnchor);

        $primaryDailyLimit = $primaryKey === 'salary'
            ? ($salaryAnchor['daily_limit'] ?? 0)
            : ($importAnchor['daily_limit'] ?? 0);

        $freeAfterObligations = $this->freeForAnchor(
            $primaryKey === 'salary' ? $salaryAnchor : $importAnchor,
            $balance,
        );

        $nextPayment = $this->obligationSchedule->findNext($obligations, $today);
        $nextObligation = $this->formatNextObligation($nextPayment, $balance, $today);

        $zone = $this->zoneResolver->resolve(
            $freeAfterObligations,
            $balance,
            $nextPayment['amount'] ?? null,
            $nextPayment['due_date'] ?? null,
            $today,
        );

        $importDueAt = isset($importAnchor['next_due_at'])
            ? \Carbon\Carbon::parse($importAnchor['next_due_at'])->startOfDay()
            : null;

        if ($salaryAnchor !== null) {
            unset($salaryAnchor['_free']);
        }
        unset($importAnchor['_free']);

        return [
            'balance' => Money::toApiNumber($balance),
            'balance_updated_at' => $balanceSnapshot?->recorded_at?->toIso8601String(),
            'incomes' => $this->buildIncomesSection($userId, $today),
            'savings' => $this->buildSavingsSection($userId),
            'zone' => $zone->value,
            'free_after_obligations' => Money::toApiNumber($freeAfterObligations),
            'anchors' => [
                'primary' => $primaryKey,
                'salary' => $salaryAnchor,
                'import' => $importAnchor,
            ],
            'primary_daily_limit' => $primaryDailyLimit,
            'next_obligation' => $nextObligation,
            'obligations_until_salary_total' => Money::toApiNumber(
                $salaryAnchor !== null
                    ? $this->obligationsTotalForAnchor($obligations, $reconciliation, 'salary', $today)
                    : '0.00',
            ),
            'check_in_due' => $this->isCheckInDue($userSettings, $today),
            'import_due' => $importDueAt !== null && $today->diffInDays($importDueAt, false) <= 1 && $today->lte($importDueAt),
            'import_overdue' => $importDueAt !== null && $today->gt($importDueAt),
            'streak' => [
                'import_on_time' => 0,
                'check_in_weeks' => 0,
            ],
        ];
    }

    private function buildSalaryAnchor(
        ReconciliationSetting $settings,
        $obligations,
        string $balance,
        CarbonInterface $today,
    ): ?array {
        if ($settings->salary_day_of_month === null) {
            return null;
        }

        $anchorDate = $this->anchorDates->nextSalaryDate($settings->salary_day_of_month, $today);
        $daysRemaining = $this->anchorDates->daysRemaining($anchorDate, $today);
        $obligationsUntil = $this->obligationSchedule->totalDueUntil($obligations, $anchorDate, $today);
        $limits = $this->safeToSpend->calculate($balance, $obligationsUntil, $daysRemaining);

        return [
            'day_of_month' => $settings->salary_day_of_month,
            'next_date' => $anchorDate->toDateString(),
            'days_remaining' => $daysRemaining,
            'daily_limit' => Money::toApiNumber($limits['daily_limit']),
            '_free' => $limits['free'],
        ];
    }

    private function buildImportAnchor(
        ReconciliationSetting $settings,
        $obligations,
        string $balance,
        CarbonInterface $today,
    ): array {
        $interval = ImportIntervalDays::fromDays($settings->import_interval_days);
        $anchorDate = $this->anchorDates->nextImportDue($settings->last_import_at, $interval, $today);
        $daysRemaining = $this->anchorDates->daysRemaining($anchorDate, $today);
        $obligationsUntil = $this->obligationSchedule->totalDueUntil($obligations, $anchorDate, $today);
        $limits = $this->safeToSpend->calculate($balance, $obligationsUntil, $daysRemaining);

        return [
            'interval_days' => $interval->value,
            'last_import_at' => $settings->last_import_at?->toIso8601String(),
            'next_due_at' => $anchorDate->toDateString(),
            'days_remaining' => $daysRemaining,
            'daily_limit' => Money::toApiNumber($limits['daily_limit']),
            '_free' => $limits['free'],
        ];
    }

    private function resolvePrimaryAnchorKey(
        ReconciliationSetting $settings,
        ?array $salaryAnchor,
        array $importAnchor,
    ): string {
        $key = match ($settings->primary_anchor) {
            PrimaryAnchor::Salary => 'salary',
            PrimaryAnchor::Import => 'import',
            PrimaryAnchor::Auto => $this->pickStricterAnchor($salaryAnchor, $importAnchor),
        };

        if ($key === 'salary' && $salaryAnchor === null) {
            return 'import';
        }

        return $key;
    }

    private function pickStricterAnchor(?array $salaryAnchor, array $importAnchor): string
    {
        if ($salaryAnchor === null) {
            return 'import';
        }

        return $salaryAnchor['days_remaining'] <= $importAnchor['days_remaining'] ? 'salary' : 'import';
    }

    private function freeForAnchor(?array $anchor, string $balance): string
    {
        if ($anchor !== null && isset($anchor['_free'])) {
            return $anchor['_free'];
        }

        return $balance;
    }

    private function formatNextObligation(?array $nextPayment, string $balance, CarbonInterface $today): ?array
    {
        if ($nextPayment === null) {
            return null;
        }

        $covers = Money::compare($balance, $nextPayment['amount']) >= 0;
        $shortfall = $covers ? null : Money::toApiNumber(Money::sub($nextPayment['amount'], $balance));

        return [
            'id' => $nextPayment['obligation']->id,
            'title' => $nextPayment['obligation']->title,
            'due_date' => $nextPayment['due_date']->toDateString(),
            'amount' => Money::toApiNumber($nextPayment['amount']),
            'days_until' => max(0, (int) $today->diffInDays($nextPayment['due_date'], false)),
            'balance_covers' => $covers,
            'shortfall' => $shortfall,
        ];
    }

    private function obligationsTotalForAnchor($obligations, ReconciliationSetting $settings, string $anchorKey, CarbonInterface $today): string
    {
        if ($anchorKey === 'salary' && $settings->salary_day_of_month !== null) {
            $anchorDate = $this->anchorDates->nextSalaryDate($settings->salary_day_of_month, $today);

            return $this->obligationSchedule->totalDueUntil($obligations, $anchorDate, $today);
        }

        $interval = ImportIntervalDays::fromDays($settings->import_interval_days);
        $anchorDate = $this->anchorDates->nextImportDue($settings->last_import_at, $interval, $today);

        return $this->obligationSchedule->totalDueUntil($obligations, $anchorDate, $today);
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
