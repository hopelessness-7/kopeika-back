<?php

namespace App\Http\Controllers\Api;

use App\Application\Services\BaseCrudService;
use App\Application\Services\Obligation\ObligationService;
use App\Http\Requests\Obligation\StoreObligationRequest;
use App\Http\Requests\Obligation\UpdateObligationRequest;
use App\Http\Resources\ObligationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

final class ObligationController extends BaseCrudController
{
    public function __construct(
        private readonly ObligationService $obligations,
    ) {}

    protected function crudService(): BaseCrudService
    {
        return $this->obligations;
    }

    protected function resourceClass(): string
    {
        return ObligationResource::class;
    }

    public function store(StoreObligationRequest $request): JsonResponse
    {
        return $this->respondStored($request, fn ($dto) => $this->obligations->store($dto));
    }

    public function update(int $id, UpdateObligationRequest $request): JsonResponse
    {
        return $this->respondUpdated($id, $request, fn ($obligationId, $dto) => $this->obligations->update($obligationId, $dto));
    }
}
