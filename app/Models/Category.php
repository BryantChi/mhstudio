<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Category extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'icon',
        'color',
        'status',
        'order',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = \Illuminate\Support\Str::slug($category->name);
            }
        });
    }

    /**
     * Activity Log 配置
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'order'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * 關聯:父分類
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * 關聯:子分類
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order');
    }

    /**
     * 關聯:文章
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * 查詢範圍:啟用
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * 查詢範圍:排序
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('order');
    }

    /**
     * 查詢範圍:頂層分類
     */
    public function scopeTopLevel(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    /**
     * 查詢範圍:有父分類
     */
    public function scopeHasParent(Builder $query): void
    {
        $query->whereNotNull('parent_id');
    }

    /**
     * 檢查是否為頂層分類
     */
    public function isTopLevel(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * 檢查是否有子分類
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * 獲取所有祖先分類
     */
    public function getAncestors(): array
    {
        $ancestors = [];
        $category = $this;

        while ($category->parent) {
            $ancestors[] = $category->parent;
            $category = $category->parent;
        }

        return array_reverse($ancestors);
    }

    /**
     * 獲取所有後代分類
     */
    public function getDescendants(): array
    {
        $descendants = [];

        foreach ($this->children as $child) {
            $descendants[] = $child;
            $descendants = array_merge($descendants, $child->getDescendants());
        }

        return $descendants;
    }

    /**
     * 獲取文章數量
     */
    public function getArticlesCountAttribute(): int
    {
        return $this->articles()->published()->count();
    }
}
