<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArticleAuthor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'article_folder_id',
        'workspace_id',
        'created_by',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(ArticleFolder::class, 'article_folder_id');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
