<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'amount',
        'received_at',
        'is_recurring',
        'day_of_month',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'received_at' => 'date',
            'is_recurring' => 'boolean',
            'day_of_month' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
