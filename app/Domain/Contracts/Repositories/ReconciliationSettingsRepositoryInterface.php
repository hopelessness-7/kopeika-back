<?php

namespace App\Domain\Contracts\Repositories;

use App\DTO\Reconciliation\ReconciliationSettingsData;
use App\Models\ReconciliationSetting;

interface ReconciliationSettingsRepositoryInterface
{
    public function findByUserId(int $userId): ?ReconciliationSetting;

    public function findOrCreateForUser(int $userId): ReconciliationSetting;

    public function upsert(ReconciliationSettingsData $data): ReconciliationSetting;
}
