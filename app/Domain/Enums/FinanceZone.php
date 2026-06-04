<?php

namespace App\Domain\Enums;

enum FinanceZone: string
{
    case Green = 'green';
    case Yellow = 'yellow';
    case Red = 'red';
}
