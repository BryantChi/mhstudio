@extends('layouts.admin')

@section('title', '儀表板')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h2 class="mb-0">儀表板</h2>
            <p class="text-muted">歡迎回來，{{ auth()->user()->name }}!</p>
        </div>
    </div>
</div>

{{-- 統計卡片 --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-6 fw-semibold text-primary">{{ $stats['total_users'] ?? 0 }}</div>
                        <div class="text-muted small text-uppercase fw-semibold">用戶總數</div>
                    </div>
                    <div class="text-primary">
                        <svg class="icon icon-xl">
                            <use xlink:href="/assets/icons/free.svg#cil-people"></use>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-6 fw-semibold text-success">{{ $stats['published_articles'] ?? 0 }}</div>
                        <div class="text-muted small text-uppercase fw-semibold">已發布文章</div>
                    </div>
                    <div class="text-success">
                        <svg class="icon icon-xl">
                            <use xlink:href="/assets/icons/free.svg#cil-newspaper"></use>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-6 fw-semibold text-warning">{{ $stats['draft_articles'] ?? 0 }}</div>
                        <div class="text-muted small text-uppercase fw-semibold">草稿文章</div>
                    </div>
                    <div class="text-warning">
                        <svg class="icon icon-xl">
                            <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-6 fw-semibold text-info">{{ $stats['today_views'] ?? 0 }}</div>
                        <div class="text-muted small text-uppercase fw-semibold">今日瀏覽</div>
                    </div>
                    <div class="text-info">
                        <svg class="icon icon-xl">
                            <use xlink:href="/assets/icons/free.svg#cil-chart-line"></use>
                        </svg>
                    </div>
                </div>
                @php
                    $yesterdayViews = $stats['yesterday_views'] ?? 0;
                    $todayViews = $stats['today_views'] ?? 0;
                    $viewsDiff = $yesterdayViews > 0 ? round(($todayViews - $yesterdayViews) / $yesterdayViews * 100, 1) : 0;
                @endphp
                <div class="mt-2 small">
                    @if($viewsDiff > 0)
                        <span class="text-success">&#9650; {{ $viewsDiff }}%</span>
                    @elseif($viewsDiff < 0)
                        <span class="text-danger">&#9660; {{ abs($viewsDiff) }}%</span>
                    @else
                        <span class="text-muted">-- 持平</span>
                    @endif
                    <span class="text-muted">較昨日</span>
                    <span class="text-muted ms-1">| 本週 {{ number_format($stats['week_views'] ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 商業概覽 KPI --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <h5 class="text-muted mb-0">商業概覽</h5>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('admin.invoices.index', ['status' => 'paid']) }}" class="card dashboard-card text-decoration-none">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-6 fw-semibold text-success">NT$ {{ number_format($monthRevenue ?? 0) }}</div>
                        <div class="text-muted small text-uppercase fw-semibold">本月營收</div>
                    </div>
                    <div class="text-success">
                        <svg class="icon icon-xl"><use xlink:href="/assets/icons/free.svg#cil-dollar"></use></svg>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('admin.invoices.index', ['status' => 'sent']) }}" class="card dashboard-card text-decoration-none">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-6 fw-semibold text-warning">NT$ {{ number_format($pendingAmount ?? 0) }}</div>
                        <div class="text-muted small text-uppercase fw-semibold">待收款項</div>
                    </div>
                    <div class="text-warning">
                        <svg class="icon icon-xl"><use xlink:href="/assets/icons/free.svg#cil-clock"></use></svg>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('admin.tasks.index') }}" class="card dashboard-card text-decoration-none">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-6 fw-semibold text-primary">{{ $pendingTaskCount ?? 0 }}</div>
                        <div class="text-muted small text-uppercase fw-semibold">進行中任務</div>
                    </div>
                    <div class="text-primary">
                        <svg class="icon icon-xl"><use xlink:href="/assets/icons/free.svg#cil-task"></use></svg>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('admin.time-entries.index') }}" class="card dashboard-card text-decoration-none">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        @php $weekHours = floor(($weeklyMinutes ?? 0) / 60); $weekMins = ($weeklyMinutes ?? 0) % 60; @endphp
                        <div class="fs-6 fw-semibold text-info">{{ sprintf('%02d:%02d', $weekHours, $weekMins) }}</div>
                        <div class="text-muted small text-uppercase fw-semibold">本週工時</div>
                    </div>
                    <div class="text-info">
                        <svg class="icon icon-xl"><use xlink:href="/assets/icons/free.svg#cil-av-timer"></use></svg>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 報價請求 KPI --}}
