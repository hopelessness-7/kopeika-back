<?php

namespace App\Http\Requests\CheckIn;

use App\DTO\CheckIn\CheckInData;
use App\Http\Requests\BaseRequest;

class SubmitCheckInRequest extends BaseRequest
{
    protected function dtoClass(): string
    {
        return CheckInData::class;
    }

    public function rules(): array
    {
        return [
            'balance_confirmed' => ['required', 'boolean'],
            'balance_amount' => ['nullable', 'required_if:balance_confirmed,false', 'numeric', 'min:0'],
            'large_expense_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
