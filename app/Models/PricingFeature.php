<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PricingFeature extends Model
{
    protected $fillable = [
        'pricing_category_id',
        'name',
        'slug',
        'description',
        'price_min',
        'price_max',
        'icon',
        'order',
        'is_active',
    ];

    protected $casts = [
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($feature) {
            if (empty($feature->slug)) {
                $feature->slug = Str::slug($feature->name);
            }
        });
    }

    /* ===== Relations ===== */

    public function category(): BelongsTo
    {
        return $this->belongsTo(PricingCategory::class, 'pricing_category_id');
    }

    /* ===== Scopes ===== */

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('order');
    }

    public function scopeUniversal(Builder $query): void
    {
        $query->whereNull('pricing_category_id');
    }

    public function scopeForCategory(Builder $query, int $categoryId): void
    {
        $query->where(function ($q) use ($categoryId) {
            $q->where('pricing_category_id', $categoryId)
              ->orWhereNull('pricing_category_id');
        });
    }
}
