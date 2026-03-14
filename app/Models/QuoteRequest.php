<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class QuoteRequest extends Model
{
    protected $fillable = [
        'request_number',
        'token',
        'client_id',
        'name',
        'email',
        'company',
        'phone',
        'message',
        'project_type',
        'selected_features',
        'timeline',
        'budget',
        'estimated_min',
        'estimated_max',
        'currency',
        'status',
        'admin_notes',
        'quoted_at',
        'quote_id',
    ];

    protected $casts = [
        'selected_features' => 'array',
        'estimated_min' => 'decimal:2',
        'estimated_max' => 'decimal:2',
        'quoted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quoteRequest) {
            if (empty($quoteRequest->request_number)) {
                $quoteRequest->request_number = static::generateNumber();
            }
            if (empty($quoteRequest->token)) {
                $quoteRequest->token = Str::random(64);
            }
        });
    }

    /**
     * 自動生成報價請求編號 QR-YYYYMM-001
     */
    public static function generateNumber(): string
    {
        $prefix = 'QR-' . now()->format('Ym') . '-';
        $latest = static::where('request_number', 'like', $prefix . '%')
            ->orderByDesc('request_number')
            ->value('request_number');

        if ($latest) {
            $number = (int) substr($latest, -3) + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /* ===== Relations ===== */

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /* ===== Scopes ===== */

    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    public function scopeReviewing(Builder $query): void
    {
        $query->where('status', 'reviewing');
    }

    public function scopeQuoted(Builder $query): void
    {
        $query->where('status', 'quoted');
    }

    /* ===== Methods ===== */

    /**
     * 轉換為正式報價單
     */
    public function convertToQuote(): Quote
    {
        $quote = Quote::create([
            'client_id' => $this->client_id,
            'title' => $this->project_type . ' — 報價 (' . $this->request_number . ')',
            'description' => $this->message,
            'status' => 'draft',
            'tax_rate' => 0,
            'discount' => 0,
            'currency' => $this->currency,
            'valid_until' => now()->addDays(30),
            'notes' => '來源：網站報價請求 ' . $this->request_number,
        ]);

        $features = $this->selected_features ?? [];
        foreach ($features as $index => $feature) {
            $quote->items()->create([
                'description' => $feature['name'] ?? '功能項目',
                'quantity' => 1,
                'unit' => '項',
                'unit_price' => $feature['price_max'] ?? 0,
                'amount' => $feature['price_max'] ?? 0,
                'order' => $index,
            ]);
        }

        $quote->recalculate();

        $this->update([
            'status' => 'quoted',
            'quote_id' => $quote->id,
            'quoted_at' => now(),
        ]);

        return $quote;
    }

    /* ===== Accessors ===== */

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'reviewing' => 'info',
            'quoted' => 'primary',
            'accepted' => 'success',
            'rejected' => 'danger',
            'expired' => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => '待處理',
            'reviewing' => '審核中',
            'quoted' => '已報價',
            'accepted' => '已接受',
            'rejected' => '已拒絕',
            'expired' => '已過期',
            default => '未知',
        };
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->status === 'pending' && $this->created_at->diffInDays(now()) > 30;
    }
}
