<?php

namespace App\Http\Resources;

use App\Application\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Income */
class IncomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'amount' => Money::toApiNumber((string) $this->amount),
            'received_at' => $this->received_at->toDateString(),
            'is_recurring' => (bool) $this->is_recurring,
            'day_of_month' => $this->day_of_month,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
