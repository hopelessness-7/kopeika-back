<?php

namespace App\Domain\Enums\Concerns;

trait EnumValues
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
