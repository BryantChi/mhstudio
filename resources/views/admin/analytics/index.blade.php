@extends('layouts.admin')

@section('title', '流量分析')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">流量分析</h2>
                <p class="text-muted mb-0">網站流量與訪客行為分析</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <select id="dateRange" class="form-select form-select-sm" style="width: auto;">
                    <option value="7">最近 7 天</option>
                    <option value="30" selected>最近 30 天</option>
                    <option value="90">最近 90 天</option>
                    <option value="custom">自訂範圍</option>
                </select>
                <div id="customDateRange" class="d-none d-flex align-items-center gap-2">
                    <input type="date" id="startDate" class="form-control form-control-sm">
                    <span class="text-muted">至</span>
                    <input type="date" id="endDate" class="form-control form-control-sm">
                    <button id="applyCustomDate" class="btn btn-sm btn-primary">套用</button>
                </div>
                <button id="refreshData" class="btn btn-sm btn-outline-secondary" title="重新整理">
                    <svg class="icon">
                        <use xlink:href="/assets/icons/free.svg#cil-reload"></use>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 快速統計 --}}
<div class="row g-3 mb-4" id="quickStats">
    <div class="col-12 text-center py-3">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">載入中...</span>
        </div>
    </div>
</div>

{{-- 統計卡片 --}}
<div class="row g-3 mb-4 d-none" id="statCards">
    <div class="col-sm-6 col-lg-3">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold text-primary" id="statTotalViews">-</div>
                        <div class="text-muted small text-uppercase fw-semibold">總瀏覽量</div>
                    </div>
                    <div class="text-primary">
                        <svg class="icon icon-xl">
                            <use xlink:href="/assets/icons/free.svg#cil-chart-line"></use>
                        </svg>
                    </div>
                </div>
                <div class="mt-2 small" id="trendViews"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold text-success" id="statUniqueVisitors">-</div>
                        <div class="text-muted small text-uppercase fw-semibold">不重複訪客</div>
                    </div>
                    <div class="text-success">
                        <svg class="icon icon-xl">
                            <use xlink:href="/assets/icons/free.svg#cil-people"></use>
                        </svg>
                    </div>
                </div>
                <div class="mt-2 small" id="trendVisitors"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold text-warning" id="statPagesPerSession">-</div>
                        <div class="text-muted small text-uppercase fw-semibold">頁面/工作階段</div>
                    </div>
                    <div class="text-warning">
                        <svg class="icon icon-xl">
                            <use xlink:href="/assets/icons/free.svg#cil-layers"></use>
                        </svg>
                    </div>
                </div>
                <div class="mt-2 small" id="trendPagesPerSession"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold text-info" id="statBounceRate">-</div>
                        <div class="text-muted small text-uppercase fw-semibold">跳出率</div>
                    </div>
                    <div class="text-info">
                        <svg class="icon icon-xl">
                            <use xlink:href="/assets/icons/free.svg#cil-arrow-circle-bottom"></use>
                        </svg>
                    </div>
                </div>
                <div class="mt-2 small" id="trendBounceRate"></div>
            </div>
        </div>
    </div>
</div>

{{-- 時間區間統計 --}}
<div class="row g-3 mb-4 d-none" id="timeStats">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 bg-light">
            <div class="card-body text-center py-2">
                <div class="fw-semibold" id="statTodayViews">-</div>
                <div class="text-muted small">今日</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 bg-light">
            <div class="card-body text-center py-2">
                <div class="fw-semibold" id="statWeekViews">-</div>
                <div class="text-muted small">本週</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 bg-light">
            <div class="card-body text-center py-2">
                <div class="fw-semibold" id="statMonthViews">-</div>
                <div class="text-muted small">本月</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 bg-light">
            <div class="card-body text-center py-2">
                <div class="fw-semibold" id="statAllTimeViews">-</div>
                <div class="text-muted small">全部</div>
            </div>
        </div>
    </div>
</div>

