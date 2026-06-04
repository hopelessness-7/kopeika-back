<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Contracts\Repositories\BankImportRepositoryInterface;
use App\Models\BankImport;
use Illuminate\Support\Collection;

final class EloquentBankImportRepository implements BankImportRepositoryInterface
{
    public function listForUser(int $userId): Collection
    {
        return BankImport::query()
            ->forUser($userId)
            ->orderByDesc('imported_at')
            ->orderByDesc('id')
            ->get();
    }

    public function findForUserOrFail(int $userId, int $id): BankImport
    {
        return BankImport::query()
            ->forUser($userId)
            ->whereKey($id)
            ->firstOrFail();
    }

    public function save(BankImport $import): BankImport
    {
        $import->save();

        return $import;
    }
}
