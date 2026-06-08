<?php

namespace App\DTO\Saving;

use App\DTO\Concerns\MapsFromArray;
use App\DTO\Contracts\DataTransferObject;

readonly class SavingData implements DataTransferObject
{
    use MapsFromArray;

    public function __construct(
        public int $userId,
        public string $title,
        public string $bank,
        public string $balance,
        public string $monthlyContribution,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            userId: (int) $data['user_id'],
            title: (string) $data['title'],
            bank: (string) $data['bank'],
            balance: (string) $data['balance'],
            monthlyContribution: (string) $data['monthly_contribution'],
        );
    }

    public function toModelAttributes(bool $forUpdate = false): array
    {
        $attributes = [
            'title' => $this->title,
            'bank' => $this->bank,
            'balance' => $this->balance,
            'monthly_contribution' => $this->monthlyContribution,
        ];

        if (! $forUpdate) {
            $attributes['user_id'] = $this->userId;
        }

        return $attributes;
    }
}
