<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
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
     * 真實營收的收款：合約收款 + 獨立發票（contract_id=null）收款。
     * 排除「合約發票自身帳本」的收款——那與合約收款重複（合約才是唯一真實來源）。
     */
    public function scopeRevenue(Builder $query): void
    {
        $query->where(function (Builder $q) {
            $q->where('payable_type', Contract::class)
                ->orWhere(function (Builder $q2) {
                    $q2->where('payable_type', Invoice::class)
                        ->whereIn('payable_id', Invoice::query()->whereNull('contract_id')->select('id'));
                });
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
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
