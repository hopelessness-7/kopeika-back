<?php

namespace App\DTO\Settings;

use App\Domain\Enums\NotificationMode;
use App\DTO\Concerns\MapsFromArray;
use App\DTO\Contracts\DataTransferObject;
use Carbon\CarbonInterface;

readonly class UserSettingsData implements DataTransferObject
{
    use MapsFromArray;

    public function __construct(
        public int $userId,
        public ?CarbonInterface $lastCheckInAt,
        public int $checkInStreakWeeks,
        public NotificationMode $notificationMode,
        public ?string $bufferAmount = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            userId: (int) $data['user_id'],
            lastCheckInAt: self::carbon($data, 'last_check_in_at'),
            checkInStreakWeeks: isset($data['check_in_streak_weeks'])
                ? (int) $data['check_in_streak_weeks']
                : 0,
            notificationMode: self::enum($data, 'notification_mode', NotificationMode::class, NotificationMode::Normal),
            bufferAmount: ($data['buffer_amount'] ?? null) !== null
                ? (string) $data['buffer_amount']
                : null,
        );
    }

    public function toModelAttributes(): array
    {
        return [
            'user_id' => $this->userId,
            'last_check_in_at' => $this->lastCheckInAt,
            'check_in_streak_weeks' => $this->checkInStreakWeeks,
            'notification_mode' => $this->notificationMode,
            'buffer_amount' => $this->bufferAmount,
        ];
    }
}
