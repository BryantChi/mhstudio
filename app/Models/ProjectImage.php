<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectImage extends Model
{
    protected $fillable = [
        'project_id',
        'media_item_id',
        'image_url',
        'alt_text',
        'caption',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /* ===== Relations ===== */

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function mediaItem(): BelongsTo
    {
        return $this->belongsTo(MediaItem::class);
    }

    /* ===== Scopes ===== */

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('order');
    }
}
