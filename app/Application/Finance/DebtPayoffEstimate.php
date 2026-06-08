<?php

namespace App\Application\Finance;

final readonly class DebtPayoffEstimate
{
    public function __construct(
        public ?int $months,
        public ?string $totalToPay,
        public ?string $totalInterest,
        public bool $neverCloses,
        public ?string $minPayment = null,
    ) {}

    public static function immediate(): self
    {
        return new self(
            months: 0,
            totalToPay: '0.00',
            totalInterest: '0.00',
            neverCloses: false,
        );
    }
}
