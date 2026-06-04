<?php

namespace App\Domain\Enums;

use App\Domain\Enums\Concerns\EnumValues;

enum ObligationType: string
{
    use EnumValues;

    case Loan = 'loan';
    case Installment = 'installment';
    case PersonalDebt = 'personal_debt';
    case Rent = 'rent';
    case Subscription = 'subscription';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Loan => 'Кредит',
            self::Installment => 'Рассрочка',
            self::PersonalDebt => 'Долг человеку',
            self::Rent => 'Аренда',
            self::Subscription => 'Подписка',
            self::Other => 'Другое',
        };
    }
}
