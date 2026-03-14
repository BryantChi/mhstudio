<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'project_id',
        'assigned_to',
        'created_by',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'estimated_hours',
        'order',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'estimated_hours' => 'decimal:1',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($task) {
            if (empty($task->created_by)) {
                $task->created_by = auth()->id();
            }
        });
    }

    /* ===== Relations ===== */

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /* ===== Scopes ===== */

    public function scopeTodo(Builder $query): void
    {
        $query->where('status', 'todo');
    }

    public function scopeInProgress(Builder $query): void
    {
        $query->where('status', 'in_progress');
    }

    public function scopeInReview(Builder $query): void
    {
        $query->where('status', 'in_review');
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', 'completed');
    }

    public function scopeByPriority(Builder $query, string $priority): void
    {
        $query->where('priority', $priority);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('order');
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->where('status', '!=', 'completed');
    }

    /* ===== Accessors ===== */

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'todo' => 'secondary',
            'in_progress' => 'primary',
            'in_review' => 'info',
            'completed' => 'success',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'todo' => '待辦',
            'in_progress' => '進行中',
            'in_review' => '審核中',
            'completed' => '已完成',
            default => '未知',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'secondary',
            'medium' => 'info',
            'high' => 'warning',
            'urgent' => 'danger',
            default => 'secondary',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => '低',
            'medium' => '中',
            'high' => '高',
            'urgent' => '緊急',
            default => '未知',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && $this->status !== 'completed';
    }

    /**
     * 計算實際花費工時（分鐘）
     */
    public function getActualMinutesAttribute(): int
    {
        return $this->timeEntries()->sum('duration_minutes') ?? 0;
    }

    /**
     * 計算實際花費工時（小時）
     */
    public function getActualHoursAttribute(): float
    {
        return round($this->actual_minutes / 60, 1);
    }
}
