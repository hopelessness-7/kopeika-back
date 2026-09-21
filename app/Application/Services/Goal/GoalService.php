<?php

namespace App\Application\Services\Goal;

use App\Application\Auth\CurrentUserResolver;
use App\Application\Services\BaseCrudService;
use App\Domain\Contracts\Repositories\GoalRepositoryInterface;
use App\Domain\Contracts\Repositories\UserOwnedRepositoryInterface;
use App\DTO\Goal\GoalData;
use App\Models\Goal;

final class GoalService extends BaseCrudService
{
    public function __construct(
        private readonly GoalRepositoryInterface $goals,
        CurrentUserResolver $currentUser,
    ) {
        parent::__construct($currentUser);
    }

    protected function repository(): UserOwnedRepositoryInterface
    {
        return $this->goals;
    }

    public function store(GoalData $data): Goal
    {
        return $this->goals->create($data);
    }

    public function update(int $id, GoalData $data): Goal
    {
        $goal = $this->goals->findForUserOrFail($this->currentUserId(), $id);
        $goal->fill($data->toModelAttributes(forUpdate: true));
        $this->goals->save($goal);

        return $goal;
    }
}
