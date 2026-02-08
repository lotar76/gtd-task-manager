<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramSubscription extends Model
{
    protected $fillable = [
        'workspace_id',
        'user_id',
        'chat_id',
        'link_token',
        'is_active',
        'morning_digest_time',
        'reminder_minutes_before',
        'notify_overdue',
        'notify_morning_digest',
        'notify_reminders',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'notify_overdue' => 'boolean',
            'notify_morning_digest' => 'boolean',
            'notify_reminders' => 'boolean',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
