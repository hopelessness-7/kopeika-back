<?php

namespace App\Http\Requests\Settings;

use App\Domain\Contracts\Repositories\UserSettingsRepositoryInterface;
use App\Domain\Enums\NotificationMode;
use App\DTO\Settings\UserSettingsData;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends BaseRequest
{
    protected function dtoClass(): string
    {
        return UserSettingsData::class;
    }

    public function rules(): array
    {
        return [
            'last_check_in_at' => ['nullable', 'date'],
            'notification_mode' => ['sometimes', Rule::in(NotificationMode::values())],
            'buffer_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function dtoPayload(): array
    {
        $userId = $this->resolveUserId();
        $existing = app(UserSettingsRepositoryInterface::class)->findOrCreateForUser($userId);

        return array_merge(
            [
                'user_id' => $userId,
                'notification_mode' => $existing->notification_mode->value,
                'last_check_in_at' => $existing->last_check_in_at,
                'check_in_streak_weeks' => (int) ($existing->check_in_streak_weeks ?? 0),
                'buffer_amount' => $existing->buffer_amount,
            ],
            $this->validated(),
        );
    }
}
