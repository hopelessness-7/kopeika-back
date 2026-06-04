<?php

namespace App\DTO\Obligation;

use App\Domain\Enums\ObligationType;
use App\DTO\Concerns\ArrayAccessible;
use App\DTO\Concerns\MapsFromArray;
use App\DTO\Contracts\DataTransferObject;
use Carbon\CarbonInterface;

readonly class ObligationData implements DataTransferObject
{
    use ArrayAccessible;
    use MapsFromArray;

    public function __construct(
        public int $userId,
        public string $title,
        public ObligationType $type,
        public string $paymentAmount,
        public int $paymentDay,
        public ?string $remainingAmount = null,
        public ?string $totalAmount = null,
        public ?string $interestRate = null,
        public ?string $lender = null,
        public ?string $note = null,
        public bool $isActive = true,
        public ?CarbonInterface $startsAt = null,
        public ?CarbonInterface $endsAt = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            userId: (int) $data['user_id'],
            title: (string) $data['title'],
            type: ObligationType::from($data['type']),
            paymentAmount: (string) $data['payment_amount'],
            paymentDay: (int) $data['payment_day'],
            remainingAmount: self::string($data, 'remaining_amount'),
            totalAmount: self::string($data, 'total_amount'),
            interestRate: self::string($data, 'interest_rate'),
            lender: self::string($data, 'lender'),
            note: self::string($data, 'note'),
            isActive: self::bool($data, 'is_active', true),
            startsAt: self::carbon($data, 'starts_at'),
            endsAt: self::carbon($data, 'ends_at'),
        );
    }

    public function toModelAttributes(bool $forUpdate = false): array
    {
        $attributes = [
            'title' => $this->title,
            'type' => $this->type,
            'payment_amount' => $this->paymentAmount,
            'payment_day' => $this->paymentDay,
            'remaining_amount' => $this->remainingAmount,
            'total_amount' => $this->totalAmount,
            'interest_rate' => $this->interestRate,
            'lender' => $this->lender,
            'note' => $this->note,
            'is_active' => $this->isActive,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
        ];

        if (! $forUpdate) {
            $attributes['user_id'] = $this->userId;
        }

        return $attributes;
    }
}
