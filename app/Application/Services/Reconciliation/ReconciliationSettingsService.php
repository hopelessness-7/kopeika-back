<?php

namespace App\Application\Services\Reconciliation;

use App\Application\Auth\CurrentUserResolver;
use App\Application\Services\BaseService;
use App\Domain\Contracts\Repositories\ReconciliationSettingsRepositoryInterface;
use App\DTO\Reconciliation\ReconciliationSettingsData;
use App\Models\ReconciliationSetting;

final class ReconciliationSettingsService extends BaseService
{
    public function __construct(
        CurrentUserResolver $currentUser,
        private readonly ReconciliationSettingsRepositoryInterface $settings,
    ) {
        parent::__construct($currentUser);
    }

    public function get(?int $userId = null): array
    {
        return $this->toApi($this->settings->findOrCreateForUser($userId ?? $this->currentUserId()));
    }

    public function update(ReconciliationSettingsData $data): array
    {
        return $this->toApi($this->settings->upsert($data));
    }

    private function toApi(ReconciliationSetting $settings): array
    {
        return [
            'import_interval_days' => $settings->import_interval_days,
            'last_import_at' => $settings->last_import_at?->toIso8601String(),
            'primary_anchor' => $settings->primary_anchor->value,
            'salary_day_of_month' => $settings->salary_day_of_month,
        ];
    }
}
