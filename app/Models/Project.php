<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'results',
        'client',
        'cover_image',
        'url',
        'github_url',
        'tech_stack',
        'category',
        'status',
        'is_featured',
        'visibility',
        'display_mode',
        'hide_client',
        'hide_results',
        'confidential_label',
        'abstract_color',
        'exclude_from_search',
        'share_token',
        'order',
        'completed_at',
    ];

    protected $casts = [
        'tech_stack' => 'array',
        'is_featured' => 'boolean',
        'hide_client' => 'boolean',
        'hide_results' => 'boolean',
        'exclude_from_search' => 'boolean',
        'completed_at' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->title);
            }
            if (empty($project->share_token)) {
                $project->share_token = Str::random(32);
            }
        });
    }

    /* ===== Relations ===== */

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('order');
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
        $description = $this->excerpt ?: '';
        if (!$description && $this->content) {
            $description = mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags($this->content))), 0, 160);
        }

        $keywords = is_array($this->tech_stack) ? implode(', ', $this->tech_stack) : '';
        if ($this->category) {
            $keywords = $this->category . ($keywords ? ', ' . $keywords : '');
        }

        $data = [
            'meta_title' => mb_substr($this->title . ' | ' . $siteName, 0, 250),
            'meta_description' => $description,
            'meta_keywords' => $keywords,
            'meta_robots' => $this->exclude_from_search ? 'noindex, nofollow' : 'index, follow',
            'og_title' => $this->title,
            'og_description' => $description,
            'og_image' => $this->cover_image,
            'og_type' => 'website',
            'canonical_url' => route('portfolio.show', $this->slug),
        ];

        if ($force || !$this->seoMeta) {
            SeoMeta::updateOrCreate(
                ['model_type' => static::class, 'model_id' => $this->id],
                $data
            );
        }
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectComment::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'client_projects')
            ->withPivot('role')
            ->withTimestamps();
    }

    /* ===== Scopes ===== */

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    /**
     * 前台列表可見（published + public/NULL 或 showcase）
     */
    public function scopePublicVisible(Builder $query): void
    {
        $query->where('status', 'published')->where(function ($q) {
            $q->whereIn('visibility', ['public', 'showcase'])->orWhereNull('visibility');
        });
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('order');
    }

    /* ===== Accessors ===== */

    /**
     * 封面圖片：優先使用原欄位值，fallback 到圖片庫第一張
     */
    public function getCoverImageAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        // Fallback 到圖片庫第一張
        $firstImage = $this->relationLoaded('images')
            ? $this->images->first()
            : $this->images()->first();

        return $firstImage?->image_url;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'draft' => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'published' => '已發布',
            'draft' => '草稿',
            default => '未知',
        };
    }

    public function getVisibilityLabelAttribute(): string
    {
        return match ($this->visibility) {
            'public' => '公開',
            'showcase' => '僅展示',
            'unlisted' => '僅限連結',
            'hidden' => '隱藏',
            default => '公開',
        };
    }

    public function getVisibilityColorAttribute(): string
    {
        return match ($this->visibility) {
            'public' => 'success',
            'showcase' => 'info',
            'unlisted' => 'warning',
            'hidden' => 'secondary',
            default => 'success',
        };
    }

    public function getDisplayModeLabelAttribute(): string
    {
        return match ($this->display_mode) {
            'blurred' => '模糊保密',
            'abstract' => '抽象封面',
            default => '正常展示',
        };
    }

    public function getEffectiveClientAttribute(): ?string
    {
        return $this->hide_client ? '機密客戶' : $this->client;
    }

    public function getConfidentialLabelTextAttribute(): string
    {
        return $this->confidential_label ?: 'Confidential Project';
    }

    public function isConfidential(): bool
    {
        return $this->display_mode !== 'normal';
    }

    /**
     * 獲取進度百分比（根據里程碑完成比例）
     */
    public function getProgressPercentageAttribute(): int
    {
        $total = $this->milestones()->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $this->milestones()->where('status', 'completed')->count();

        return (int) round(($completed / $total) * 100);
    }
}
