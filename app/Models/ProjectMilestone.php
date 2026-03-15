<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMilestone extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'due_date',
        'completed_at',
        'order',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    /* ===== Relations ===== */

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /* ===== Scopes ===== */

    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    public function scopeInProgress(Builder $query): void
    {
        $query->where('status', 'in_progress');
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', 'completed');
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('order');
    }

    /* ===== Accessors ===== */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => '待開始',
            'in_progress' => '進行中',
            'completed' => '已完成',
            default => '未知',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'secondary',
            'in_progress' => 'info',
            'completed' => 'success',
            default => 'secondary',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->status !== 'completed'
            && $this->due_date->isPast();
    }
}
