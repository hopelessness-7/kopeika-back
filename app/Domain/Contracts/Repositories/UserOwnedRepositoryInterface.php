<?php

namespace App\Domain\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @template TModel of Model
 */
interface UserOwnedRepositoryInterface
{
    public function listForUser(int $userId): Collection;

    public function findForUser(int $userId, int $id): ?Model;

    public function findForUserOrFail(int $userId, int $id): Model;

    public function delete(Model $model): void;
}
