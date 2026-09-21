<?php



namespace App\Application\Finance;



use Carbon\CarbonInterface;



final class AnchorDateCalculator

{

    public function nextDayOfMonthDate(int $dayOfMonth, CarbonInterface $from): CarbonInterface

    {

        $from = $from->copy()->startOfDay();

        $candidate = $this->dayInMonth($from, $dayOfMonth);



        if ($candidate->lt($from)) {

            $nextMonth = $from->copy()->addMonthNoOverflow()->startOfMonth();



            return $this->dayInMonth($nextMonth, $dayOfMonth);

        }



        return $candidate;

    }



    /** @deprecated Use nextDayOfMonthDate() */

    public function nextSalaryDate(int $salaryDayOfMonth, CarbonInterface $from): CarbonInterface

    {

        return $this->nextDayOfMonthDate($salaryDayOfMonth, $from);

    }



    public function daysRemaining(CarbonInterface $anchorDate, CarbonInterface $from): int

    {

        $days = $from->copy()->startOfDay()->diffInDays($anchorDate->copy()->startOfDay(), false);



        return max(1, (int) $days);

    }



    private function dayInMonth(CarbonInterface $month, int $dayOfMonth): CarbonInterface

    {

        $day = min($dayOfMonth, $month->daysInMonth);



        return $month->copy()->day($day)->startOfDay();

    }

}

