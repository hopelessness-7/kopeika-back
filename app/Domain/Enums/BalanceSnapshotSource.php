<?php

namespace App\Domain\Enums;

use App\Domain\Enums\Concerns\EnumValues;

enum BalanceSnapshotSource: string
{
    use EnumValues;

    case Manual = 'manual';
    case Import = 'import';
    case CheckIn = 'check_in';
}
