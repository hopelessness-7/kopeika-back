<?php

namespace App\Http\Requests\Income;

use App\DTO\Income\IncomeData;
use App\Http\Requests\BaseRequest;
use App\Http\Requests\Income\Concerns\ValidatesIncomeAttributes;

class UpdateIncomeRequest extends BaseRequest
{
    use ValidatesIncomeAttributes;

    protected function dtoClass(): string
    {
        return IncomeData::class;
    }

    public function rules(): array
    {
        return $this->incomeAttributeRules();
    }
}
