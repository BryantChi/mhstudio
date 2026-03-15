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
        'order',
        'completed_at',
    ];

    protected $casts = [
        'tech_stack' => 'array',
        'is_featured' => 'boolean',
        'completed_at' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->title);
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

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('order')->orderByDesc('completed_at');
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
