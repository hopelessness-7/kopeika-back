<?php

namespace App\Http\Controllers\Api\Reconciliation;

use App\Application\Services\Reconciliation\ReconciliationSettingsService;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Reconciliation\UpdateReconciliationSettingsRequest;
use Illuminate\Http\JsonResponse;

final class ReconciliationSettingsController extends BaseApiController
{
    public function __construct(
        private readonly ReconciliationSettingsService $settings,
    ) {}

    public function show(): JsonResponse
    {
        return $this->success($this->settings->get($this->resolveUserId()));
    }

    public function update(UpdateReconciliationSettingsRequest $request): JsonResponse
    {
        return $this->success($this->settings->update($request->getDto()));
    }
}
