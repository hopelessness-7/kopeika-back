<?php

namespace App\Http\Controllers\Api\Reconciliation;

use App\Application\Services\Reconciliation\BankImportService;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Reconciliation\StoreBankImportRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class BankImportController extends BaseApiController
{
    public function __construct(
        private readonly BankImportService $imports,
    ) {}

    public function index(): JsonResponse
    {
        return $this->success($this->imports->list($this->resolveUserId()));
    }

    public function store(StoreBankImportRequest $request): JsonResponse
    {
        return $this->created(
            $this->imports->store(
                $request->file('file'),
                $request->validated('bank'),
                $this->resolveUserId(),
            ),
        );
    }

    public function show(int $id): JsonResponse
    {
        return $this->success($this->imports->show($id, $this->resolveUserId()));
    }

    public function download(int $id): StreamedResponse
    {
        return $this->imports->download($id, $this->resolveUserId());
    }
}
