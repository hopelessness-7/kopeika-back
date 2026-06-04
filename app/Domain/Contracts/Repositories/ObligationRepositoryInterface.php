<?php

namespace App\Domain\Contracts\Repositories;

use App\DTO\Obligation\ObligationData;
use App\Models\Obligation;

/**
 * @extends UserOwnedRepositoryInterface<Obligation>
 */
interface ObligationRepositoryInterface extends UserOwnedRepositoryInterface
{
    public function listActiveForUser(int $userId): \Illuminate\Support\Collection;

    public function create(ObligationData $data): Obligation;

    public function save(Obligation $obligation): Obligation;
}
