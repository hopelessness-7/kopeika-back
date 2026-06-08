<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Contracts\Repositories\ObligationPaymentRepositoryInterface;
use App\Domain\Enums\ObligationPaymentStatus;
use App\DTO\Obligation\ObligationPaymentData;
use App\Models\ObligationPayment;
use Illuminate\Support\Collection;

final class EloquentObligationPaymentRepository implements ObligationPaymentRepositoryInterface
{
    public function listForObligation(int $userId, int $obligationId): Collection
    {
        return ObligationPayment::query()
            ->forUser($userId)
            ->where('obligation_id', $obligationId)
            ->orderByDesc('paid_at')
            ->orderByDesc('due_date')
            ->orderByDesc('id')
            ->get();
    }

    public function findForUserOrFail(int $userId, int $id): ObligationPayment
    {
        return ObligationPayment::query()
            ->forUser($userId)
            ->whereKey($id)
            ->firstOrFail();
    }

    public function create(ObligationPaymentData $data): ObligationPayment
    {
        return ObligationPayment::query()->create($data->toModelAttributes());
    }

    public function delete(ObligationPayment $payment): void
    {
        $payment->delete();
    }

    public function paidDueDateKeysByObligationForUser(int $userId): array
    {
        $rows = ObligationPayment::query()
            ->forUser($userId)
            ->where('status', ObligationPaymentStatus::Paid)
            ->get(['obligation_id', 'due_date']);

        $map = [];

        foreach ($rows as $row) {
            $map[$row->obligation_id][] = $row->due_date->toDateString();
        }

        return $map;
    }
}
