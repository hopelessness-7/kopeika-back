<?php

namespace App\Http\Controllers\Api;

use App\Application\Services\BaseCrudService;
use App\Application\Services\Saving\SavingService;
use App\Http\Requests\Saving\StoreSavingRequest;
use App\Http\Requests\Saving\UpdateSavingRequest;
use App\Http\Resources\SavingResource;
use Illuminate\Http\JsonResponse;

final class SavingController extends BaseCrudController
{
    public function __construct(
        private readonly SavingService $savings,
    ) {}

    protected function crudService(): BaseCrudService
    {
        return $this->savings;
    }

    protected function resourceClass(): string
    {
        return SavingResource::class;
    }

    public function store(StoreSavingRequest $request): JsonResponse
    {
        return $this->respondStored($request, fn ($dto) => $this->savings->store($dto));
    }

    public function update(int $id, UpdateSavingRequest $request): JsonResponse
    {
        return $this->respondUpdated($id, $request, fn ($savingId, $dto) => $this->savings->update($savingId, $dto));
    }
}
