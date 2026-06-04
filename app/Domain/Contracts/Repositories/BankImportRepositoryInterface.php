<?php

namespace App\Domain\Contracts\Repositories;

use App\Models\BankImport;
use Illuminate\Support\Collection;

interface BankImportRepositoryInterface
{
    public function listForUser(int $userId): Collection;

    public function findForUserOrFail(int $userId, int $id): BankImport;

    public function save(BankImport $import): BankImport;
}
