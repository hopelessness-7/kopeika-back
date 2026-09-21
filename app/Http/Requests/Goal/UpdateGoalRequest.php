<?php

namespace App\Http\Requests\Goal;

use App\Domain\Contracts\Repositories\GoalRepositoryInterface;
use App\DTO\Goal\GoalData;
use App\Http\Requests\BaseRequest;
use App\Http\Requests\Goal\Concerns\ValidatesGoalAttributes;

class UpdateGoalRequest extends BaseRequest
{
    use ValidatesGoalAttributes;

    protected function dtoClass(): string
    {
        return GoalData::class;
    }

    public function rules(): array
    {
        return $this->goalAttributeRules(partial: true);
    }

    protected function dtoPayload(): array
    {
        $userId = $this->resolveUserId();
        $existing = app(GoalRepositoryInterface::class)
            ->findForUserOrFail($userId, (int) $this->route('goal'));

        return array_merge(
            [
                'user_id' => $userId,
                'title' => $existing->title,
                'target_amount' => (string) $existing->target_amount,
                'saved_amount' => (string) $existing->saved_amount,
                'target_date' => $existing->target_date,
                'is_active' => $existing->is_active,
            ],
            $this->validated(),
        );
    }
}
