<?php

namespace App\Application\Finance;

use App\Application\Support\Money;

/**
 * Расчёт срока закрытия долга с учётом процентов.
 *
 * Ежемесячная капитализация: на остаток начисляется r = годовая_ставка / 12,
 * затем списывается фиксированный платёж до полного погашения.
 */
final class DebtPayoffCalculator
{
    private const SAFETY_LIMIT_MONTHS = 1200;

    public function calculate(
        string $remaining,
        string $monthlyPayment,
        ?float $annualInterestRate,
    ): DebtPayoffEstimate {
        $remainingFloat = (float) $remaining;
        $paymentFloat = (float) $monthlyPayment;

        if ($remainingFloat <= 0 || $paymentFloat <= 0) {
            return DebtPayoffEstimate::immediate();
        }

        $monthlyRate = $annualInterestRate !== null && $annualInterestRate > 0
            ? $annualInterestRate / 100 / 12
            : 0.0;

        if ($monthlyRate === 0.0) {
            $months = (int) ceil($remainingFloat / $paymentFloat);
            $totalPaid = $paymentFloat * $months;

            return new DebtPayoffEstimate(
                months: $months,
                totalToPay: Money::normalize((string) $totalPaid),
                totalInterest: '0.00',
                neverCloses: false,
            );
        }

        $monthlyInterestOnDebt = $remainingFloat * $monthlyRate;
        if ($paymentFloat <= $monthlyInterestOnDebt) {
            return new DebtPayoffEstimate(
                months: null,
                totalToPay: null,
                totalInterest: null,
                neverCloses: true,
                minPayment: Money::normalize((string) ($monthlyInterestOnDebt + 0.01)),
            );
        }

        $balance = $remainingFloat;
        $months = 0;
        $totalPaid = 0.0;

        while ($balance > 0 && $months < self::SAFETY_LIMIT_MONTHS) {
            $interest = $balance * $monthlyRate;
            $balance += $interest;
            $payment = min($paymentFloat, $balance);
            $balance -= $payment;
            $totalPaid += $payment;
            $months++;
        }

        if ($months >= self::SAFETY_LIMIT_MONTHS && $balance > 0.01) {
            return new DebtPayoffEstimate(
                months: null,
                totalToPay: null,
                totalInterest: null,
                neverCloses: true,
            );
        }

        $interestTotal = max(0.0, $totalPaid - $remainingFloat);

        return new DebtPayoffEstimate(
            months: $months,
            totalToPay: Money::normalize((string) $totalPaid),
            totalInterest: Money::normalize((string) $interestTotal),
            neverCloses: false,
        );
    }
}
