<?php

namespace App\Application\Services\Settings;

use App\Application\Auth\CurrentUserResolver;
use App\Application\Services\BaseService;
use App\Domain\Contracts\Repositories\UserSettingsRepositoryInterface;
use App\DTO\Settings\UserSettingsData;
use App\Models\UserSetting;

final class SettingsService extends BaseService
{
    public function __construct(
        CurrentUserResolver $currentUser,
        private readonly UserSettingsRepositoryInterface $settings,
    ) {
        parent::__construct($currentUser);
    }

    public function get(?int $userId = null): array
    {
        return $this->toApi($this->settings->findOrCreateForUser($userId ?? $this->currentUserId()));
    }

    public function update(UserSettingsData $data): array
    {
        return $this->toApi($this->settings->upsert($data));
    }

    private function toApi(UserSetting $settings): array
    {
        return [
            'notification_mode' => $settings->notification_mode->value,
            'last_check_in_at' => $settings->last_check_in_at?->toIso8601String(),
        ];
    }
}
