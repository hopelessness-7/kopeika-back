<?php

namespace App\Domain\Enums;

use App\Domain\Enums\Concerns\EnumValues;

enum BankImportStatus: string
{
    use EnumValues;

    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
