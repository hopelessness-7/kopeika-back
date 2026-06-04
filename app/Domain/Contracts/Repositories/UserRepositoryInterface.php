<?php

namespace App\Domain\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function findOrFail(int $id): User;

    public function save(User $user): User;
}
