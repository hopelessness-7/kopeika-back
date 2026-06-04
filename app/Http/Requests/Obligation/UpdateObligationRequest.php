<?php

namespace App\Http\Requests\Obligation;

use App\DTO\Obligation\ObligationData;
use App\Http\Requests\BaseRequest;
use App\Http\Requests\Obligation\Concerns\ValidatesObligationAttributes;

class UpdateObligationRequest extends BaseRequest
{
    use ValidatesObligationAttributes;

    protected function dtoClass(): string
    {
        return ObligationData::class;
    }

    public function rules(): array
    {
        return $this->obligationAttributeRules();
    }
}
