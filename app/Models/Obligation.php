<?php

namespace App\Models;

use App\Domain\Enums\ObligationType;
use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Obligation extends Model
{
    use BelongsToUser;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'payment_amount',
        'payment_day',
        'remaining_amount',
        'total_amount',
        'interest_rate',
        'lender',
        'note',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ObligationType::class,
            'payment_amount' => 'decimal:2',
            'payment_day' => 'integer',
            'remaining_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'is_active' => 'boolean',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ObligationPayment::class);
    }
}
