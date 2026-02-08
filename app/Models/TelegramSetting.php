<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class TelegramSetting extends Model
{
    protected $fillable = [
        'workspace_id',
        'bot_token',
        'bot_username',
        'webhook_secret',
        'is_active',
    ];

    protected $hidden = [
        'bot_token',
        'webhook_secret',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function setBotTokenAttribute(string $value): void
    {
        $this->attributes['bot_token'] = Crypt::encryptString($value);
    }

    public function getBotTokenAttribute(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
