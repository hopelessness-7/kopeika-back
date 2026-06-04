<?php

namespace App\Domain\Contracts\Repositories;

use App\DTO\Saving\SavingData;
use App\Models\Saving;

/**
 * @extends UserOwnedRepositoryInterface<Saving>
 */
interface SavingRepositoryInterface extends UserOwnedRepositoryInterface
{
    public function create(SavingData $data): Saving;

    public function save(Saving $saving): Saving;
}
