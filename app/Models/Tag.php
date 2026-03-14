<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'count',
    ];

    protected $casts = [
        'count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = \Illuminate\Support\Str::slug($tag->name);
            }
        });
    }

    /**
     * 關聯:文章
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }

    /**
     * 查詢範圍:熱門標籤
     */
    public function scopePopular(Builder $query, int $limit = 10): void
    {
        $query->orderBy('count', 'desc')->limit($limit);
    }

    /**
     * 查詢範圍:依使用次數排序
     */
    public function scopeOrderByUsage(Builder $query, string $direction = 'desc'): void
    {
        $query->orderBy('count', $direction);
    }

    /**
     * 增加使用次數
     */
    public function incrementCount(): void
    {
        $this->increment('count');
    }

    /**
     * 減少使用次數
     */
    public function decrementCount(): void
    {
        if ($this->count > 0) {
            $this->decrement('count');
        }
    }

    /**
     * 同步使用次數
     */
    public function syncCount(): void
    {
        $this->count = $this->articles()->count();
        $this->save();
    }

    /**
     * 獲取文章數量
     */
    public function getArticlesCountAttribute(): int
    {
        return $this->articles()->published()->count();
    }
}
