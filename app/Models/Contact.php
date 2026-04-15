<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'contact_user_id',
        'name',
        'email',
        'phone',
        'avatar',
        'notes',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contact_user_id');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_contact')
            ->withPivot('role')
            ->withTimestamps();
    }
}
