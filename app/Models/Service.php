<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

class Service extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'type',
        'subtitle',
        'icon',
        'excerpt',
        'content',
        'features',
        'tech_tags',
        'price_range',
        'price',
        'price_label',
        'billing_cycle',
        'pages_min',
        'pages_max',
        'design_method',
        'special_features_count',
        'cms_modules_count',
        'revisions',
        'warranty_months',
        'work_days_min',
        'work_days_max',
        'pricing_category_id',
        'faq',
        'order',
        'is_active',
        'is_featured',
        'show_on_homepage',
    ];

    protected $casts = [
        'features' => 'array',
        'tech_tags' => 'array',
        'faq' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'show_on_homepage' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->title);
            }
        });
    }

    /* ===== Relations ===== */

    public function pricingCategory(): BelongsTo
    {
        return $this->belongsTo(PricingCategory::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceItem::class)->orderBy('order');
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'model');
    }

    /**
     * 自動生成 SEO Meta
     */
    public function generateSeoMeta(bool $force = false): void
    {
        $siteName = setting('site_name', 'MH Studio 孟衡');
        $description = $this->description ?: '';
        if ($description) {
            $description = mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags($description))), 0, 160);
        }

        $data = [
            'meta_title' => mb_substr($this->title . ' | ' . $siteName, 0, 250),
            'meta_description' => $description,
            'meta_robots' => 'index, follow',
            'og_title' => $this->title,
            'og_description' => $description,
            'og_image' => $this->image,
            'og_type' => 'website',
            'canonical_url' => route('services.show', $this->slug),
        ];

        if ($force || !$this->seoMeta) {
            SeoMeta::updateOrCreate(
                ['model_type' => static::class, 'model_id' => $this->id],
                $data
            );
        }
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

    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    public function scopeHomepage(Builder $query): void
    {
        $query->where('show_on_homepage', true);
    }

    /* ===== Accessors ===== */

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'website' => '網站方案',
            'hosting' => '主機代管',
            'maintenance' => '維護服務',
            'addon' => '加值服務',
            'consulting' => '顧問服務',
            default => $this->type ?? '',
        };
    }

    public function getBillingCycleLabelAttribute(): string
    {
        return match ($this->billing_cycle) {
            'once' => '一次性',
            'yearly' => '年繳',
            'monthly' => '月繳',
            'hourly' => '按時計費',
            default => '',
        };
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->price_label) {
            return $this->price_label;
        }

        if ($this->price > 0) {
            return 'NT$ ' . number_format($this->price);
        }

        return '';
    }

    public function getWorkDaysLabelAttribute(): string
    {
        if ($this->work_days_min && $this->work_days_max) {
            return $this->work_days_min . '-' . $this->work_days_max . ' 工作天';
        }
        if ($this->work_days_min) {
            return $this->work_days_min . ' 工作天起';
        }

        return '';
    }
}
