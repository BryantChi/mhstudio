<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'task_id',
        'description',
        'started_at',
        'ended_at',
        'duration_minutes',
        'is_billable',
        'hourly_rate',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_billable' => 'boolean',
        'hourly_rate' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($entry) {
            // 自動計算時長
            if ($entry->started_at && $entry->ended_at && empty($entry->duration_minutes)) {
                $entry->duration_minutes = (int) $entry->started_at->diffInMinutes($entry->ended_at);
            }
        });
    }

    /* ===== Relations ===== */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /* ===== Scopes ===== */

    public function scopeBillable(Builder $query): void
    {
        $query->where('is_billable', true);
    }

    public function scopeRunning(Builder $query): void
    {
        $query->whereNull('ended_at');
    }

    public function scopeThisWeek(Builder $query): void
    {
        $query->whereBetween('started_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeThisMonth(Builder $query): void
    {
        $query->whereBetween('started_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);
    }

    public function scopeByUser(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /* ===== Accessors ===== */

    public function getIsRunningAttribute(): bool
    {
        return $this->started_at && !$this->ended_at;
    }

    public function getDurationFormattedAttribute(): string
    {
        if (!$this->duration_minutes) {
            if ($this->is_running) {
                $minutes = (int) $this->started_at->diffInMinutes(now());
            } else {
                return '0:00';
            }
        } else {
            $minutes = $this->duration_minutes;
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return sprintf('%d:%02d', $hours, $mins);
    }

    public function getBillableAmountAttribute(): float
    {
        if (!$this->is_billable || !$this->hourly_rate || !$this->duration_minutes) {
            return 0;
        }

        return round(($this->duration_minutes / 60) * $this->hourly_rate, 2);
    }

    /**
     * 停止計時器
     */
    public function stop(): void
    {
        $this->ended_at = now();
        $this->duration_minutes = (int) $this->started_at->diffInMinutes($this->ended_at);
        $this->save();
    }
}
