<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifeSphereImage extends Model
{
    protected $fillable = [
        'life_sphere_id',
        'path',
        'position',
    ];

    public function sphere(): BelongsTo
    {
        return $this->belongsTo(LifeSphere::class, 'life_sphere_id');
    }
}
