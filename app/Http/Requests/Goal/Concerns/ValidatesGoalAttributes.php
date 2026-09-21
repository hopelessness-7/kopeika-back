<?php

namespace App\Http\Requests\Goal\Concerns;

trait ValidatesGoalAttributes
{
    protected function goalAttributeRules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'title' => [$required, 'string', 'max:255'],
            'target_amount' => [$required, 'numeric', 'min:0.01', 'max:999999999999.99'],
            'saved_amount' => ['sometimes', 'numeric', 'min:0', 'max:999999999999.99'],
            'target_date' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
