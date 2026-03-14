<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\User;
use App\Models\Category;
use App\Models\AnalyticsEvent;
use App\Models\Invoice;
use App\Models\Contract;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\ClientInteraction;
use App\Models\QuoteRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * 顯示儀表板
     */
    public function index(): View
    {
        // 統計數據（快取 5 分鐘）
        $stats = Cache::remember('dashboard_stats', 300, function () {
            $todayViews = AnalyticsEvent::today()->where('event_name', 'page_view')->count();
            $yesterdayViews = AnalyticsEvent::where('event_name', 'page_view')
                ->whereDate('event_time', now()->subDay())
                ->count();
            $weekViews = AnalyticsEvent::thisWeek()->where('event_name', 'page_view')->count();

            return [
                'total_users' => User::count(),
                'total_articles' => Article::count(),
                'published_articles' => Article::published()->count(),
                'draft_articles' => Article::draft()->count(),
                'total_categories' => Category::count(),
                'today_views' => $todayViews,
                'yesterday_views' => $yesterdayViews,
                'week_views' => $weekViews,
            ];
        });

        // 最近文章
        $recentArticles = Article::with(['author', 'category'])
            ->latest()
            ->limit(5)
            ->get();

        // 熱門文章
        $popularArticles = Article::published()
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get();

        // 每日瀏覽量 (最近 7 天，快取 10 分鐘)
        $dailyViews = Cache::remember('dashboard_daily_views', 600, fn () => $this->getDailyViews(7));

        // 熱門頁面（快取 10 分鐘）
        $topPages = Cache::remember('dashboard_top_pages', 600, fn () => AnalyticsEvent::getTopPages(5, now()->subDays(7), now()));

        // 今日熱門頁面 Top 3
        $todayTopPages = AnalyticsEvent::getTopPages(3, now()->startOfDay(), now());

        // 商業概覽 KPI（快取 5 分鐘）
        $businessKpi = Cache::remember('dashboard_business_kpi', 300, function () {
            return [
                'month_revenue' => Invoice::where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year)
                    ->sum('total'),
                'pending_amount' => (float) Invoice::whereIn('status', ['sent', 'partially_paid', 'overdue'])
                    ->selectRaw('SUM(total - paid_amount) as balance')
                    ->value('balance') ?? 0,
                'pending_task_count' => Task::where('status', '!=', 'completed')->count(),
                'weekly_minutes' => TimeEntry::thisWeek()->sum('duration_minutes'),
            ];
        });

        $monthRevenue = $businessKpi['month_revenue'];
        $pendingAmount = $businessKpi['pending_amount'];
        $pendingTaskCount = $businessKpi['pending_task_count'];
        $weeklyMinutes = $businessKpi['weekly_minutes'];

        $overdueInvoices = Invoice::overdue()->with('client')->latest('due_date')->take(5)->get();
        $expiringContracts = Contract::active()->expiringSoon(30)->with('client')->take(5)->get();
        $recentInteractions = ClientInteraction::with(['client', 'user'])->latest('interaction_date')->take(5)->get();
        $recentCompletedTasks = Task::where('status', 'completed')->with('project')->latest('completed_at')->take(5)->get();

        // 報價請求
        $pendingQuoteRequests = QuoteRequest::where('status', 'pending')->count();
        $recentQuoteRequests = QuoteRequest::latest()->take(5)->get();

        return view('admin.dashboard.index', compact(
            'stats', 'recentArticles', 'popularArticles', 'dailyViews', 'topPages', 'todayTopPages',
            'monthRevenue', 'pendingAmount', 'pendingTaskCount', 'weeklyMinutes',
            'overdueInvoices', 'expiringContracts', 'recentInteractions', 'recentCompletedTasks',
            'pendingQuoteRequests', 'recentQuoteRequests'
        ));
    }

    /**
     * 獲取每日瀏覽量
     */
    protected function getDailyViews(int $days = 7): array
    {
        $data = [];
        $startDate = now()->subDays($days - 1)->startOfDay();

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $count = AnalyticsEvent::whereDate('event_time', $date)
                ->where('event_name', 'page_view')
                ->count();

            $data[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('m/d'),
                'count' => $count,
            ];
        }

        return $data;
    }

    /**
     * 獲取系統資訊
     */
    public function systemInfo(): View
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
        ];

        // 讀取已安裝的套件
        $packages = $this->getInstalledPackages();

        return view('admin.dashboard.system-info', compact('info', 'packages'));
    }

    /**
     * 獲取已安裝的套件列表
     */
    protected function getInstalledPackages(): array
    {
        $composerLockPath = base_path('composer.lock');

        if (!file_exists($composerLockPath)) {
            return [];
        }

        $composerLock = json_decode(file_get_contents($composerLockPath), true);
        $packages = [];

        // 只列出 require 區段的主要套件
        if (isset($composerLock['packages'])) {
            foreach ($composerLock['packages'] as $package) {
                // 過濾掉 Laravel 核心和一些系統套件
                if ($this->shouldShowPackage($package['name'])) {
                    $packages[] = [
                        'name' => $package['name'],
                        'version' => $package['version'],
                        'description' => $package['description'] ?? '',
                    ];
                }
            }
        }

        // 按名稱排序
        usort($packages, fn($a, $b) => strcmp($a['name'], $b['name']));

        return $packages;
    }

    /**
     * 判斷是否應該顯示該套件
     */
    protected function shouldShowPackage(string $packageName): bool
    {
        // 只顯示特定前綴的套件
        $allowedPrefixes = [
            'spatie/',
            'laravel/sanctum',
            'laravel/tinker',
            'laravel/telescope',
        ];

        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($packageName, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
