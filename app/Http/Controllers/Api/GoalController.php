<?php

namespace App\Http\Controllers\Api;

use App\Application\Services\BaseCrudService;
use App\Application\Services\Goal\GoalService;
use App\Http\Requests\Goal\StoreGoalRequest;
use App\Http\Requests\Goal\UpdateGoalRequest;
use App\Http\Resources\GoalResource;
use Illuminate\Http\JsonResponse;

final class GoalController extends BaseCrudController
{
    public function __construct(
        private readonly GoalService $goals,
    ) {}

    protected function crudService(): BaseCrudService
    {
        return $this->goals;
    }

    protected function resourceClass(): string
    {
        return GoalResource::class;
    }

    public function store(StoreGoalRequest $request): JsonResponse
    {
        return $this->respondStored($request, fn ($dto) => $this->goals->store($dto));
    }

    public function show(int $goal): JsonResponse
    {
        return parent::show($goal);
    }

    public function update(int $goal, UpdateGoalRequest $request): JsonResponse
    {
        return $this->respondUpdated($goal, $request, fn ($goalId, $dto) => $this->goals->update($goalId, $dto));
    }

    public function destroy(int $goal): JsonResponse
    {
        return parent::destroy($goal);
    }
}
