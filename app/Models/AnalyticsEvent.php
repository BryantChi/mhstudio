<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AnalyticsEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_name',
        'event_category',
        'event_action',
        'event_label',
        'event_value',
        'page_url',
        'page_title',
        'referrer',
        'user_agent',
        'ip_address',
        'session_id',
        'custom_dimensions',
        'custom_metrics',
        'event_time',
    ];

    protected $casts = [
        'custom_dimensions' => 'array',
        'custom_metrics' => 'array',
        'event_time' => 'datetime',
        'event_value' => 'integer',
    ];

    /**
     * 關聯:用戶
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 查詢範圍:依事件名稱
     */
    public function scopeByEventName(Builder $query, string $eventName): void
    {
        $query->where('event_name', $eventName);
    }

    /**
     * 查詢範圍:依分類
     */
    public function scopeByCategory(Builder $query, string $category): void
    {
        $query->where('event_category', $category);
    }

    /**
     * 查詢範圍:依日期範圍
     */
    public function scopeDateRange(Builder $query, Carbon $startDate, Carbon $endDate): void
    {
        $query->whereBetween('event_time', [$startDate, $endDate]);
    }

    /**
     * 查詢範圍:今天
     */
    public function scopeToday(Builder $query): void
    {
        $query->whereDate('event_time', today());
    }

    /**
     * 查詢範圍:本週
     */
    public function scopeThisWeek(Builder $query): void
    {
        $query->whereBetween('event_time', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * 查詢範圍:本月
     */
    public function scopeThisMonth(Builder $query): void
    {
        $query->whereMonth('event_time', now()->month)
              ->whereYear('event_time', now()->year);
    }

    /**
     * 查詢範圍:依頁面
     */
    public function scopeByPage(Builder $query, string $pageUrl): void
    {
        $query->where('page_url', $pageUrl);
    }

    /**
     * 記錄頁面瀏覽
     */
    public static function recordPageView(array $data = []): self
    {
        return static::create(array_merge([
            'event_name' => 'page_view',
            'event_category' => 'engagement',
            'event_action' => 'view',
            'user_id' => auth()->id(),
            'page_url' => request()->fullUrl(),
            'page_title' => $data['page_title'] ?? null,
            'referrer' => request()->header('referer'),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
            'session_id' => session()->getId(),
            'event_time' => now(),
        ], $data));
    }

    /**
     * 記錄點擊事件
     */
    public static function recordClick(string $label, array $data = []): self
    {
        return static::create(array_merge([
            'event_name' => 'click',
            'event_category' => 'engagement',
            'event_action' => 'click',
            'event_label' => $label,
            'user_id' => auth()->id(),
            'page_url' => request()->fullUrl(),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
            'session_id' => session()->getId(),
            'event_time' => now(),
        ], $data));
    }

    /**
     * 記錄自訂事件
     */
    public static function recordCustomEvent(string $eventName, array $data = []): self
    {
        return static::create(array_merge([
            'event_name' => $eventName,
            'user_id' => auth()->id(),
            'page_url' => request()->fullUrl(),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
            'session_id' => session()->getId(),
            'event_time' => now(),
        ], $data));
    }

    /**
     * 獲取熱門頁面
     */
    public static function getTopPages(int $limit = 10, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = static::select('page_url', 'page_title')
            ->selectRaw('COUNT(*) as views')
            ->where('event_name', 'page_view')
            ->groupBy('page_url', 'page_title')
            ->orderBy('views', 'desc')
            ->limit($limit);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->get()->toArray();
    }

    /**
     * 獲取事件統計
     */
    public static function getEventStats(string $eventName, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = static::where('event_name', $eventName);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return [
            'total_count' => $query->count(),
            'unique_users' => $query->distinct('user_id')->count('user_id'),
            'total_value' => $query->sum('event_value'),
            'avg_value' => $query->avg('event_value'),
        ];
    }
}
