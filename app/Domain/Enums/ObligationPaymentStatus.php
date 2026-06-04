<?php

namespace App\Domain\Enums;

use App\Domain\Enums\Concerns\EnumValues;

enum ObligationPaymentStatus: string
{
    use EnumValues;

    case Planned = 'planned';
    case Paid = 'paid';
    case Skipped = 'skipped';
}
