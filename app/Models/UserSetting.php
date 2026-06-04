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
        'notification_mode',
    ];

    protected function casts(): array
    {
        return [
            'last_check_in_at' => 'datetime',
            'notification_mode' => NotificationMode::class,
        ];
    }
}
