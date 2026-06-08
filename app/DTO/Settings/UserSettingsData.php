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
        public NotificationMode $notificationMode,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            userId: (int) $data['user_id'],
            lastCheckInAt: self::carbon($data, 'last_check_in_at'),
            notificationMode: self::enum($data, 'notification_mode', NotificationMode::class, NotificationMode::Normal),
        );
    }

    public function toModelAttributes(): array
    {
        return [
            'user_id' => $this->userId,
            'last_check_in_at' => $this->lastCheckInAt,
            'notification_mode' => $this->notificationMode,
        ];
    }
}
