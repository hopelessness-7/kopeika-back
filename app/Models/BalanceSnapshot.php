<?php

namespace App\Models;

use App\Domain\Enums\BalanceSnapshotSource;
use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalanceSnapshot extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'bank_import_id',
        'amount',
        'source',
        'recorded_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'source' => BalanceSnapshotSource::class,
            'recorded_at' => 'datetime',
        ];
    }

    public function bankImport(): BelongsTo
    {
        return $this->belongsTo(BankImport::class);
    }
}
