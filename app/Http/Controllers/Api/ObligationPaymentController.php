<?php

namespace App\Http\Controllers\Api;

use App\Application\Services\Obligation\ObligationPaymentService;
use App\Http\Requests\Obligation\StoreObligationPaymentRequest;
use App\Http\Resources\ObligationPaymentResource;
use App\Http\Resources\ObligationResource;
use Illuminate\Http\JsonResponse;

final class ObligationPaymentController extends BaseApiController
{
    public function __construct(
        private readonly ObligationPaymentService $payments,
    ) {}

    public function index(int $obligation): JsonResponse
    {
        $items = $this->payments->list($obligation);

        return $this->success(ObligationPaymentResource::collection($items));
    }

    public function store(int $obligation, StoreObligationPaymentRequest $request): JsonResponse
    {
        $payment = $this->payments->store($obligation, $request->getDto());

        return $this->created(ObligationPaymentResource::make($payment));
    }

    public function destroy(int $obligation, int $payment): JsonResponse
    {
        $this->payments->destroy($obligation, $payment);

        return $this->noContent();
    }

    public function close(int $obligation): JsonResponse
    {
        $obligationModel = $this->payments->close($obligation);

        return $this->success(ObligationResource::make($obligationModel));
    }

    public function reopen(int $obligation): JsonResponse
    {
        $obligationModel = $this->payments->reopen($obligation);

        return $this->success(ObligationResource::make($obligationModel));
    }
}
