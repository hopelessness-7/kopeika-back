<?php

namespace App\Http\Controllers\Api;

use App\Application\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;

final class DashboardController extends BaseApiController
{
    public function __construct(
        private readonly DashboardService $dashboard,
    ) {}

    public function __invoke(): JsonResponse
    {
        return $this->success($this->dashboard->build($this->resolveUserId()));
    }
}
