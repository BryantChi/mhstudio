<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quote extends Model
{
    protected $fillable = [
        'quote_number',
        'client_id',
        'project_id',
        'title',
        'description',
        'status',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount',
        'total',
        'currency',
        'valid_until',
        'notes',
        'accepted_at',
        'rejected_at',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'valid_until' => 'date',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            if (empty($quote->quote_number)) {
                $quote->quote_number = static::generateNumber();
            }
            if (empty($quote->created_by)) {
                $quote->created_by = auth()->id();
            }
        });
    }

    /**
     * 自動生成報價編號 QUO-YYYYMM-001
     */
    public static function generateNumber(): string
    {
        $prefix = 'QUO-'.now()->format('Ym').'-';
        $latest = static::where('quote_number', 'like', $prefix.'%')
            ->orderByDesc('quote_number')
            ->value('quote_number');

        if ($latest) {
            $number = (int) substr($latest, -3) + 1;
        } else {
            $number = 1;
        }

        return $prefix.str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /* ===== Relations ===== */

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->orderBy('order');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class);
    }

    /* ===== Scopes ===== */

    public function scopeDraft(Builder $query): void
    {
        $query->where('status', 'draft');
    }

    public function scopeAccepted(Builder $query): void
    {
        $query->where('status', 'accepted');
    }

    /* ===== Methods ===== */

    /**
     * 重新計算金額
     */
    public function recalculate(): void
    {
        $this->subtotal = $this->items()->sum('amount');
        $taxable = $this->subtotal - $this->discount;
        $this->tax_amount = round($taxable * ($this->tax_rate / 100), 2);
        $this->total = $taxable + $this->tax_amount;
        $this->save();
    }

    /* ===== Accessors ===== */

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'sent' => 'info',
            'accepted' => 'success',
            'rejected' => 'danger',
            'expired' => 'warning',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => '草稿',
            'sent' => '已送出',
            'accepted' => '已接受',
            'rejected' => '已拒絕',
            'expired' => '已過期',
            default => '未知',
        };
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->valid_until && $this->valid_until->isPast() && $this->status === 'sent';
    }
}
