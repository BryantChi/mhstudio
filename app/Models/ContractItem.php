<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractItem extends Model
{
    protected $fillable = [
        'contract_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'amount',
        'order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->amount = round($item->quantity * $item->unit_price, 2);
        });
    }

    /* ===== Scopes ===== */

    public function scopeOrdered(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->orderBy('order');
    }

    /* ===== Relations ===== */

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
