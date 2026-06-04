<?php

namespace App\Http\Requests\Income\Concerns;

trait ValidatesIncomeAttributes
{
    protected function incomeAttributeRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'received_at' => ['required', 'date'],
        ];
    }
}
