<?php

namespace App\Http\Requests\Settings;

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
            'notification_mode' => ['required', Rule::in(NotificationMode::values())],
        ];
    }
}
