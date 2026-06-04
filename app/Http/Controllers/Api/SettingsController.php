<?php

namespace App\Http\Controllers\Api;

use App\Application\Services\Settings\SettingsService;
use App\Http\Requests\Settings\UpdateSettingsRequest;
use Illuminate\Http\JsonResponse;

final class SettingsController extends BaseApiController
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    public function show(): JsonResponse
    {
        return $this->success($this->settings->get($this->resolveUserId()));
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        return $this->success($this->settings->update($request->getDto()));
    }
}
