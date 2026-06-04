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
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'received_at' => 'date',
        ];
    }
}