@if(isset($pendingQuoteRequests) && $pendingQuoteRequests > 0)
<div class="row g-3 mb-4">
    <div class="col-12">
        <a href="{{ route('admin.quote-requests.index', ['status' => 'pending']) }}" class="card border-warning text-decoration-none">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-5 fw-semibold text-warning">{{ $pendingQuoteRequests }} 筆待處理</div>
                        <div class="text-muted small">報價請求等待處理</div>
                    </div>
                    <div class="text-warning">
                        <svg class="icon icon-xl"><use xlink:href="/assets/icons/free.svg#cil-envelope-letter"></use></svg>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
@endif

{{-- 警告區塊 --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-danger">
            <div class="card-header bg-danger bg-opacity-10">
                <strong class="text-danger">
                    <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-warning"></use></svg>
                    逾期發票
                </strong>
            </div>
            <div class="card-body p-0">
                @if(isset($overdueInvoices) && $overdueInvoices->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($overdueInvoices as $invoice)
                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="list-group-item list-group-item-action list-group-item-danger">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $invoice->client?->name ?? '未知客戶' }}</strong>
                                <small class="d-block text-muted">{{ $invoice->invoice_number }}</small>
                            </div>
                            <div class="text-end">
                                <strong>NT$ {{ number_format($invoice->balance_due) }}</strong>
                                <small class="d-block text-danger">逾期 {{ now()->diffInDays($invoice->due_date) }} 天</small>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="text-center text-muted py-3">
                    <svg class="icon icon-xl mb-1"><use xlink:href="/assets/icons/free.svg#cil-check-circle"></use></svg>
                    <div>沒有逾期發票</div>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning bg-opacity-10">
                <strong class="text-warning">
                    <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-calendar"></use></svg>
                    即將到期合約
                </strong>
            </div>
            <div class="card-body p-0">
                @if(isset($expiringContracts) && $expiringContracts->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($expiringContracts as $contract)
                    <a href="{{ route('admin.contracts.show', $contract) }}" class="list-group-item list-group-item-action list-group-item-warning">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $contract->client?->name ?? '未知客戶' }}</strong>
                                <small class="d-block text-muted">{{ $contract->contract_number }}</small>
                            </div>
                            <div class="text-end">
                                <small>{{ $contract->end_date?->format('Y-m-d') }}</small>
                                <small class="d-block text-warning">剩餘 {{ now()->diffInDays($contract->end_date) }} 天</small>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="text-center text-muted py-3">
                    <svg class="icon icon-xl mb-1"><use xlink:href="/assets/icons/free.svg#cil-check-circle"></use></svg>
                    <div>沒有即將到期的合約</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 近期活動 --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong>最近客戶互動</strong></div>
            <div class="card-body p-0">
                @if(isset($recentInteractions) && $recentInteractions->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($recentInteractions as $interaction)
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <svg class="icon me-2 text-{{ $interaction->type_color }}">
                                <use xlink:href="/assets/icons/free.svg#{{ $interaction->type_icon }}"></use>
                            </svg>
                            <div class="flex-grow-1">
                                <strong>{{ $interaction->client?->name }}</strong>
                                <small class="d-block text-muted">{{ $interaction->subject }} &middot; {{ $interaction->interaction_date->format('m/d H:i') }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center text-muted py-3">尚無互動紀錄</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong>最近完成任務</strong></div>
            <div class="card-body p-0">
                @if(isset($recentCompletedTasks) && $recentCompletedTasks->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($recentCompletedTasks as $task)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $task->title }}</strong>
                                <small class="d-block text-muted">{{ $task->project?->title ?? '無專案' }}</small>
                            </div>
                            <small class="text-muted">{{ $task->completed_at?->format('m/d H:i') }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center text-muted py-3">尚無完成的任務</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 最新報價請求 --}}
@if(isset($recentQuoteRequests) && $recentQuoteRequests->count() > 0)
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>最新報價請求</strong>
                <a href="{{ route('admin.quote-requests.index') }}" class="btn btn-sm btn-link text-decoration-none">
                    查看全部
                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-arrow-right"></use></svg>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <tbody>
                            @foreach($recentQuoteRequests as $qr)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.quote-requests.show', $qr) }}">{{ $qr->request_number }}</a>
                                </td>
                                <td>
                                    <strong>{{ $qr->name }}</strong>
                                    <small class="d-block text-muted">{{ $qr->email }}</small>
                                </td>
                                <td>{{ $qr->project_type }}</td>
                                <td class="text-end">
                                    <span class="badge bg-{{ $qr->status_color }}">{{ $qr->status_label }}</span>
                                </td>
                                <td class="text-end text-muted small">{{ $qr->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-3">
    {{-- 最近文章 --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <strong>最近文章</strong>
            </div>
            <div class="card-body p-0">
                @if($recentArticles && $recentArticles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tbody>
                                @foreach($recentArticles as $article)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $article->title }}</div>
                                        <small class="text-muted">
                                            {{ $article->display_author_name }} •
                                            {{ $article->created_at->diffForHumans() }}
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ $article->status_color }}">
                                            {{ $article->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">📝</div>
                        <div>尚無文章</div>
                    </div>
                @endif
            </div>
            @if($recentArticles && $recentArticles->count() > 0)
            <div class="card-footer">
                <a href="{{ route('admin.articles.index') }}" class="btn btn-sm btn-link">
                    查看全部文章
                    <svg class="icon">
                        <use xlink:href="/assets/icons/free.svg#cil-arrow-right"></use>
                    </svg>
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- 熱門文章 --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <strong>熱門文章</strong>
            </div>
            <div class="card-body p-0">
                @if($popularArticles && $popularArticles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tbody>
                                @foreach($popularArticles as $article)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $article->title }}</div>
                                        <small class="text-muted">
                                            {{ $article->category->name ?? 'Uncategorized' }}
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <div class="text-muted small">
                                            <svg class="icon">
                                                <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                                            </svg>
                                            {{ number_format($article->views_count) }}
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">📊</div>
                        <div>暫無數據</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 快速分析 --}}
<div class="row g-3 mt-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>最近 7 天瀏覽量趨勢</strong>
                <a href="{{ route('admin.analytics.index') }}" class="btn btn-sm btn-link text-decoration-none">
                    完整分析報告
                    <svg class="icon">
                        <use xlink:href="/assets/icons/free.svg#cil-arrow-right"></use>
                    </svg>
                </a>
            </div>
            <div class="card-body">
                @if(isset($dailyViews) && count($dailyViews) > 0)
                <div class="chart-container" style="position: relative; height: 220px;">
                    <canvas id="dailyViewsChart"></canvas>
                </div>
                @else
                <div class="text-center text-muted py-4">暫無瀏覽量資料</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <strong>今日熱門頁面</strong>
            </div>
            <div class="card-body p-0">
                @if(isset($todayTopPages) && count($todayTopPages) > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <tbody>
                            @foreach($todayTopPages as $page)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-truncate" style="max-width: 200px;" title="{{ $page['page_url'] }}">
                                        {{ $page['page_title'] ?: $page['page_url'] }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-info">{{ number_format($page['views']) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <div class="mb-1">今日尚無資料</div>
                </div>
                @endif
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.analytics.index') }}" class="btn btn-sm btn-link">
                    查看完整分析
                    <svg class="icon">
                        <use xlink:href="/assets/icons/free.svg#cil-arrow-right"></use>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(isset($dailyViews) && count($dailyViews) > 0)
    const ctx = document.getElementById('dailyViewsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($dailyViews, 'label')) !!},
                datasets: [{
                    label: '瀏覽量',
                    data: {!! json_encode(array_column($dailyViews, 'count')) !!},
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(item) {
                                return '瀏覽量: ' + new Intl.NumberFormat('zh-TW').format(item.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
    @endif
});
</script>
@endpush
@endsection
