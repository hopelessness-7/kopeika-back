<?php

namespace App\Models;

use App\Domain\Enums\BankImportStatus;
use App\Domain\Enums\BankProvider;
use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankImport extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'bank',
        'status',
        'file_hash',
        'original_filename',
        'storage_path',
        'file_size',
        'period_from',
        'period_to',
        'error_message',
        'imported_at',
        'confirmed_at',
        'confirmed_balance',
    ];

    protected function casts(): array
    {
        return [
            'bank' => BankProvider::class,
            'status' => BankImportStatus::class,
            'period_from' => 'date',
            'period_to' => 'date',
            'imported_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'confirmed_balance' => 'decimal:2',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function balanceSnapshots(): HasMany
    {
        return $this->hasMany(BalanceSnapshot::class);
    }

    public function spendPeriodSummary(): HasOne
    {
        return $this->hasOne(SpendPeriodSummary::class);
    }
}
