<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpendPeriodSummary extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'bank_import_id',
        'period_from',
        'period_to',
        'planned_spend',
        'actual_spend',
        'delta',
        'daily_limit_after',
        'limit_valid_until',
    ];

    protected function casts(): array
    {
        return [
            'period_from' => 'date',
            'period_to' => 'date',
            'planned_spend' => 'decimal:2',
            'actual_spend' => 'decimal:2',
            'delta' => 'decimal:2',
            'daily_limit_after' => 'decimal:2',
            'limit_valid_until' => 'date',
        ];
    }

    public function bankImport(): BelongsTo
    {
        return $this->belongsTo(BankImport::class);
    }
}
