<?php

namespace App\Domain\Contracts\Repositories;

use App\DTO\Obligation\ObligationPaymentData;
use App\Models\ObligationPayment;
use Illuminate\Support\Collection;

interface ObligationPaymentRepositoryInterface
{
    public function listForObligation(int $userId, int $obligationId): Collection;

    public function findForUserOrFail(int $userId, int $id): ObligationPayment;

    public function create(ObligationPaymentData $data): ObligationPayment;

    public function delete(ObligationPayment $payment): void;

    /**
     * @return array<int, list<string>> obligation_id => due_date (Y-m-d)
     */
    public function paidDueDateKeysByObligationForUser(int $userId): array;
}
