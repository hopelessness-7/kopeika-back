<?php

namespace App\Http\Resources;

use App\Application\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Goal */
class GoalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'target_amount' => Money::toApiNumber((string) $this->target_amount),
            'saved_amount' => Money::toApiNumber((string) $this->saved_amount),
            'target_date' => $this->target_date?->toDateString(),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
