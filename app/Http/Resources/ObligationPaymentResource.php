<?php

namespace App\Http\Resources;

use App\Application\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ObligationPayment */
class ObligationPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'obligation_id' => $this->obligation_id,
            'amount' => Money::toApiNumber((string) $this->amount),
            'due_date' => $this->due_date->toDateString(),
            'status' => $this->status->value,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'note' => $this->note,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
