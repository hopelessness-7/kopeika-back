<?php

namespace App\Http\Requests\Obligation\Concerns;

use App\Domain\Enums\ObligationType;
use Illuminate\Validation\Rule;

trait ValidatesObligationAttributes
{
    protected function obligationAttributeRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(ObligationType::values())],
            'payment_amount' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'payment_day' => ['required', 'integer', 'between:1,31'],
            'remaining_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lender' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
