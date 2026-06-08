<?php

namespace App\DTO\Income;

use App\DTO\Concerns\MapsFromArray;
use App\DTO\Contracts\DataTransferObject;
use Carbon\CarbonInterface;

readonly class IncomeData implements DataTransferObject
{
    use MapsFromArray;

    public function __construct(
        public int $userId,
        public string $title,
        public string $amount,
        public CarbonInterface $receivedAt,
        public ?string $description = null,
        public bool $isRecurring = false,
        public ?int $dayOfMonth = null,
        public bool $isActive = true,
    ) {}

    public static function fromArray(array $data): static
    {
        $isRecurring = self::bool($data, 'is_recurring', false);
        $dayOfMonth = self::int($data, 'day_of_month');

        return new self(
            userId: (int) $data['user_id'],
            title: (string) $data['title'],
            amount: (string) $data['amount'],
            receivedAt: self::carbon($data, 'received_at') ?? now(),
            description: self::string($data, 'description'),
            isRecurring: $isRecurring,
            dayOfMonth: $isRecurring ? $dayOfMonth : null,
            isActive: self::bool($data, 'is_active', true),
        );
    }

    public function toModelAttributes(bool $forUpdate = false): array
    {
        $attributes = [
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount,
            'received_at' => $this->receivedAt,
            'is_recurring' => $this->isRecurring,
            'day_of_month' => $this->dayOfMonth,
            'is_active' => $this->isActive,
        ];

        if (! $forUpdate) {
            $attributes['user_id'] = $this->userId;
        }

        return $attributes;
    }
}
