<?php

namespace Tests\Unit;

use App\Application\Finance\ObligationSchedule;
use App\Domain\Enums\ObligationType;
use App\Models\Obligation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ObligationScheduleTest extends TestCase
{
    public function test_find_next_unpaid_skips_marked_due_dates(): void
    {
        $schedule = new ObligationSchedule();
        $today = Carbon::parse('2026-06-08')->startOfDay();

        $obligation = new Obligation([
            'title' => 'Test',
            'type' => ObligationType::Rent,
            'payment_amount' => '1000.00',
            'payment_day' => 5,
            'is_active' => true,
        ]);
        $obligation->id = 1;

        $paidMap = [
            1 => ['2026-07-05'],
        ];

        $next = $schedule->findNextUnpaid(Collection::make([$obligation]), $today, $paidMap);

        $this->assertNotNull($next);
        $this->assertSame('2026-08-05', $next['due_date']->toDateString());
    }
}
