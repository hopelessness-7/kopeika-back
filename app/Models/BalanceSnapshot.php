<?php

namespace App\Models;

use App\Domain\Enums\BalanceSnapshotSource;
use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class BalanceSnapshot extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
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
}
