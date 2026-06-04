<?php

namespace App\Http\Requests\Balance;

use App\Domain\Enums\BalanceSnapshotSource;
use App\DTO\Balance\BalanceSnapshotData;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class RecordBalanceRequest extends BaseRequest
{
    protected function dtoClass(): string
    {
        return BalanceSnapshotData::class;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'recorded_at' => ['nullable', 'date'],
            'source' => ['sometimes', Rule::in(BalanceSnapshotSource::values())],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function dtoPayload(): array
    {
        return array_merge(parent::dtoPayload(), [
            'source' => $this->input('source', BalanceSnapshotSource::Manual->value),
        ]);
    }
}
