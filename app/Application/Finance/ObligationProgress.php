<?php

namespace App\Application\Finance;

use App\Application\Support\Money;

final class ObligationProgress
{
    /**
     * @return array{progress_percent: float, paid_amount: string, basis: string}|null
     */
    public static function calculate(
        ?string $totalAmount,
        ?string $remainingAmount,
        ?string $totalPaidFromHistory = null,
    ): ?array {
        if ($totalAmount !== null
            && $remainingAmount !== null
            && Money::compare($totalAmount, '0') > 0
        ) {
            $total = (string) $totalAmount;
            $remaining = Money::normalize($remainingAmount);
            $paid = Money::sub($total, $remaining);

            if (Money::compare($paid, '0') < 0) {
                $paid = '0.00';
            }

            if (Money::compare($paid, $total) > 0) {
                $paid = $total;
            }

            $percent = round(((float) $paid) / ((float) $total) * 100, 1);

            return [
                'progress_percent' => min(100, max(0, $percent)),
                'paid_amount' => $paid,
                'basis' => 'balance',
            ];
        }

        if ($totalAmount !== null
            && $totalPaidFromHistory !== null
            && Money::compare($totalAmount, '0') > 0
        ) {
            $percent = round(((float) $totalPaidFromHistory) / ((float) $totalAmount) * 100, 1);

            return [
                'progress_percent' => min(100, max(0, $percent)),
                'paid_amount' => Money::normalize($totalPaidFromHistory),
                'basis' => 'payments',
            ];
        }

        return null;
    }
}
