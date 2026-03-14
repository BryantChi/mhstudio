<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class LegalPage extends Model
{
    use LogsActivity;

    protected $fillable = [
        'title',
        'slug',
        'type',
        'content',
        'meta_title',
        'meta_description',
        'is_active',
        'order',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'published_at' => 'datetime',
    ];

    /**
     * 頁面類型選項
     */
    public const TYPES = [
        'privacy' => '隱私權政策',
        'terms' => '服務條款',
        'cookie' => 'Cookie 政策',
        'disclaimer' => '免責聲明',
        'custom' => '自訂頁面',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = \Illuminate\Support\Str::slug($page->title);
            }
            if (empty($page->published_at)) {
                $page->published_at = now();
            }
        });
    }

    /**
     * Activity Log 配置
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'type', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
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

    /* ===== Accessors ===== */

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? '自訂頁面';
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'privacy' => 'primary',
            'terms' => 'success',
            'cookie' => 'warning',
            'disclaimer' => 'info',
            default => 'secondary',
        };
    }

    public function getEffectiveMetaTitleAttribute(): string
    {
        return $this->meta_title ?: $this->title;
    }

    public function getEffectiveMetaDescriptionAttribute(): string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }

        $content = strip_tags($this->content);
        $content = preg_replace('/\s+/', ' ', $content);
        return substr(trim($content), 0, 160);
    }
}
