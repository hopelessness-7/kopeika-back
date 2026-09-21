<?php

namespace App\Models;

use App\Domain\Enums\NotificationMode;
use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'last_check_in_at',
        'check_in_streak_weeks',
        'notification_mode',
        'buffer_amount',
    ];

    protected function casts(): array
    {
        return [
            'last_check_in_at' => 'datetime',
            'notification_mode' => NotificationMode::class,
            'buffer_amount' => 'decimal:2',
        ];
    }
}
