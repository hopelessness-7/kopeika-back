<?php

namespace App\Domain\Enums;

use App\Domain\Enums\Concerns\EnumValues;

enum TransactionDirection: string
{
    use EnumValues;

    case Out = 'out';

    case In = 'in';
}
