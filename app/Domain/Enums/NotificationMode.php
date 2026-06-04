<?php

namespace App\Domain\Enums;

use App\Domain\Enums\Concerns\EnumValues;

enum NotificationMode: string
{
    use EnumValues;

    case Quiet = 'quiet';
    case Normal = 'normal';
    case PaymentsOnly = 'payments_only';
}
