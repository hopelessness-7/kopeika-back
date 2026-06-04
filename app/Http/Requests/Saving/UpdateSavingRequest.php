<?php

namespace App\Http\Requests\Saving;

use App\DTO\Saving\SavingData;
use App\Http\Requests\BaseRequest;
use App\Http\Requests\Saving\Concerns\ValidatesSavingAttributes;

class UpdateSavingRequest extends BaseRequest
{
    use ValidatesSavingAttributes;

    protected function dtoClass(): string
    {
        return SavingData::class;
    }

    public function rules(): array
    {
        return $this->savingAttributeRules();
    }
}