{{-- 瀏覽量趨勢圖表 --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>瀏覽量趨勢</strong>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active" data-group-by="daily">每日</button>
                    <button type="button" class="btn btn-outline-secondary" data-group-by="weekly">每週</button>
                    <button type="button" class="btn btn-outline-secondary" data-group-by="monthly">每月</button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="viewsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 熱門頁面 & 來源 --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <strong>熱門頁面</strong>
            </div>
            <div class="card-body p-0">
                <div id="topPagesLoading" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">載入中...</span>
                    </div>
                </div>
                <div id="topPagesContent" class="d-none">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>頁面</th>
                                    <th class="text-end" style="width: 80px;">瀏覽量</th>
                                    <th class="text-end" style="width: 80px;">不重複</th>
                                </tr>
                            </thead>
                            <tbody id="topPagesBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="topPagesEmpty" class="d-none">
                    <div class="text-center text-muted py-4">暫無數據</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <strong>熱門來源</strong>
            </div>
            <div class="card-body p-0">
                <div id="topReferrersLoading" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">載入中...</span>
                    </div>
                </div>
                <div id="topReferrersContent" class="d-none">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>來源</th>
                                    <th class="text-end" style="width: 80px;">造訪</th>
                                    <th class="text-end" style="width: 80px;">不重複</th>
                                </tr>
                            </thead>
                            <tbody id="topReferrersBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="topReferrersEmpty" class="d-none">
                    <div class="text-center text-muted py-4">暫無數據</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 裝置分布 --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <strong>裝置分布</strong>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 280px;">
                    <canvas id="deviceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <strong>裝置統計</strong>
            </div>
            <div class="card-body">
                <div id="deviceStatsLoading" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">載入中...</span>
                    </div>
                </div>
                <div id="deviceStatsContent" class="d-none">
                    <div id="deviceStatsList"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 狀態管理
    let currentRange = '30';
    let currentGroupBy = 'daily';
    let viewsChart = null;
    let deviceChart = null;

    const dateRangeSelect = document.getElementById('dateRange');
    const customDateRange = document.getElementById('customDateRange');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const applyCustomDateBtn = document.getElementById('applyCustomDate');
    const refreshBtn = document.getElementById('refreshData');
    const groupByBtns = document.querySelectorAll('[data-group-by]');

    // 初始化日期
    const today = new Date();
    endDateInput.value = today.toISOString().split('T')[0];
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
    startDateInput.value = thirtyDaysAgo.toISOString().split('T')[0];

    // 事件綁定
    dateRangeSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customDateRange.classList.remove('d-none');
            return;
        }
        customDateRange.classList.add('d-none');
        currentRange = this.value;
        loadAllData();
    });

    applyCustomDateBtn.addEventListener('click', function() {
        currentRange = 'custom';
        loadAllData();
    });

    refreshBtn.addEventListener('click', function() {
        loadAllData();
    });

    groupByBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            groupByBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentGroupBy = this.dataset.groupBy;
            loadChartData();
        });
    });

    // API 請求
    function getQueryParams() {
        const params = new URLSearchParams();
        if (currentRange === 'custom') {
            params.set('range', 'custom');
            params.set('start_date', startDateInput.value);
            params.set('end_date', endDateInput.value);
        } else {
            params.set('range', currentRange);
        }
        return params;
    }

    function loadAllData() {
        loadOverview();
        loadChartData();
        loadPages();
        loadReferrers();
    }

    function loadOverview() {
        const params = getQueryParams();
        document.getElementById('quickStats').classList.remove('d-none');
        document.getElementById('statCards').classList.add('d-none');
        document.getElementById('timeStats').classList.add('d-none');

        fetch(`{{ route('admin.analytics.api.overview') }}?${params}`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('quickStats').classList.add('d-none');
                document.getElementById('statCards').classList.remove('d-none');
                document.getElementById('timeStats').classList.remove('d-none');

                document.getElementById('statTotalViews').textContent = numberFormat(data.total_views);
                document.getElementById('statUniqueVisitors').textContent = numberFormat(data.unique_visitors);
                document.getElementById('statPagesPerSession').textContent = data.pages_per_session;
                document.getElementById('statBounceRate').textContent = data.bounce_rate + '%';

                renderTrend('trendViews', data.trends.views);
                renderTrend('trendVisitors', data.trends.visitors);
                renderTrend('trendPagesPerSession', data.trends.pages_per_session);
                renderTrend('trendBounceRate', data.trends.bounce_rate);

                document.getElementById('statTodayViews').textContent = numberFormat(data.today_views);
                document.getElementById('statWeekViews').textContent = numberFormat(data.week_views);
                document.getElementById('statMonthViews').textContent = numberFormat(data.month_views);
                document.getElementById('statAllTimeViews').textContent = numberFormat(data.all_time_views);
            })
            .catch(err => {
                console.error('載入總覽失敗:', err);
                document.getElementById('quickStats').innerHTML = '<div class="col-12 text-center text-danger py-3">載入失敗，請重試</div>';
            });
    }

    function loadChartData() {
        const params = getQueryParams();
        params.set('group_by', currentGroupBy);

        fetch(`{{ route('admin.analytics.api.chart') }}?${params}`)
            .then(r => r.json())
            .then(data => {
                renderViewsChart(data.chart);
                renderDeviceChart(data.devices);
            })
            .catch(err => console.error('載入圖表失敗:', err));
    }

    function loadPages() {
        const params = getQueryParams();
        showLoading('topPages');

        fetch(`{{ route('admin.analytics.api.pages') }}?${params}`)
            .then(r => r.json())
            .then(data => {
                if (data.pages.length === 0) {
                    showEmpty('topPages');
                    return;
                }
                showContent('topPages');
                const tbody = document.getElementById('topPagesBody');
                tbody.innerHTML = data.pages.map((page, i) => `
                    <tr>
                        <td>
                            <div class="fw-semibold text-truncate" style="max-width: 250px;" title="${escapeHtml(page.page_url)}">
                                ${escapeHtml(page.page_title || page.page_url)}
                            </div>
                            <small class="text-muted text-truncate d-block" style="max-width: 250px;">${escapeHtml(shortenUrl(page.page_url))}</small>
                        </td>
                        <td class="text-end">${numberFormat(page.views)}</td>
                        <td class="text-end">${numberFormat(page.unique_views)}</td>
                    </tr>
                `).join('');
            })
            .catch(err => {
                console.error('載入熱門頁面失敗:', err);
                showEmpty('topPages');
            });
    }

    function loadReferrers() {
        const params = getQueryParams();
        showLoading('topReferrers');

        fetch(`{{ route('admin.analytics.api.referrers') }}?${params}`)
            .then(r => r.json())
            .then(data => {
                if (data.referrers.length === 0) {
                    showEmpty('topReferrers');
                    return;
                }
                showContent('topReferrers');
                const tbody = document.getElementById('topReferrersBody');
                tbody.innerHTML = data.referrers.map(ref => `
                    <tr>
                        <td>
                            <div class="text-truncate" style="max-width: 280px;" title="${escapeHtml(ref.referrer_url)}">
                                ${escapeHtml(shortenUrl(ref.referrer_url))}
                            </div>
                        </td>
                        <td class="text-end">${numberFormat(ref.visits)}</td>
                        <td class="text-end">${numberFormat(ref.unique_visits)}</td>
                    </tr>
                `).join('');
            })
            .catch(err => {
                console.error('載入熱門來源失敗:', err);
                showEmpty('topReferrers');
            });
    }

    // 圖表渲染
    function renderViewsChart(data) {
        const ctx = document.getElementById('viewsChart');
        if (viewsChart) {
            viewsChart.destroy();
        }

        viewsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.label),
                datasets: [{
                    label: '瀏覽量',
                    data: data.map(d => d.views),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: data.length > 60 ? 0 : 3,
                    pointHoverRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: function(items) {
                                return items[0].label;
                            },
                            label: function(item) {
                                return `瀏覽量: ${numberFormat(item.raw)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    },
                    x: {
                        ticks: {
                            maxTicksLimit: 15,
                            maxRotation: 45,
                        }
                    }
                }
            }
        });
    }

    function renderDeviceChart(devices) {
        const ctx = document.getElementById('deviceChart');
        if (deviceChart) {
            deviceChart.destroy();
        }

        const colors = ['#3b82f6', '#10b981', '#f59e0b'];
        const hasData = devices.some(d => d.value > 0);

        document.getElementById('deviceStatsLoading').classList.add('d-none');
        document.getElementById('deviceStatsContent').classList.remove('d-none');

        // 裝置統計列表
        const statsList = document.getElementById('deviceStatsList');
        statsList.innerHTML = devices.map((d, i) => `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle me-2" style="width: 12px; height: 12px; background-color: ${colors[i]};"></div>
                    <span>${d.label}</span>
                </div>
                <div>
                    <span class="fw-semibold">${numberFormat(d.value)}</span>
                    <span class="text-muted ms-1">(${d.percentage}%)</span>
                </div>
            </div>
            <div class="progress mb-3" style="height: 6px;">
                <div class="progress-bar" style="width: ${d.percentage}%; background-color: ${colors[i]};"></div>
            </div>
        `).join('');

        if (!hasData) {
            return;
        }

        deviceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: devices.map(d => d.label),
                datasets: [{
                    data: devices.map(d => d.value),
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(item) {
                                const d = devices[item.dataIndex];
                                return `${d.label}: ${numberFormat(d.value)} (${d.percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // 工具函式
    function numberFormat(num) {
        return new Intl.NumberFormat('zh-TW').format(num);
    }

    function renderTrend(elementId, trend) {
        const el = document.getElementById(elementId);
        if (!el) return;

        if (trend.direction === 'flat') {
            el.innerHTML = '<span class="text-muted">-- 持平</span>';
            return;
        }

        const colorClass = trend.is_positive ? 'text-success' : 'text-danger';
        const arrow = trend.direction === 'up' ? '&#9650;' : '&#9660;';
        el.innerHTML = `<span class="${colorClass}">${arrow} ${trend.percentage}%</span> <span class="text-muted">較前期</span>`;
    }

    function showLoading(prefix) {
        document.getElementById(prefix + 'Loading').classList.remove('d-none');
        document.getElementById(prefix + 'Content').classList.add('d-none');
        document.getElementById(prefix + 'Empty').classList.add('d-none');
    }

    function showContent(prefix) {
        document.getElementById(prefix + 'Loading').classList.add('d-none');
        document.getElementById(prefix + 'Content').classList.remove('d-none');
        document.getElementById(prefix + 'Empty').classList.add('d-none');
    }

    function showEmpty(prefix) {
        document.getElementById(prefix + 'Loading').classList.add('d-none');
        document.getElementById(prefix + 'Content').classList.add('d-none');
        document.getElementById(prefix + 'Empty').classList.remove('d-none');
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function shortenUrl(url) {
        if (!url) return '';
        try {
            const u = new URL(url);
            return u.pathname + u.search;
        } catch {
            return url;
        }
    }

    // 初始載入
    loadAllData();
});
</script>
@endpush
@endsection
