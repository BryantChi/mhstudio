<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * 綜合報表中心頁面（空殼，資料由各 API 端點載入）
     */
    public function index(): View
    {
        return view('admin.reports.index');
    }

    /**
     * API: 營收財務報表（現金基礎，依 Payment.paid_on）
     */
    public function apiRevenue(Request $request): JsonResponse
    {
        [$from, $to] = $this->parseRange($request);

        $rangeQuery = fn () => Payment::forInvoices()->whereBetween('paid_on', [$from, $to]);

        $revenue = (float) $rangeQuery()->sum('amount');
        $count = (int) $rangeQuery()->count();

        // 月營收趨勢
        $trendRows = $rangeQuery()
            ->selectRaw('DATE_FORMAT(paid_on, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $trend = $trendRows->map(fn ($row) => [
            'label' => Carbon::parse($row->month . '-01')->format('Y/m'),
            'total' => (float) $row->total,
        ]);

        // 未收款 / 逾期（不限區間，反映當前應收狀態）
        $outstanding = (float) (Invoice::unpaid()
            ->selectRaw('SUM(total - paid_amount) as bal')->value('bal') ?? 0);
        $overdue = (float) (Invoice::overdue()
            ->selectRaw('SUM(total - paid_amount) as bal')->value('bal') ?? 0);

        return response()->json([
            'revenue' => $revenue,
            'count' => $count,
            'outstanding' => $outstanding,
            'overdue' => $overdue,
            'trend' => $trend,
        ]);
    }

    /**
     * API: 客戶分析報表
     */
    public function apiClients(Request $request): JsonResponse
    {
        [$from, $to] = $this->parseRange($request);

        $byTier = Client::selectRaw('tier, COUNT(*) as c')
            ->groupBy('tier')->pluck('c', 'tier');
        $bySource = Client::selectRaw('source, COUNT(*) as c')
            ->groupBy('source')->pluck('c', 'source');

        $topRevenue = Client::orderByDesc('total_revenue')
            ->take(10)
            ->get(['name', 'total_revenue'])
            ->map(fn ($client) => [
                'name' => $client->name,
                'total_revenue' => (float) $client->total_revenue,
            ]);

        return response()->json([
            'total' => (int) Client::count(),
            'active' => (int) Client::active()->count(),
            'leads' => (int) Client::leads()->count(),
            'new_in_range' => (int) Client::whereBetween('created_at', [$from, $to])->count(),
            'by_tier' => $byTier,
            'by_source' => $bySource,
            'top_revenue' => $topRevenue,
        ]);
    }

    /**
     * API: 工時計費報表（依 TimeEntry.started_at）
     */
    public function apiTime(Request $request): JsonResponse
    {
        [$from, $to] = $this->parseRange($request);

        $base = fn () => TimeEntry::whereBetween('started_at', [$from, $to]);

        $totalMinutes = (int) $base()->sum('duration_minutes');
        $billableMinutes = (int) $base()->billable()->sum('duration_minutes');
        $billableAmount = (float) ($base()->billable()->whereNotNull('hourly_rate')
            ->selectRaw('SUM(duration_minutes / 60 * hourly_rate) as amt')->value('amt') ?? 0);

        // 各專案工時
        $byProject = $base()
            ->selectRaw('project_id, SUM(duration_minutes) as minutes')
            ->groupBy('project_id')
            ->with('project:id,title')
            ->orderByDesc('minutes')
            ->get()
            ->map(fn ($row) => [
                'project' => $row->project?->title ?? '未指定專案',
                'minutes' => (int) $row->minutes,
            ]);

        return response()->json([
            'total_minutes' => $totalMinutes,
            'billable_minutes' => $billableMinutes,
            'billable_amount' => $billableAmount,
            'by_project' => $byProject,
        ]);
    }

    /**
     * 解析時間區間（本月 / 本年 / 最近 N 天 / 自訂）
     */
    protected function parseRange(Request $request): array
    {
        $range = $request->get('range', 'month');

        return match ($range) {
            'year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [
                Carbon::parse($request->get('from', now()->startOfMonth()->toDateString()))->startOfDay(),
                Carbon::parse($request->get('to', now()->toDateString()))->endOfDay(),
            ],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [now()->subDays((int) $range)->startOfDay(), now()->endOfDay()],
        };
    }
}
