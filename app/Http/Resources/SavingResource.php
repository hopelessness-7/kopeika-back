<?php

namespace App\Http\Resources;

use App\Application\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Saving */
class SavingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'bank' => $this->bank,
            'balance' => Money::toApiNumber((string) $this->balance),
            'monthly_contribution' => Money::toApiNumber((string) $this->monthly_contribution),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
