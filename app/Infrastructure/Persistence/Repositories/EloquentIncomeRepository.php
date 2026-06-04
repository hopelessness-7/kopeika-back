<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Contracts\Repositories\IncomeRepositoryInterface;
use App\DTO\Income\IncomeData;
use App\Models\Income;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class EloquentIncomeRepository implements IncomeRepositoryInterface
{
    public function listForUser(int $userId): Collection
    {
        return Income::query()
            ->forUser($userId)
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->get();
    }

    public function findForUser(int $userId, int $id): ?Income
    {
        return Income::query()
            ->forUser($userId)
            ->whereKey($id)
            ->first();
    }

    public function findForUserOrFail(int $userId, int $id): Income
    {
        return Income::query()
            ->forUser($userId)
            ->whereKey($id)
            ->firstOrFail();
    }

    public function create(IncomeData $data): Income
    {
        return Income::query()->create($data->toModelAttributes());
    }

    public function save(Income $income): Income
    {
        $income->save();

        return $income;
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }
}
