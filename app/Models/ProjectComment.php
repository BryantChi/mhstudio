<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectComment extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'content',
        'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    /* ===== Relations ===== */

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ===== Scopes ===== */

    /**
     * 過濾非內部留言（客戶可見）
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_internal', false);
    }

    /**
     * 僅內部留言
     */
    public function scopeInternal(Builder $query): void
    {
        $query->where('is_internal', true);
    }
}
