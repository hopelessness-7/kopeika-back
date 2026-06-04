<?php

namespace App\Application\Services;

use App\Application\Auth\CurrentUserResolver;

abstract class BaseService
{
    public function __construct(
        protected readonly CurrentUserResolver $currentUser,
    ) {}

    protected function currentUserId(): int
    {
        return $this->currentUser->id();
    }
}
