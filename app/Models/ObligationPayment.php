<?php

namespace App\Models;

use App\Domain\Enums\ObligationPaymentStatus;
use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObligationPayment extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'obligation_id',
        'due_date',
        'amount',
        'status',
        'paid_at',
        'note',
    ];

    protected static function booted(): void
    {
        static::creating(function (ObligationPayment $payment): void {
            if ($payment->user_id !== null) {
                return;
            }

            $obligationId = $payment->obligation_id;

            if ($obligationId === null) {
                return;
            }

            $payment->user_id = Obligation::query()
                ->whereKey($obligationId)
                ->value('user_id');
        });
    }

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'status' => ObligationPaymentStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function obligation(): BelongsTo
    {
        return $this->belongsTo(Obligation::class);
    }
}
