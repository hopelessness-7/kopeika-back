<?php

namespace App\Domain\Contracts\Repositories;

use App\DTO\Income\IncomeData;
use App\Models\Income;

/**
 * @extends UserOwnedRepositoryInterface<Income>
 */
interface IncomeRepositoryInterface extends UserOwnedRepositoryInterface
{
    public function create(IncomeData $data): Income;

    public function save(Income $income): Income;
}
