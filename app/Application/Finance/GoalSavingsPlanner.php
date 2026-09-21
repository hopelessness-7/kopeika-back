<?php

namespace App\Application\Finance;

use App\Application\Support\Money;
use App\Models\Goal;

final class GoalSavingsPlanner
{
    public function plan(Goal $goal, ?string $freeAfterObligations, ?int $daysRemaining): array
    {
        $remaining = Money::sub((string) $goal->target_amount, (string) $goal->saved_amount);
        $progressPercent = null;

        if (Money::compare((string) $goal->target_amount, '0') > 0) {
            $saved = Money::sub((string) $goal->target_amount, $remaining);
            $progressPercent = (int) min(100, round(
                (float) bcdiv($saved, (string) $goal->target_amount, 4) * 100
            ));
        }

        if (Money::compare($remaining, '0') <= 0) {
            return [
                'goal_id' => $goal->id,
                'remaining' => 0,
                'progress_percent' => 100,
                'is_complete' => true,
                'comfortable' => null,
                'faster' => null,
                'target_date_plan' => null,
            ];
        }

        $days = max(1, $daysRemaining ?? 30);
        $free = $freeAfterObligations ?? '0.00';
        $monthlySurplus = bcmul(bcdiv($free, (string) $days, 4), '30', 2);

        $comfortMonthly = bcmul($monthlySurplus, '0.15', 2);
        $fasterMonthly = bcmul($monthlySurplus, '0.30', 2);

        $comfortable = $this->monthsPlan($remaining, $comfortMonthly);
        $faster = $this->monthsPlan($remaining, $fasterMonthly);

        $targetDatePlan = null;
        if ($goal->target_date !== null) {
            $monthsLeft = max(1, (int) now()->startOfDay()->diffInMonths($goal->target_date, false) + 1);
            $required = bcdiv($remaining, (string) $monthsLeft, 2);
            $targetDatePlan = [
                'months_left' => $monthsLeft,
                'monthly_required' => Money::toApiNumber($required),
            ];
        }

        return [
            'goal_id' => $goal->id,
            'remaining' => Money::toApiNumber($remaining),
            'progress_percent' => $progressPercent,
            'is_complete' => false,
            'comfortable' => $comfortable,
            'faster' => $faster,
            'target_date_plan' => $targetDatePlan,
        ];
    }

    private function monthsPlan(string $remaining, string $monthlyAmount): ?array
    {
        if (Money::compare($monthlyAmount, '0') <= 0) {
            return null;
        }

        $months = (int) ceil((float) bcdiv($remaining, $monthlyAmount, 4));

        return [
            'monthly_amount' => Money::toApiNumber($monthlyAmount),
            'months_to_goal' => $months,
        ];
    }
}
