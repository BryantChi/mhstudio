<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'company',
        'address',
        'website',
        'industry',
        'source',
        'status',
        'tier',
        'notes',
        'avatar',
        'tags',
        'total_revenue',
        'user_id',
    ];

    protected $casts = [
        'tags' => 'array',
        'total_revenue' => 'decimal:2',
    ];

    /* ===== Relations ===== */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(ClientInteraction::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function quoteRequests(): HasMany
    {
        return $this->hasMany(QuoteRequest::class);
    }

    /* ===== Scopes ===== */

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function scopeLeads(Builder $query): void
    {
        $query->where('status', 'lead');
    }

    public function scopeByTier(Builder $query, string $tier): void
    {
        $query->where('tier', $tier);
    }

    /* ===== Accessors ===== */

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'lead' => 'info',
            'active' => 'success',
            'inactive' => 'warning',
            'archived' => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'lead' => '潛在客戶',
            'active' => '活躍',
            'inactive' => '不活躍',
            'archived' => '已歸檔',
            default => '未知',
        };
    }

    public function getTierColorAttribute(): string
    {
        return match ($this->tier) {
            'vip' => 'danger',
            'premium' => 'warning',
            'standard' => 'secondary',
            default => 'secondary',
        };
    }

    public function getTierLabelAttribute(): string
    {
        return match ($this->tier) {
            'vip' => 'VIP',
            'premium' => '高級',
            'standard' => '標準',
            default => '未知',
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'website' => '網站',
            'referral' => '推薦',
            'social' => '社群媒體',
            'cold_outreach' => '主動開發',
            'other' => '其他',
            default => '未知',
        };
    }

    /**
     * 重新計算累計營收
     */
    public function recalculateRevenue(): void
    {
        $this->total_revenue = $this->invoices()
            ->where('status', 'paid')
            ->sum('total');
        $this->save();
    }
}
