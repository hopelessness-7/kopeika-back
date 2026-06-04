<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Contracts\Repositories\BalanceSnapshotRepositoryInterface;
use App\DTO\Balance\BalanceSnapshotData;
use App\Models\BalanceSnapshot;

final class EloquentBalanceSnapshotRepository implements BalanceSnapshotRepositoryInterface
{
    public function latestForUser(int $userId): ?BalanceSnapshot
    {
        return BalanceSnapshot::query()
            ->forUser($userId)
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->first();
    }

    public function record(BalanceSnapshotData $data): BalanceSnapshot
    {
        return BalanceSnapshot::query()->create($data->toModelAttributes());
    }
}
