<?php

namespace App\Models;

use App\Domain\Enums\PrimaryAnchor;
use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class ReconciliationSetting extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'import_interval_days',
        'last_import_at',
        'primary_anchor',
        'salary_day_of_month',
    ];

    protected function casts(): array
    {
        return [
            'import_interval_days' => 'integer',
            'last_import_at' => 'datetime',
            'primary_anchor' => PrimaryAnchor::class,
            'salary_day_of_month' => 'integer',
        ];
    }
}
