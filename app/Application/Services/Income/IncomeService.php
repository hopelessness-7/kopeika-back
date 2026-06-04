<?php

namespace App\Application\Services\Income;

use App\Application\Auth\CurrentUserResolver;
use App\Application\Services\BaseCrudService;
use App\Domain\Contracts\Repositories\IncomeRepositoryInterface;
use App\Domain\Contracts\Repositories\UserOwnedRepositoryInterface;
use App\DTO\Income\IncomeData;
use App\Models\Income;

final class IncomeService extends BaseCrudService
{
    public function __construct(
        private readonly IncomeRepositoryInterface $incomes,
        CurrentUserResolver $currentUser,
    ) {
        parent::__construct($currentUser);
    }

    protected function repository(): UserOwnedRepositoryInterface
    {
        return $this->incomes;
    }

    public function store(IncomeData $data): Income
    {
        return $this->incomes->create($data);
    }

    public function update(int $id, IncomeData $data): Income
    {
        $income = $this->incomes->findForUserOrFail($this->currentUserId(), $id);
        $income->fill($data->toModelAttributes(forUpdate: true));
        $this->incomes->save($income);

        return $income;
    }
}
