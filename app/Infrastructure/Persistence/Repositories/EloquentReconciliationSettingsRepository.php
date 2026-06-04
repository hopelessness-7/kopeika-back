<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Contracts\Repositories\ReconciliationSettingsRepositoryInterface;
use App\Domain\Enums\ImportIntervalDays;
use App\Domain\Enums\PrimaryAnchor;
use App\DTO\Reconciliation\ReconciliationSettingsData;
use App\Models\ReconciliationSetting;

final class EloquentReconciliationSettingsRepository implements ReconciliationSettingsRepositoryInterface
{
    public function findByUserId(int $userId): ?ReconciliationSetting
    {
        return ReconciliationSetting::query()->where('user_id', $userId)->first();
    }

    public function findOrCreateForUser(int $userId): ReconciliationSetting
    {
        return ReconciliationSetting::query()->firstOrCreate(
            ['user_id' => $userId],
            [
                'import_interval_days' => ImportIntervalDays::TenDays->value,
                'primary_anchor' => PrimaryAnchor::Auto,
            ],
        );
    }

    public function upsert(ReconciliationSettingsData $data): ReconciliationSetting
    {
        return ReconciliationSetting::query()->updateOrCreate(
            ['user_id' => $data->userId],
            $data->toModelAttributes(),
        );
    }
}
