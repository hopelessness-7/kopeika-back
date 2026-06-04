<?php

namespace App\Application\Services\Balance;

use App\Application\Auth\CurrentUserResolver;
use App\Application\Services\BaseService;
use App\Application\Support\Money;
use App\Domain\Contracts\Repositories\BalanceSnapshotRepositoryInterface;
use App\DTO\Balance\BalanceSnapshotData;

final class BalanceService extends BaseService
{
    public function __construct(
        CurrentUserResolver $currentUser,
        private readonly BalanceSnapshotRepositoryInterface $balances,
    ) {
        parent::__construct($currentUser);
    }

    public function record(BalanceSnapshotData $data): array
    {
        $snapshot = $this->balances->record($data);

        return [
            'balance' => Money::toApiNumber((string) $snapshot->amount),
            'balance_updated_at' => $snapshot->recorded_at->toIso8601String(),
        ];
    }
}
