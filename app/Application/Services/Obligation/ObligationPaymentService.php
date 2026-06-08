<?php

namespace App\Application\Services\Obligation;

use App\Application\Auth\CurrentUserResolver;
use App\Application\Services\BaseService;
use App\Application\Support\Money;
use App\Domain\Contracts\Repositories\ObligationPaymentRepositoryInterface;
use App\Domain\Contracts\Repositories\ObligationRepositoryInterface;
use App\Domain\Enums\ObligationPaymentStatus;
use App\DTO\Obligation\ObligationPaymentData;
use App\Models\Obligation;
use App\Models\ObligationPayment;
use Illuminate\Support\Facades\DB;

final class ObligationPaymentService extends BaseService
{
    public function __construct(
        CurrentUserResolver $currentUser,
        private readonly ObligationRepositoryInterface $obligations,
        private readonly ObligationPaymentRepositoryInterface $payments,
    ) {
        parent::__construct($currentUser);
    }

    public function list(int $obligationId, ?int $userId = null): array
    {
        $userId ??= $this->currentUserId();
        $this->obligations->findForUserOrFail($userId, $obligationId);

        return $this->payments->listForObligation($userId, $obligationId)->all();
    }

    public function store(int $obligationId, ObligationPaymentData $data): ObligationPayment
    {
        return DB::transaction(function () use ($obligationId, $data) {
            $obligation = $this->obligations->findForUserOrFail($data->userId, $obligationId);

            $payment = $this->payments->create($data);

            if ($payment->status === ObligationPaymentStatus::Paid) {
                $this->applyPaymentToBalance($obligation, (string) $payment->amount, sign: -1);
            }

            return $payment->refresh();
        });
    }

    public function destroy(int $obligationId, int $paymentId, ?int $userId = null): void
    {
        DB::transaction(function () use ($obligationId, $paymentId, $userId) {
            $userId ??= $this->currentUserId();
            $obligation = $this->obligations->findForUserOrFail($userId, $obligationId);
            $payment = $this->payments->findForUserOrFail($userId, $paymentId);

            if ((int) $payment->obligation_id !== $obligationId) {
                abort(404);
            }

            if ($payment->status === ObligationPaymentStatus::Paid) {
                $this->applyPaymentToBalance($obligation, (string) $payment->amount, sign: 1);
            }

            $this->payments->delete($payment);
        });
    }

    public function close(int $obligationId, ?int $userId = null): Obligation
    {
        $userId ??= $this->currentUserId();
        $obligation = $this->obligations->findForUserOrFail($userId, $obligationId);
        $obligation->is_active = false;
        $obligation->ends_at ??= now()->toDateString();
        $obligation->remaining_amount = '0.00';

        return $this->obligations->save($obligation);
    }

    public function reopen(int $obligationId, ?int $userId = null): Obligation
    {
        $userId ??= $this->currentUserId();
        $obligation = $this->obligations->findForUserOrFail($userId, $obligationId);
        $obligation->is_active = true;

        return $this->obligations->save($obligation);
    }

    private function applyPaymentToBalance(Obligation $obligation, string $amount, int $sign): void
    {
        if ($obligation->remaining_amount === null) {
            return;
        }

        $delta = $sign < 0
            ? Money::sub((string) $obligation->remaining_amount, $amount)
            : Money::add((string) $obligation->remaining_amount, $amount);

        if ($sign < 0 && Money::compare($delta, '0') < 0) {
            $delta = '0.00';
        }

        $obligation->remaining_amount = $delta;

        if ($sign > 0 && ! $obligation->is_active && Money::compare($delta, '0') > 0) {
            $obligation->is_active = true;
            $obligation->ends_at = null;
        }

        $this->obligations->save($obligation);
    }
}
