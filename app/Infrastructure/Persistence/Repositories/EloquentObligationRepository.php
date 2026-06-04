<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Contracts\Repositories\ObligationRepositoryInterface;
use App\DTO\Obligation\ObligationData;
use App\Models\Obligation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class EloquentObligationRepository implements ObligationRepositoryInterface
{
    public function listForUser(int $userId): Collection
    {
        return $this->listActiveForUser($userId);
    }

    public function listActiveForUser(int $userId): Collection
    {
        return Obligation::query()
            ->forUser($userId)
            ->active()
            ->orderBy('payment_day')
            ->get();
    }

    public function findForUser(int $userId, int $id): ?Obligation
    {
        return Obligation::query()
            ->forUser($userId)
            ->whereKey($id)
            ->first();
    }

    public function findForUserOrFail(int $userId, int $id): Obligation
    {
        return Obligation::query()
            ->forUser($userId)
            ->whereKey($id)
            ->firstOrFail();
    }

    public function create(ObligationData $data): Obligation
    {
        return Obligation::query()->create($data->toModelAttributes());
    }

    public function save(Obligation $obligation): Obligation
    {
        $obligation->save();

        return $obligation;
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }
}
