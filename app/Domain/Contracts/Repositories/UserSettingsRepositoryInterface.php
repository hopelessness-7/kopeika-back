<?php

namespace App\Domain\Contracts\Repositories;

use App\DTO\Settings\UserSettingsData;
use App\Models\UserSetting;

interface UserSettingsRepositoryInterface
{
    public function findOrCreateForUser(int $userId): UserSetting;

    public function upsert(UserSettingsData $data): UserSetting;
}
