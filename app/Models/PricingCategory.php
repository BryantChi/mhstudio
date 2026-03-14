<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PricingCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'base_price_min',
        'base_price_max',
        'icon',
        'order',
        'is_active',
    ];

    protected $casts = [
        'base_price_min' => 'decimal:2',
        'base_price_max' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /* ===== Relations ===== */

    public function features(): HasMany
    {
        return $this->hasMany(PricingFeature::class)->orderBy('order');
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
}
