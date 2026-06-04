<?php

namespace App\Http\Resources;

use App\Application\Finance\ObligationSchedule;
use App\Application\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Obligation */
class ObligationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $schedule = app(ObligationSchedule::class);
        $nextPayment = $schedule->nextPaymentOnOrAfter($this->resource, now());

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type->value,
            'payment_amount' => Money::toApiNumber((string) $this->payment_amount),
            'payment_day' => $this->payment_day,
            'remaining_amount' => $this->remaining_amount !== null
                ? Money::toApiNumber((string) $this->remaining_amount)
                : null,
            'total_amount' => $this->total_amount !== null
                ? Money::toApiNumber((string) $this->total_amount)
                : null,
            'interest_rate' => $this->interest_rate !== null ? (float) $this->interest_rate : null,
            'lender' => $this->lender,
            'note' => $this->note,
            'is_active' => $this->is_active,
            'next_payment_date' => $nextPayment->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
