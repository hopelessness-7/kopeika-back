<?php

namespace App\Models;

use App\Domain\Enums\TransactionDirection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'bank_import_id',
        'booked_at',
        'direction',
        'amount',
        'description',
        'category_guess',
        'external_hash',
    ];

    protected function casts(): array
    {
        return [
            'booked_at' => 'datetime',
            'direction' => TransactionDirection::class,
            'amount' => 'decimal:2',
        ];
    }

    public function bankImport(): BelongsTo
    {
        return $this->belongsTo(BankImport::class);
    }

    public function signedAmount(): string
    {
        $amount = (string) $this->amount;

        if ($this->direction === TransactionDirection::Out) {
            return bcmul($amount, '-1', 2);
        }

        return $amount;
    }
}
