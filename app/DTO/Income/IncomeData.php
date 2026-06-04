<?php

namespace App\DTO\Income;

use App\DTO\Concerns\ArrayAccessible;
use App\DTO\Concerns\MapsFromArray;
use App\DTO\Contracts\DataTransferObject;
use Carbon\CarbonInterface;

readonly class IncomeData implements DataTransferObject
{
    use ArrayAccessible;
    use MapsFromArray;

    public function __construct(
        public int $userId,
        public string $title,
        public string $amount,
        public CarbonInterface $receivedAt,
        public ?string $description = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            userId: (int) $data['user_id'],
            title: (string) $data['title'],
            amount: (string) $data['amount'],
            receivedAt: self::carbon($data, 'received_at') ?? now(),
            description: self::string($data, 'description'),
        );
    }

    public function toModelAttributes(bool $forUpdate = false): array
    {
        $attributes = [
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount,
            'received_at' => $this->receivedAt,
        ];

        if (! $forUpdate) {
            $attributes['user_id'] = $this->userId;
        }

        return $attributes;
    }
}
