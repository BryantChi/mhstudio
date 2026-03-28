<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Article extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, LogsActivity;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'published_at',
        'views_count',
        'likes_count',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_featured',
        'allow_comments',
        'order',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'views_count' => 'integer',
        'likes_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = \Illuminate\Support\Str::slug($article->title);
            }

            if (empty($article->user_id)) {
                $article->user_id = auth()->id();
            }

            // 狀態為 published 但沒設 published_at 時，自動填入現在時間
            if ($article->status === 'published' && empty($article->published_at)) {
                $article->published_at = now();
            }
        });

        static::updating(function ($article) {
            // 從其他狀態改為 published 但沒設 published_at 時，自動填入
            if ($article->isDirty('status') && $article->status === 'published' && empty($article->published_at)) {
                $article->published_at = now();
            }
        });
    }

    /**
     * Activity Log 配置
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'published_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * 關聯：作者
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 關聯：分類
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * 關聯：標籤
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * 關聯：SEO Meta（多態關聯）
     */
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'model');
    }

    /**
     * 查詢範圍：已發布
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')
              ->where('published_at', '<=', now());
    }

    /**
     * 查詢範圍：草稿
     */
    public function scopeDraft(Builder $query): void
    {
        $query->where('status', 'draft');
    }

    /**
     * 查詢範圍：精選
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * 查詢範圍：排序
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('order')->orderByDesc('created_at');
    }

    /**
     * 查詢範圍：依分類
     */
    public function scopeByCategory(Builder $query, int $categoryId): void
    {
        $query->where('category_id', $categoryId);
    }

    /**
     * 增加瀏覽次數
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * 增加按讚數
     */
    public function incrementLikes(): void
    {
        $this->increment('likes_count');
    }

    /**
     * 檢查是否已發布
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at?->isPast();
    }

    /**
     * 檢查是否為草稿
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * 自動生成 SEO Meta
     */
    public function generateSeoMeta(bool $force = false): void
    {
        $siteName = setting('site_name', 'MH Studio 孟衡');
        $description = $this->generateMetaDescription();

        $data = [
            'meta_title' => mb_substr($this->title . ' | ' . $siteName, 0, 250),
            'meta_description' => $description,
            'meta_keywords' => $this->generateMetaKeywords(),
            'meta_robots' => 'index, follow',
            'og_title' => $this->title,
            'og_description' => $description,
            'og_image' => $this->featured_image,
            'og_type' => 'article',
            'canonical_url' => route('blog.show', $this->slug),
        ];

        if ($force || !$this->seoMeta) {
            $this->seoMeta()->updateOrCreate(
                ['model_type' => static::class, 'model_id' => $this->id],
                $data
            );
        }
    }

    /**
     * 生成 Meta Description
     */
    protected function generateMetaDescription(): string
    {
        if ($this->excerpt) {
            return substr($this->excerpt, 0, 160);
        }

        $content = strip_tags($this->content);
        $content = preg_replace('/\s+/', ' ', $content);
        return substr(trim($content), 0, 160);
    }

    /**
     * 生成 Meta Keywords
     */
    protected function generateMetaKeywords(): string
    {
        $keywords = $this->tags->pluck('name')->toArray();
        return implode(', ', $keywords);
    }

    /**
     * 獲取狀態標籤顏色
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'draft' => 'secondary',
            'scheduled' => 'info',
            'archived' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * 獲取狀態標籤文字
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'published' => '已發布',
            'draft' => '草稿',
            'scheduled' => '排程',
            'archived' => '封存',
            default => '未知',
        };
    }

    /**
     * Media Library 設定
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')
             ->singleFile()
             ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('gallery')
             ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
}
