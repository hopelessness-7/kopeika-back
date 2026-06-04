<?php

namespace App\Http\Controllers\Api;

use App\Application\Services\BaseCrudService;
use App\Application\Services\Income\IncomeService;
use App\Http\Requests\Income\StoreIncomeRequest;
use App\Http\Requests\Income\UpdateIncomeRequest;
use App\Http\Resources\IncomeResource;
use Illuminate\Http\JsonResponse;

final class IncomeController extends BaseCrudController
{
    public function __construct(
        private readonly IncomeService $incomes,
    ) {}

    protected function crudService(): BaseCrudService
    {
        return $this->incomes;
    }

    protected function resourceClass(): string
    {
        return IncomeResource::class;
    }

    public function store(StoreIncomeRequest $request): JsonResponse
    {
        return $this->respondStored($request, fn ($dto) => $this->incomes->store($dto));
    }

    public function update(int $id, UpdateIncomeRequest $request): JsonResponse
    {
        return $this->respondUpdated($id, $request, fn ($incomeId, $dto) => $this->incomes->update($incomeId, $dto));
    }
}
