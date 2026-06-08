<?php

namespace App\Http\Requests\Obligation;

use App\Domain\Enums\ObligationPaymentStatus;
use App\DTO\Obligation\ObligationPaymentData;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StoreObligationPaymentRequest extends BaseRequest
{
    protected function dtoClass(): string
    {
        return ObligationPaymentData::class;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'paid_at' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'status' => ['sometimes', Rule::in(ObligationPaymentStatus::values())],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function defaultDtoAttributes(): array
    {
        return [
            'user_id' => $this->resolveUserId(),
            'obligation_id' => (int) $this->route('obligation'),
        ];
    }
}
