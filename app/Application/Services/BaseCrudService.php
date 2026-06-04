<?php

namespace App\Application\Services;

use App\Domain\Contracts\Repositories\UserOwnedRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @template TModel of Model
 */
abstract class BaseCrudService extends BaseService
{
    abstract protected function repository(): UserOwnedRepositoryInterface;

    public function index(?int $userId = null): Collection
    {
        return $this->repository()->listForUser($userId ?? $this->currentUserId());
    }

    public function show(int $id, ?int $userId = null): Model
    {
        return $this->repository()->findForUserOrFail($userId ?? $this->currentUserId(), $id);
    }

    public function destroy(int $id, ?int $userId = null): void
    {
        $model = $this->repository()->findForUserOrFail($userId ?? $this->currentUserId(), $id);

        $this->repository()->delete($model);
    }
}
