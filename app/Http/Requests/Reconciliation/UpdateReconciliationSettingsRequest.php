<?php

namespace App\Http\Requests\Reconciliation;

use App\Domain\Enums\ImportIntervalDays;
use App\Domain\Enums\PrimaryAnchor;
use App\DTO\Reconciliation\ReconciliationSettingsData;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateReconciliationSettingsRequest extends BaseRequest
{
    protected function dtoClass(): string
    {
        return ReconciliationSettingsData::class;
    }

    public function rules(): array
    {
        return [
            'import_interval_days' => ['required', Rule::in(ImportIntervalDays::values())],
            'last_import_at' => ['nullable', 'date'],
            'primary_anchor' => ['required', Rule::in(PrimaryAnchor::values())],
            'salary_day_of_month' => ['nullable', 'integer', 'between:1,31'],
        ];
    }
}
