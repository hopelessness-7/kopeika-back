<?php

namespace App\Http\Resources;

use App\Application\Finance\ObligationProgress;
use App\Application\Finance\ObligationSchedule;
use App\Application\Support\Money;
use App\Domain\Contracts\Repositories\ObligationPaymentRepositoryInterface;
use App\Domain\Enums\ObligationPaymentStatus;
use App\Models\ObligationPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/** @mixin \App\Models\Obligation */
class ObligationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $schedule = app(ObligationSchedule::class);
        $today = now();
        $paidDueDates = $this->paidDueDateKeys();
        $nextPayment = $this->is_active
            ? $schedule->nextUnpaidPaymentOnOrAfter($this->resource, $today, $paidDueDates)
            : null;

        $progressData = ObligationProgress::calculate(
            $this->total_amount !== null ? (string) $this->total_amount : null,
            $this->remaining_amount !== null ? (string) $this->remaining_amount : null,
        );

        $debtTypes = ['loan', 'installment', 'personal_debt'];
        $isDebt = in_array($this->type->value, $debtTypes, true);
        $remaining = $this->remaining_amount !== null ? (string) $this->remaining_amount : null;

        $base = [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type->value,
            'payment_amount' => Money::toApiNumber((string) $this->payment_amount),
            'payment_day' => $this->payment_day,
            'remaining_amount' => $remaining !== null
                ? Money::toApiNumber($remaining)
                : null,
            'total_amount' => $this->total_amount !== null
                ? Money::toApiNumber((string) $this->total_amount)
                : null,
            'interest_rate' => $this->interest_rate !== null ? (float) $this->interest_rate : null,
            'lender' => $this->lender,
            'note' => $this->note,
            'is_active' => (bool) $this->is_active,
            'starts_at' => $this->starts_at?->toDateString(),
            'ends_at' => $this->ends_at?->toDateString(),
            'next_payment_date' => $nextPayment?->toDateString(),
            'progress_percent' => $progressData['progress_percent'] ?? null,
            'paid_amount' => isset($progressData['paid_amount'])
                ? Money::toApiNumber($progressData['paid_amount'])
                : null,
            'progress_basis' => $progressData['basis'] ?? null,
            'needs_close' => $this->is_active
                && $isDebt
                && $remaining !== null
                && Money::compare($remaining, '0') <= 0,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];

        if ($request->boolean('with_summary')) {
            $base['summary'] = $this->buildSummary($progressData);
        }

        return $base;
    }

    /**
     * @return list<string>
     */
    private function paidDueDateKeys(): array
    {
        $repo = app(ObligationPaymentRepositoryInterface::class);
        $userId = (int) $this->user_id;
        $obligationId = (int) $this->id;

        return $repo->listForObligation($userId, $obligationId)
            ->where('status', ObligationPaymentStatus::Paid)
            ->map(fn (ObligationPayment $payment) => $payment->due_date->toDateString())
            ->values()
            ->all();
    }

    /**
     * @param  array{progress_percent: float, paid_amount: string, basis: string}|null  $progressData
     */
    private function buildSummary(?array $progressData): array
    {
        $repo = app(ObligationPaymentRepositoryInterface::class);
        $userId = (int) $this->user_id;
        $obligationId = (int) $this->id;

        /** @var Collection<int, ObligationPayment> $payments */
        $payments = $repo->listForObligation($userId, $obligationId);
        $paid = $payments->where('status', ObligationPaymentStatus::Paid);

        $today = now();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $yearStart = $today->copy()->startOfYear();

        $totalPaid = '0.00';
        $paidThisMonth = '0.00';
        $paidThisYear = '0.00';
        $monthlyBuckets = [];

        foreach ($paid as $payment) {
            $amount = (string) $payment->amount;
            $totalPaid = Money::add($totalPaid, $amount);

            $paidAt = $payment->paid_at ?? $payment->due_date;
            if ($paidAt === null) {
                continue;
            }

            if ($paidAt->between($monthStart, $monthEnd)) {
                $paidThisMonth = Money::add($paidThisMonth, $amount);
            }

            if ($paidAt->greaterThanOrEqualTo($yearStart)) {
                $paidThisYear = Money::add($paidThisYear, $amount);
            }

            $bucket = $paidAt->format('Y-m');
            $monthlyBuckets[$bucket] = Money::add($monthlyBuckets[$bucket] ?? '0.00', $amount);
        }

        krsort($monthlyBuckets);
        $monthly = [];
        foreach ($monthlyBuckets as $key => $sum) {
            $monthly[] = [
                'month' => $key,
                'total' => Money::toApiNumber($sum),
            ];
        }

        $progressResult = ObligationProgress::calculate(
            $this->total_amount !== null ? (string) $this->total_amount : null,
            $this->remaining_amount !== null ? (string) $this->remaining_amount : null,
            $totalPaid,
        );

        return [
            'total_paid' => Money::toApiNumber($totalPaid),
            'paid_this_month' => Money::toApiNumber($paidThisMonth),
            'paid_this_year' => Money::toApiNumber($paidThisYear),
            'payments_count' => $paid->count(),
            'last_paid_at' => $paid->first()?->paid_at?->toIso8601String(),
            'progress_percent' => $progressResult['progress_percent'] ?? null,
            'paid_amount' => isset($progressResult['paid_amount'])
                ? Money::toApiNumber($progressResult['paid_amount'])
                : null,
            'progress_basis' => $progressResult['basis'] ?? null,
            'monthly' => array_slice($monthly, 0, 12),
        ];
    }
}
