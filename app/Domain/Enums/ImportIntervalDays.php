<?php

namespace App\Domain\Enums;

use App\Domain\Enums\Concerns\EnumValues;
use InvalidArgumentException;

enum ImportIntervalDays: int
{
    use EnumValues;

    case Weekly = 7;
    case TenDays = 10;
    case Biweekly = 14;

    public static function fromDays(int $days): self
    {
        return self::tryFrom($days)
            ?? throw new InvalidArgumentException("Unsupported import interval: {$days}");
    }
}
