<?php

namespace App\Application\Services\Saving;

use App\Application\Auth\CurrentUserResolver;
use App\Application\Services\BaseCrudService;
use App\Domain\Contracts\Repositories\SavingRepositoryInterface;
use App\Domain\Contracts\Repositories\UserOwnedRepositoryInterface;
use App\DTO\Saving\SavingData;
use App\Models\Saving;

final class SavingService extends BaseCrudService
{
    public function __construct(
        private readonly SavingRepositoryInterface $savings,
        CurrentUserResolver $currentUser,
    ) {
        parent::__construct($currentUser);
    }

    protected function repository(): UserOwnedRepositoryInterface
    {
        return $this->savings;
    }

    public function store(SavingData $data): Saving
    {
        return $this->savings->create($data);
    }

    public function update(int $id, SavingData $data): Saving
    {
        $saving = $this->savings->findForUserOrFail($this->currentUserId(), $id);
        $saving->fill($data->toModelAttributes(forUpdate: true));
        $this->savings->save($saving);

        return $saving;
    }
}
