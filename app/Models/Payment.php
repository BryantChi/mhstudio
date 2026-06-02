<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $fillable = [
        'amount',
        'payment_method',
        'paid_on',
        'note',
        'proof_path',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_on' => 'date',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 只計發票收款（合約 paid_amount 由發票衍生，不另計），作為營收口徑來源。
     */
    public function scopeForInvoices($query)
    {
        return $query->where('payable_type', Invoice::class);
    }

    /**
     * 依實際收款日 paid_on 篩選某月（現金基礎營收歸期）。
     */
    public function scopeInMonth($query, int $month, int $year)
    {
        return $query->whereMonth('paid_on', $month)->whereYear('paid_on', $year);
    }

    /**
     * 依實際收款日 paid_on 篩選某年（現金基礎營收歸期）。
     */
    public function scopeInYear($query, int $year)
    {
        return $query->whereYear('paid_on', $year);
    }

    /**
     * 憑證檔的可存取網址（靠 /storage 後備路由）。
     */
    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_path ? '/storage/'.$this->proof_path : null;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
