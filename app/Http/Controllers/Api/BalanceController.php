<?php

namespace App\Http\Controllers\Api;

use App\Application\Services\Balance\BalanceService;
use App\Http\Requests\Balance\RecordBalanceRequest;
use Illuminate\Http\JsonResponse;

final class BalanceController extends BaseApiController
{
    public function __construct(
        private readonly BalanceService $balance,
    ) {}

    public function store(RecordBalanceRequest $request): JsonResponse
    {
        return $this->success($this->balance->record($request->getDto()));
    }
}
