<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'title',
        'bank',
        'balance',
        'monthly_contribution',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'monthly_contribution' => 'decimal:2',
        ];
    }
}
