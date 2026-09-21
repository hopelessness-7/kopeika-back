<?php

namespace App\DTO\Goal;

use App\DTO\Concerns\MapsFromArray;
use App\DTO\Contracts\DataTransferObject;
use Carbon\CarbonInterface;

readonly class GoalData implements DataTransferObject
{
    use MapsFromArray;

    public function __construct(
        public int $userId,
        public string $title,
        public string $targetAmount,
        public string $savedAmount = '0.00',
        public ?CarbonInterface $targetDate = null,
        public bool $isActive = true,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            userId: (int) $data['user_id'],
            title: (string) $data['title'],
            targetAmount: (string) $data['target_amount'],
            savedAmount: isset($data['saved_amount']) ? (string) $data['saved_amount'] : '0.00',
            targetDate: self::carbon($data, 'target_date'),
            isActive: self::bool($data, 'is_active', true),
        );
    }

    public function toModelAttributes(bool $forUpdate = false): array
    {
        $attributes = [
            'title' => $this->title,
            'target_amount' => $this->targetAmount,
            'saved_amount' => $this->savedAmount,
            'target_date' => $this->targetDate,
            'is_active' => $this->isActive,
        ];

        if (! $forUpdate) {
            $attributes['user_id'] = $this->userId;
        }

        return $attributes;
    }
}
