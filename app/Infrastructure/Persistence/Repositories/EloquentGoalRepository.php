<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Contracts\Repositories\GoalRepositoryInterface;
use App\DTO\Goal\GoalData;
use App\Models\Goal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class EloquentGoalRepository implements GoalRepositoryInterface
{
    public function listForUser(int $userId): Collection
    {
        return Goal::query()
            ->forUser($userId)
            ->orderByDesc('is_active')
            ->orderBy('target_date')
            ->orderByDesc('id')
            ->get();
    }

    public function listActiveForUser(int $userId): Collection
    {
        return Goal::query()
            ->forUser($userId)
            ->where('is_active', true)
            ->orderBy('target_date')
            ->orderByDesc('id')
            ->get();
    }

    public function findForUserOrFail(int $userId, int $id): Goal
    {
        return Goal::query()
            ->forUser($userId)
            ->whereKey($id)
            ->firstOrFail();
    }

    public function create(GoalData $data): Goal
    {
        return Goal::query()->create($data->toModelAttributes());
    }

    public function save(Goal $goal): Goal
    {
        $goal->save();

        return $goal;
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }
}
