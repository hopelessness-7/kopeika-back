<?php



namespace App\Application\Finance;



use App\Application\Support\Money;



final class SafeToSpendCalculator

{

    /** free = balance − obligations_until(anchor) − buffer */

    public function calculate(

        string $balance,

        string $obligationsUntil,

        int $daysRemaining,

        ?string $buffer = null,

    ): array {

        $free = Money::sub($balance, $obligationsUntil);



        if ($buffer !== null && Money::compare($buffer, '0') > 0) {

            $free = Money::sub($free, $buffer);

        }



        if (Money::compare($free, '0') < 0) {

            $free = '0.00';

        }



        $dailyLimit = bcdiv($free, (string) $daysRemaining, 2);



        return [

            'free' => $free,

            'daily_limit' => $dailyLimit,

        ];

    }

}

