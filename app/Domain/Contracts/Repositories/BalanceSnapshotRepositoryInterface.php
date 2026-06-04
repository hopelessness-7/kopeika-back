<?php

namespace App\Domain\Contracts\Repositories;

use App\DTO\Balance\BalanceSnapshotData;
use App\Models\BalanceSnapshot;

interface BalanceSnapshotRepositoryInterface
{
    public function latestForUser(int $userId): ?BalanceSnapshot;

    public function record(BalanceSnapshotData $data): BalanceSnapshot;
}
