<?php

namespace App\Http\Requests\Goal;

use App\DTO\Goal\GoalData;
use App\Http\Requests\BaseRequest;
use App\Http\Requests\Goal\Concerns\ValidatesGoalAttributes;

class StoreGoalRequest extends BaseRequest
{
    use ValidatesGoalAttributes;

    protected function dtoClass(): string
    {
        return GoalData::class;
    }

    public function rules(): array
    {
        return $this->goalAttributeRules();
    }
}
