<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientInteraction extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'type',
        'subject',
        'content',
        'interaction_date',
    ];

    protected $casts = [
        'interaction_date' => 'datetime',
    ];

    /* ===== Relations ===== */

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ===== Accessors ===== */

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'note' => '備註',
            'call' => '電話',
            'email' => '郵件',
            'meeting' => '會議',
            'other' => '其他',
            default => '未知',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'note' => 'cil-notes',
            'call' => 'cil-phone',
            'email' => 'cil-envelope-closed',
            'meeting' => 'cil-people',
            'other' => 'cil-comment-square',
            default => 'cil-comment-square',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'note' => 'secondary',
            'call' => 'success',
            'email' => 'info',
            'meeting' => 'primary',
            'other' => 'warning',
            default => 'secondary',
        };
    }
}
