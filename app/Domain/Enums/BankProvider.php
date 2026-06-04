<?php

namespace App\Domain\Enums;

use App\Domain\Enums\Concerns\EnumValues;

enum BankProvider: string
{
    use EnumValues;

    case Sber = 'sber';
    case YandexPay = 'yandex_pay';
}
