<?php

namespace App\Domain\Enums;

use App\Domain\Enums\Concerns\EnumValues;

enum PrimaryAnchor: string
{
    use EnumValues;

    case Auto = 'auto';
    case Salary = 'salary';
    case Import = 'import';
}
