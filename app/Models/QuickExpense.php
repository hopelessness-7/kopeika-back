<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class QuickExpense extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'amount',
        'noted_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'noted_at' => 'datetime',
        ];
    }
}
