<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Contracts\Repositories\SavingRepositoryInterface;
use App\DTO\Saving\SavingData;
use App\Models\Saving;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class EloquentSavingRepository implements SavingRepositoryInterface
{
    public function listForUser(int $userId): Collection
    {
        return Saving::query()
            ->forUser($userId)
            ->orderBy('title')
            ->get();
    }

    public function findForUserOrFail(int $userId, int $id): Saving
    {
        return Saving::query()
            ->forUser($userId)
            ->whereKey($id)
            ->firstOrFail();
    }

    public function create(SavingData $data): Saving
    {
        return Saving::query()->create($data->toModelAttributes());
    }

    public function save(Saving $saving): Saving
    {
        $saving->save();

        return $saving;
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }
}
