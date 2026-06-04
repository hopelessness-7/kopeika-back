<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Contracts\Repositories\UserSettingsRepositoryInterface;
use App\Domain\Enums\NotificationMode;
use App\DTO\Settings\UserSettingsData;
use App\Models\UserSetting;

final class EloquentUserSettingsRepository implements UserSettingsRepositoryInterface
{
    public function findByUserId(int $userId): ?UserSetting
    {
        return UserSetting::query()->where('user_id', $userId)->first();
    }

    public function findOrCreateForUser(int $userId): UserSetting
    {
        return UserSetting::query()->firstOrCreate(
            ['user_id' => $userId],
            ['notification_mode' => NotificationMode::Normal],
        );
    }

    public function upsert(UserSettingsData $data): UserSetting
    {
        return UserSetting::query()->updateOrCreate(
            ['user_id' => $data->userId],
            $data->toModelAttributes(),
        );
    }
}
