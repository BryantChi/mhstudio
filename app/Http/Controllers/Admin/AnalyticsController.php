<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class AnalyticsController extends Controller
{
    /**
     * 主要分析儀表板頁面
     */
    public function index(): View
    {
        return view('admin.analytics.index');
    }

    /**
     * API: 總覽統計資料
     */
    public function apiOverview(Request $request): JsonResponse
    {
        [$startDate, $endDate] = $this->parseDateRange($request);

        // 基礎查詢條件
        $baseQuery = AnalyticsEvent::where('event_name', 'page_view')
            ->dateRange($startDate, $endDate);

        // 總瀏覽量
        $totalViews = (clone $baseQuery)->count();

        // 不重複訪客（依 session_id）
        $uniqueVisitors = (clone $baseQuery)
            ->whereNotNull('session_id')
            ->selectRaw('COUNT(DISTINCT session_id) as cnt')
            ->value('cnt') ?? 0;

        // 總 session 數（同上）
        $totalSessions = $uniqueVisitors;

        // 每次工作階段頁面數
        $pagesPerSession = $totalSessions > 0
            ? round($totalViews / $totalSessions, 2)
            : 0;

        // 跳出率（只有 1 個頁面瀏覽的 session 比例）
        $bounceRate = $this->calculateBounceRate($startDate, $endDate);

        // 計算趨勢（與前一個同等時段比較）
        $daysDiff = max(1, $startDate->diffInDays($endDate));
        $prevStartDate = $startDate->copy()->subDays($daysDiff);
        $prevEndDate = $startDate->copy()->subDay()->endOfDay();

        $prevBaseQuery = AnalyticsEvent::where('event_name', 'page_view')
            ->dateRange($prevStartDate, $prevEndDate);

        $prevTotalViews = (clone $prevBaseQuery)->count();
        $prevUniqueVisitors = (clone $prevBaseQuery)
            ->whereNotNull('session_id')
            ->selectRaw('COUNT(DISTINCT session_id) as cnt')
            ->value('cnt') ?? 0;

        $prevTotalSessions = $prevUniqueVisitors;

        $prevPagesPerSession = $prevTotalSessions > 0
            ? round($prevTotalViews / $prevTotalSessions, 2)
            : 0;

        $prevBounceRate = $this->calculateBounceRate($prevStartDate, $prevEndDate);

        return response()->json([
            'total_views' => $totalViews,
            'unique_visitors' => $uniqueVisitors,
            'pages_per_session' => $pagesPerSession,
            'bounce_rate' => $bounceRate,
            'trends' => [
                'views' => $this->calculateTrend($totalViews, $prevTotalViews),
                'visitors' => $this->calculateTrend($uniqueVisitors, $prevUniqueVisitors),
                'pages_per_session' => $this->calculateTrend($pagesPerSession, $prevPagesPerSession),
                'bounce_rate' => $this->calculateTrend($bounceRate, $prevBounceRate, true),
            ],
            'today_views' => AnalyticsEvent::where('event_name', 'page_view')->today()->count(),
            'week_views' => AnalyticsEvent::where('event_name', 'page_view')->thisWeek()->count(),
            'month_views' => AnalyticsEvent::where('event_name', 'page_view')->thisMonth()->count(),
            'all_time_views' => AnalyticsEvent::where('event_name', 'page_view')->count(),
        ]);
    }

    /**
     * API: 熱門頁面
     */
    public function apiPages(Request $request): JsonResponse
    {
        [$startDate, $endDate] = $this->parseDateRange($request);

        $pages = AnalyticsEvent::select('page_url', 'page_title')
            ->selectRaw('COUNT(*) as views')
            ->selectRaw('COUNT(DISTINCT session_id) as unique_views')
            ->where('event_name', 'page_view')
            ->dateRange($startDate, $endDate)
            ->groupBy('page_url', 'page_title')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        return response()->json(['pages' => $pages]);
    }

    /**
     * API: 熱門來源
     */
    public function apiReferrers(Request $request): JsonResponse
    {
        [$startDate, $endDate] = $this->parseDateRange($request);

        $referrers = AnalyticsEvent::selectRaw("COALESCE(referrer, '(直接造訪)') as referrer_url")
            ->selectRaw('COUNT(*) as visits')
            ->selectRaw('COUNT(DISTINCT session_id) as unique_visits')
            ->where('event_name', 'page_view')
            ->dateRange($startDate, $endDate)
            ->groupByRaw("COALESCE(referrer, '(直接造訪)')")
            ->orderByDesc('visits')
            ->limit(10)
            ->get();

        return response()->json(['referrers' => $referrers]);
    }

    /**
     * API: 圖表資料（每日/每週/每月瀏覽量）
     */
    public function apiChart(Request $request): JsonResponse
    {
        [$startDate, $endDate] = $this->parseDateRange($request);
        $groupBy = $request->get('group_by', 'daily');

        $data = match ($groupBy) {
            'weekly' => $this->getWeeklyChartData($startDate, $endDate),
            'monthly' => $this->getMonthlyChartData($startDate, $endDate),
            default => $this->getDailyChartData($startDate, $endDate),
        };

        // 裝置分布
        $devices = $this->getDeviceBreakdown($startDate, $endDate);

        return response()->json([
            'chart' => $data,
            'devices' => $devices,
        ]);
    }

    /**
     * 解析日期範圍
     */
    protected function parseDateRange(Request $request): array
    {
        $range = $request->get('range', '30');

        if ($range === 'custom') {
            $startDate = Carbon::parse($request->get('start_date', now()->subDays(30)->toDateString()))->startOfDay();
            $endDate = Carbon::parse($request->get('end_date', now()->toDateString()))->endOfDay();
        } else {
            $days = (int) $range;
            $startDate = now()->subDays($days)->startOfDay();
            $endDate = now()->endOfDay();
        }

        return [$startDate, $endDate];
    }

    /**
     * 計算跳出率
     */
    protected function calculateBounceRate(Carbon $startDate, Carbon $endDate): float
    {
        $sessionCounts = AnalyticsEvent::select('session_id')
            ->selectRaw('COUNT(*) as page_count')
            ->where('event_name', 'page_view')
            ->dateRange($startDate, $endDate)
            ->whereNotNull('session_id')
            ->groupBy('session_id')
            ->get();

        $totalSessions = $sessionCounts->count();

        if ($totalSessions === 0) {
            return 0;
        }

        $bounceSessions = $sessionCounts->where('page_count', 1)->count();

        return round(($bounceSessions / $totalSessions) * 100, 1);
    }

    /**
     * 計算趨勢百分比
     */
    protected function calculateTrend(float $current, float $previous, bool $inverse = false): array
    {
        if ($previous == 0) {
            $percentage = $current > 0 ? 100 : 0;
        } else {
            $percentage = round((($current - $previous) / $previous) * 100, 1);
        }

        // 對於跳出率，下降是好的
        $direction = $percentage > 0 ? 'up' : ($percentage < 0 ? 'down' : 'flat');
        $isPositive = $inverse ? $percentage <= 0 : $percentage >= 0;

        return [
            'percentage' => abs($percentage),
            'direction' => $direction,
            'is_positive' => $isPositive,
        ];
    }

    /**
     * 取得每日圖表資料
     */
    protected function getDailyChartData(Carbon $startDate, Carbon $endDate): array
    {
        $results = AnalyticsEvent::selectRaw('DATE(event_time) as date, COUNT(*) as views')
            ->where('event_name', 'page_view')
            ->dateRange($startDate, $endDate)
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('views', 'date')
            ->toArray();

        $data = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $data[] = [
                'label' => $current->format('m/d'),
                'date' => $dateStr,
                'views' => $results[$dateStr] ?? 0,
            ];
            $current->addDay();
        }

        return $data;
    }

    /**
     * 取得每週圖表資料
     */
    protected function getWeeklyChartData(Carbon $startDate, Carbon $endDate): array
    {
        $results = AnalyticsEvent::selectRaw('YEARWEEK(event_time, 1) as week_num, MIN(DATE(event_time)) as week_start, COUNT(*) as views')
            ->where('event_name', 'page_view')
            ->dateRange($startDate, $endDate)
            ->groupBy('week_num')
            ->orderBy('week_num')
            ->get();

        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'label' => Carbon::parse($row->week_start)->format('m/d') . ' 週',
                'date' => $row->week_start,
                'views' => $row->views,
            ];
        }

        return $data;
    }

    /**
     * 取得每月圖表資料
     */
    protected function getMonthlyChartData(Carbon $startDate, Carbon $endDate): array
    {
        $results = AnalyticsEvent::selectRaw('DATE_FORMAT(event_time, "%Y-%m") as month, COUNT(*) as views')
            ->where('event_name', 'page_view')
            ->dateRange($startDate, $endDate)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'label' => Carbon::parse($row->month . '-01')->format('Y/m'),
                'date' => $row->month,
                'views' => $row->views,
            ];
        }

        return $data;
    }

    /**
     * 取得裝置分布資料
     */
    protected function getDeviceBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        $baseQuery = AnalyticsEvent::where('event_name', 'page_view')
            ->dateRange($startDate, $endDate)
            ->whereNotNull('user_agent');

        // 使用 SQL CASE 在資料庫層分類，避免載入全部記錄到記憶體
        $tabletLike = "LOWER(user_agent) LIKE '%ipad%' OR LOWER(user_agent) LIKE '%tablet%' OR LOWER(user_agent) LIKE '%kindle%' OR LOWER(user_agent) LIKE '%silk%' OR LOWER(user_agent) LIKE '%playbook%' OR LOWER(user_agent) LIKE '%sm-t%'";
        $mobileLike = "LOWER(user_agent) LIKE '%mobile%' OR LOWER(user_agent) LIKE '%android%' OR LOWER(user_agent) LIKE '%iphone%' OR LOWER(user_agent) LIKE '%ipod%' OR LOWER(user_agent) LIKE '%windows phone%'";

        $result = (clone $baseQuery)->selectRaw("
            SUM(CASE WHEN ({$tabletLike}) THEN 1 ELSE 0 END) as tablet_count,
            SUM(CASE WHEN NOT ({$tabletLike}) AND ({$mobileLike}) THEN 1 ELSE 0 END) as mobile_count,
            SUM(CASE WHEN NOT ({$tabletLike}) AND NOT ({$mobileLike}) THEN 1 ELSE 0 END) as desktop_count
        ")->first();

        $devices = [
            'desktop' => (int) ($result->desktop_count ?? 0),
            'mobile' => (int) ($result->mobile_count ?? 0),
            'tablet' => (int) ($result->tablet_count ?? 0),
        ];

        $total = array_sum($devices);

        return [
            ['label' => '桌面裝置', 'value' => $devices['desktop'], 'percentage' => $total > 0 ? round(($devices['desktop'] / $total) * 100, 1) : 0],
            ['label' => '行動裝置', 'value' => $devices['mobile'], 'percentage' => $total > 0 ? round(($devices['mobile'] / $total) * 100, 1) : 0],
            ['label' => '平板裝置', 'value' => $devices['tablet'], 'percentage' => $total > 0 ? round(($devices['tablet'] / $total) * 100, 1) : 0],
        ];
    }

}
