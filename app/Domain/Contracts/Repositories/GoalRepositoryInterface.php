<?php

namespace App\Domain\Contracts\Repositories;

use App\DTO\Goal\GoalData;
use App\Models\Goal;
use Illuminate\Support\Collection;

/**
 * @extends UserOwnedRepositoryInterface<Goal>
 */
interface GoalRepositoryInterface extends UserOwnedRepositoryInterface
{
    public function create(GoalData $data): Goal;

    public function save(Goal $goal): Goal;

    public function listActiveForUser(int $userId): Collection;
}
