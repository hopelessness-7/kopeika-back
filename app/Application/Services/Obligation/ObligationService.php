<?php

namespace App\Application\Services\Obligation;

use App\Application\Auth\CurrentUserResolver;
use App\Application\Services\BaseCrudService;
use App\Domain\Contracts\Repositories\ObligationRepositoryInterface;
use App\Domain\Contracts\Repositories\UserOwnedRepositoryInterface;
use App\DTO\Obligation\ObligationData;
use App\Models\Obligation;

final class ObligationService extends BaseCrudService
{
    public function __construct(
        private readonly ObligationRepositoryInterface $obligations,
        CurrentUserResolver $currentUser,
    ) {
        parent::__construct($currentUser);
    }

    protected function repository(): UserOwnedRepositoryInterface
    {
        return $this->obligations;
    }

    public function store(ObligationData $data): Obligation
    {
        return $this->obligations->create($data);
    }

    public function update(int $id, ObligationData $data): Obligation
    {
        $obligation = $this->obligations->findForUserOrFail($this->currentUserId(), $id);
        $obligation->fill($data->toModelAttributes(forUpdate: true));
        $this->obligations->save($obligation);

        return $obligation;
    }
}
