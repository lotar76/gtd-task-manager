<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'default_workspace_id',
        'chat_id',
        'link_token',
        'is_active',
        'pending_task_text',
        'morning_digest_time',
        'reminder_minutes_before',
        'notify_overdue',
        'notify_morning_digest',
        'notify_reminders',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'notify_overdue' => 'boolean',
        'notify_morning_digest' => 'boolean',
        'notify_reminders' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function defaultWorkspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'default_workspace_id');
    }
}
