<?php

namespace App\DTO\CheckIn;

use App\DTO\Concerns\MapsFromArray;
use App\DTO\Contracts\DataTransferObject;

readonly class CheckInData implements DataTransferObject
{
    use MapsFromArray;

    public function __construct(
        public int $userId,
        public bool $balanceConfirmed,
        public ?string $balanceAmount = null,
        public ?string $largeExpenseAmount = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            userId: (int) $data['user_id'],
            balanceConfirmed: self::bool($data, 'balance_confirmed'),
            balanceAmount: self::string($data, 'balance_amount'),
            largeExpenseAmount: self::string($data, 'large_expense_amount'),
        );
    }
}
