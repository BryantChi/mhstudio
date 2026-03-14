<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Newsletter extends Model
{
    protected $fillable = [
        'subject',
        'content',
        'status',
        'sent_count',
        'failed_count',
        'total_recipients',
        'scheduled_at',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /* ===== Relations ===== */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(NewsletterLog::class);
    }

    /* ===== Scopes ===== */

    public function scopeDraft(Builder $query): void
    {
        $query->where('status', 'draft');
    }

    public function scopeSent(Builder $query): void
    {
        $query->where('status', 'sent');
    }

    public function scopeScheduled(Builder $query): void
    {
        $query->where('status', 'scheduled');
    }
}
