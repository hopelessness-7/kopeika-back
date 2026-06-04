<?php

namespace App\Http\Requests\Saving\Concerns;

trait ValidatesSavingAttributes
{
    protected function savingAttributeRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'bank' => ['required', 'string', 'max:255'],
            'balance' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'monthly_contribution' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
        ];
    }
}
