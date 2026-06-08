<?php

namespace App\DTO\Obligation;

use App\Domain\Enums\ObligationPaymentStatus;
use App\DTO\Concerns\MapsFromArray;
use App\DTO\Contracts\DataTransferObject;
use Carbon\CarbonInterface;

readonly class ObligationPaymentData implements DataTransferObject
{
    use MapsFromArray;

    public function __construct(
        public int $userId,
        public int $obligationId,
        public string $amount,
        public CarbonInterface $dueDate,
        public ObligationPaymentStatus $status,
        public ?CarbonInterface $paidAt,
        public ?string $note = null,
    ) {}

    public static function fromArray(array $data): static
    {
        $status = isset($data['status'])
            ? ObligationPaymentStatus::from($data['status'])
            : ObligationPaymentStatus::Paid;

        $paidAt = self::carbon($data, 'paid_at');

        if ($status === ObligationPaymentStatus::Paid && $paidAt === null) {
            $paidAt = now();
        }

        return new self(
            userId: (int) $data['user_id'],
            obligationId: (int) $data['obligation_id'],
            amount: (string) $data['amount'],
            dueDate: self::carbon($data, 'due_date') ?? $paidAt ?? now(),
            status: $status,
            paidAt: $paidAt,
            note: self::string($data, 'note'),
        );
    }

    public function toModelAttributes(bool $forUpdate = false): array
    {
        $attributes = [
            'amount' => $this->amount,
            'due_date' => $this->dueDate,
            'status' => $this->status,
            'paid_at' => $this->paidAt,
            'note' => $this->note,
        ];

        if (! $forUpdate) {
            $attributes['user_id'] = $this->userId;
            $attributes['obligation_id'] = $this->obligationId;
        }

        return $attributes;
    }
}
