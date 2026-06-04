<?php

namespace App\Http\Controllers\Api;

use App\Application\Services\CheckIn\CheckInService;
use App\Http\Requests\CheckIn\SubmitCheckInRequest;
use Illuminate\Http\JsonResponse;

final class CheckInController extends BaseApiController
{
    public function __construct(
        private readonly CheckInService $checkIn,
    ) {}

    public function store(SubmitCheckInRequest $request): JsonResponse
    {
        return $this->success($this->checkIn->submit($request->getDto()));
    }
}
