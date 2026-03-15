<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'project_type',
        'budget',
        'timeline',
        'message',
        'status',
        'admin_notes',
        'read_at',
        'replied_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
    ];

    /* ===== Scopes ===== */

    public function scopeUnread(Builder $query): void
    {
        $query->where('status', 'unread');
    }

    public function scopeRead(Builder $query): void
    {
        $query->where('status', 'read');
    }

    public function scopeReplied(Builder $query): void
    {
        $query->where('status', 'replied');
    }

    public function scopeArchived(Builder $query): void
    {
        $query->where('status', 'archived');
    }

    /* ===== Methods ===== */

    public function markAsRead(): void
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    public function markAsReplied(): void
    {
        $this->update([
            'status' => 'replied',
            'replied_at' => now(),
        ]);
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    /* ===== Accessors ===== */

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'unread' => 'danger',
            'read' => 'info',
            'replied' => 'success',
            'archived' => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'unread' => '未讀',
            'read' => '已讀',
            'replied' => '已回覆',
            'archived' => '已封存',
            default => '未知',
        };
    }
}
