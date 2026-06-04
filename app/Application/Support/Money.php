<?php

namespace App\Application\Support;

final class Money
{
    public static function add(string ...$amounts): string
    {
        $sum = '0.00';

        foreach ($amounts as $amount) {
            $sum = bcadd($sum, self::normalize($amount), 2);
        }

        return $sum;
    }

    public static function sub(string $left, string $right): string
    {
        return bcsub(self::normalize($left), self::normalize($right), 2);
    }

    public static function compare(string $left, string $right): int
    {
        return bccomp(self::normalize($left), self::normalize($right), 2);
    }

    public static function isNegative(string $amount): bool
    {
        return self::compare($amount, '0') < 0;
    }

    public static function normalize(string|float|int $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    public static function toApiNumber(string $amount): float
    {
        return (float) self::normalize($amount);
    }
}
