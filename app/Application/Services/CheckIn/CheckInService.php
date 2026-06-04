<?php

namespace App\Application\Services\CheckIn;

use App\Application\Auth\CurrentUserResolver;
use App\Application\Services\BaseService;
use App\Application\Services\Dashboard\DashboardService;
use App\Domain\Contracts\Repositories\BalanceSnapshotRepositoryInterface;
use App\Domain\Contracts\Repositories\UserSettingsRepositoryInterface;
use App\Domain\Enums\BalanceSnapshotSource;
use App\DTO\Balance\BalanceSnapshotData;
use App\DTO\CheckIn\CheckInData;
use App\Models\QuickExpense;
use App\Models\UserSetting;

final class CheckInService extends BaseService
{
    public function __construct(
        CurrentUserResolver $currentUser,
        private readonly UserSettingsRepositoryInterface $settings,
        private readonly BalanceSnapshotRepositoryInterface $balances,
        private readonly DashboardService $dashboard,
    ) {
        parent::__construct($currentUser);
    }

    public function submit(CheckInData $data): array
    {
        $settings = $this->settings->findOrCreateForUser($data->userId);

        if (! $data->balanceConfirmed && $data->balanceAmount !== null) {
            $this->balances->record(new BalanceSnapshotData(
                userId: $data->userId,
                amount: $data->balanceAmount,
                source: BalanceSnapshotSource::Manual,
                recordedAt: now(),
                note: 'Check-in',
            ));
        }

        if ($data->largeExpenseAmount !== null) {
            QuickExpense::query()->create([
                'user_id' => $data->userId,
                'amount' => $data->largeExpenseAmount,
                'noted_at' => now(),
                'note' => 'Check-in',
            ]);
        }

        $this->touchCheckIn($settings);

        return $this->dashboard->build($data->userId);
    }

    private function touchCheckIn(UserSetting $settings): void
    {
        $settings->last_check_in_at = now();
        $settings->save();
    }
}
