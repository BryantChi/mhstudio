@extends('layouts.admin')

@section('title', '綜合報表中心')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="mb-0">綜合報表中心</h2>
                <p class="text-muted mb-0">營收財務、客戶分析與工時計費彙總</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <select id="reportRange" class="form-select form-select-sm" style="width: auto;">
                    <option value="month" selected>本月</option>
                    <option value="year">本年</option>
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

{{-- 報表頁籤 --}}
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-revenue" data-coreui-toggle="tab" data-coreui-target="#pane-revenue" type="button" role="tab" data-report="revenue">營收財務</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-clients" data-coreui-toggle="tab" data-coreui-target="#pane-clients" type="button" role="tab" data-report="clients">客戶分析</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-time" data-coreui-toggle="tab" data-coreui-target="#pane-time" type="button" role="tab" data-report="time">工時計費</button>
    </li>
</ul>

<div class="tab-content">
    {{-- ===== 營收財務 ===== --}}
    <div class="tab-pane fade show active" id="pane-revenue" role="tabpanel">
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fs-5 fw-semibold text-success" id="revRevenue">-</div>
                                <div class="text-muted small text-uppercase fw-semibold">區間營收</div>
                            </div>
                            <div class="text-success">
                                <svg class="icon icon-xl"><use xlink:href="/assets/icons/free.svg#cil-dollar"></use></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fs-5 fw-semibold text-warning" id="revOutstanding">-</div>
                                <div class="text-muted small text-uppercase fw-semibold">未收款</div>
                            </div>
                            <div class="text-warning">
                                <svg class="icon icon-xl"><use xlink:href="/assets/icons/free.svg#cil-wallet"></use></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fs-5 fw-semibold text-danger" id="revOverdue">-</div>
                                <div class="text-muted small text-uppercase fw-semibold">逾期金額</div>
                            </div>
                            <div class="text-danger">
                                <svg class="icon icon-xl"><use xlink:href="/assets/icons/free.svg#cil-warning"></use></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fs-5 fw-semibold text-primary" id="revCount">-</div>
                                <div class="text-muted small text-uppercase fw-semibold">收款筆數</div>
                            </div>
                            <div class="text-primary">
                                <svg class="icon icon-xl"><use xlink:href="/assets/icons/free.svg#cil-list-rich"></use></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>月營收趨勢</strong> <span class="text-muted small">（現金基礎，依實收日）</span></div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="revenueChart"></canvas>
                </div>
                <div id="revenueEmpty" class="d-none text-center text-muted py-4">此區間暫無收款資料</div>
            </div>
        </div>
    </div>

    {{-- ===== 客戶分析 ===== --}}
    <div class="tab-pane fade" id="pane-clients" role="tabpanel">
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 bg-light"><div class="card-body text-center py-3">
                    <div class="fs-5 fw-semibold" id="cliTotal">-</div>
                    <div class="text-muted small">客戶總數</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 bg-light"><div class="card-body text-center py-3">
                    <div class="fs-5 fw-semibold text-success" id="cliActive">-</div>
                    <div class="text-muted small">活躍客戶</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 bg-light"><div class="card-body text-center py-3">
                    <div class="fs-5 fw-semibold text-info" id="cliLeads">-</div>
                    <div class="text-muted small">潛在客戶</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 bg-light"><div class="card-body text-center py-3">
                    <div class="fs-5 fw-semibold text-primary" id="cliNew">-</div>
                    <div class="text-muted small">區間新增</div>
                </div></div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header"><strong>客戶分級</strong></div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 260px;">
                            <canvas id="tierChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header"><strong>客戶來源</strong></div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 260px;">
                            <canvas id="sourceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Top 10 營收客戶</strong> <span class="text-muted small">（累計實收）</span></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>客戶</th>
                                <th class="text-end">累計營收</th>
                            </tr>
                        </thead>
                        <tbody id="cliTopBody">
                            <tr><td colspan="3" class="text-center text-muted py-4">載入中...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== 工時計費 ===== --}}
    <div class="tab-pane fade" id="pane-time" role="tabpanel">
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 bg-light"><div class="card-body text-center py-3">
                    <div class="fs-5 fw-semibold" id="timeTotal">-</div>
                    <div class="text-muted small">總工時</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 bg-light"><div class="card-body text-center py-3">
                    <div class="fs-5 fw-semibold text-success" id="timeBillable">-</div>
                    <div class="text-muted small">可計費工時</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 bg-light"><div class="card-body text-center py-3">
                    <div class="fs-5 fw-semibold text-primary" id="timeAmount">-</div>
                    <div class="text-muted small">可計費金額</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 bg-light"><div class="card-body text-center py-3">
                    <div class="fs-5 fw-semibold text-info" id="timeRate">-</div>
                    <div class="text-muted small">計費率</div>
                </div></div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header"><strong>各專案工時</strong></div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 300px;">
                            <canvas id="projectChart"></canvas>
                        </div>
                        <div id="projectEmpty" class="d-none text-center text-muted py-4">此區間暫無工時資料</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header"><strong>專案明細</strong></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>專案</th>
                                        <th class="text-end">工時</th>
                                    </tr>
                                </thead>
                                <tbody id="timeProjectBody">
                                    <tr><td colspan="2" class="text-center text-muted py-4">載入中...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const PALETTE = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899'];

    let currentRange = 'month';
    let loaded = { revenue: false, clients: false, time: false };
    let revenueChart = null, tierChart = null, sourceChart = null, projectChart = null;

    const rangeSelect = document.getElementById('reportRange');
    const customDateRange = document.getElementById('customDateRange');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const applyCustomBtn = document.getElementById('applyCustomDate');
    const refreshBtn = document.getElementById('refreshData');

    // 初始化日期輸入
    const today = new Date();
    endDateInput.value = today.toISOString().split('T')[0];
    const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
    startDateInput.value = monthStart.toISOString().split('T')[0];

    // 區間變更
    rangeSelect.addEventListener('change', function () {
        if (this.value === 'custom') {
            customDateRange.classList.remove('d-none');
            return;
        }
        customDateRange.classList.add('d-none');
        currentRange = this.value;
        reloadActiveTab(true);
    });
    applyCustomBtn.addEventListener('click', function () {
        currentRange = 'custom';
        reloadActiveTab(true);
    });
    refreshBtn.addEventListener('click', function () {
        loaded = { revenue: false, clients: false, time: false };
        loadReport(activeReport());
    });

    // 頁籤切換時延後載入
    document.querySelectorAll('[data-report]').forEach(function (btn) {
        btn.addEventListener('shown.coreui.tab', function () {
            loadReport(this.dataset.report);
        });
    });

    function activeReport() {
        const btn = document.querySelector('[data-report].active');
        return btn ? btn.dataset.report : 'revenue';
    }

    // 區間變更：當前 tab 立即重載，其餘標記未載入
    function reloadActiveTab(resetOthers) {
        if (resetOthers) loaded = { revenue: false, clients: false, time: false };
        loadReport(activeReport());
    }

    function getQueryParams() {
        const params = new URLSearchParams();
        if (currentRange === 'custom') {
            params.set('range', 'custom');
            params.set('from', startDateInput.value);
            params.set('to', endDateInput.value);
        } else {
            params.set('range', currentRange);
        }
        return params;
    }

    function loadReport(report) {
        if (loaded[report]) return;
        loaded[report] = true;
        if (report === 'revenue') loadRevenue();
        else if (report === 'clients') loadClients();
        else if (report === 'time') loadTime();
    }

    // ===== 營收財務 =====
    function loadRevenue() {
        fetch(`{{ route('admin.reports.api.revenue') }}?${getQueryParams()}`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('revRevenue').textContent = currency(data.revenue);
                document.getElementById('revOutstanding').textContent = currency(data.outstanding);
                document.getElementById('revOverdue').textContent = currency(data.overdue);
                document.getElementById('revCount').textContent = numberFormat(data.count);
                renderRevenueChart(data.trend);
            })
            .catch(err => { console.error('載入營收報表失敗:', err); loaded.revenue = false; });
    }

    function renderRevenueChart(trend) {
        const empty = document.getElementById('revenueEmpty');
        const canvas = document.getElementById('revenueChart');
        if (revenueChart) { revenueChart.destroy(); revenueChart = null; }

        if (!trend || trend.length === 0) {
            empty.classList.remove('d-none');
            canvas.classList.add('d-none');
            return;
        }
        empty.classList.add('d-none');
        canvas.classList.remove('d-none');

        revenueChart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: trend.map(d => d.label),
                datasets: [{
                    label: '營收',
                    data: trend.map(d => d.total),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: item => '營收: ' + currency(item.raw) } }
                },
                scales: { y: { beginAtZero: true, ticks: { callback: v => numberFormat(v) } } }
            }
        });
    }

    // ===== 客戶分析 =====
    function loadClients() {
        fetch(`{{ route('admin.reports.api.clients') }}?${getQueryParams()}`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('cliTotal').textContent = numberFormat(data.total);
                document.getElementById('cliActive').textContent = numberFormat(data.active);
                document.getElementById('cliLeads').textContent = numberFormat(data.leads);
                document.getElementById('cliNew').textContent = numberFormat(data.new_in_range);

                renderPieChart('tierChart', tierChart, data.by_tier, TIER_LABELS).then(c => tierChart = c);
                renderPieChart('sourceChart', sourceChart, data.by_source, SOURCE_LABELS).then(c => sourceChart = c);

                const body = document.getElementById('cliTopBody');
                if (!data.top_revenue || data.top_revenue.length === 0) {
                    body.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">暫無資料</td></tr>';
                } else {
                    body.innerHTML = data.top_revenue.map((c, i) => `
                        <tr>
                            <td>${i + 1}</td>
                            <td class="fw-semibold">${escapeHtml(c.name)}</td>
                            <td class="text-end">${currency(c.total_revenue)}</td>
                        </tr>`).join('');
                }
            })
            .catch(err => { console.error('載入客戶報表失敗:', err); loaded.clients = false; });
    }

    const TIER_LABELS = { vip: 'VIP', premium: '高級', standard: '標準' };
    const SOURCE_LABELS = { website: '網站', referral: '轉介', social: '社群', cold_outreach: '主動開發', other: '其他' };

    // 回傳 Promise 以便重新賦值 chart 變數
    function renderPieChart(canvasId, existing, dataObj, labelMap) {
        return new Promise(resolve => {
            if (existing) existing.destroy();
            const entries = Object.entries(dataObj || {}).filter(([, v]) => v > 0);
            const ctx = document.getElementById(canvasId);
            if (entries.length === 0) { resolve(null); return; }
            const chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: entries.map(([k]) => (labelMap && labelMap[k]) || k || '未分類'),
                    datasets: [{
                        data: entries.map(([, v]) => v),
                        backgroundColor: PALETTE,
                        borderWidth: 2,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true } } }
                }
            });
            resolve(chart);
        });
    }

    // ===== 工時計費 =====
    function loadTime() {
        fetch(`{{ route('admin.reports.api.time') }}?${getQueryParams()}`)
            .then(r => r.json())
            .then(data => {
                const rate = data.total_minutes > 0
                    ? Math.round(data.billable_minutes / data.total_minutes * 100) : 0;
                document.getElementById('timeTotal').textContent = minutesToHours(data.total_minutes);
                document.getElementById('timeBillable').textContent = minutesToHours(data.billable_minutes);
                document.getElementById('timeAmount').textContent = currency(data.billable_amount);
                document.getElementById('timeRate').textContent = rate + '%';

                renderProjectChart(data.by_project);

                const body = document.getElementById('timeProjectBody');
                if (!data.by_project || data.by_project.length === 0) {
                    body.innerHTML = '<tr><td colspan="2" class="text-center text-muted py-4">暫無資料</td></tr>';
                } else {
                    body.innerHTML = data.by_project.map(p => `
                        <tr>
                            <td>${escapeHtml(p.project)}</td>
                            <td class="text-end">${minutesToHours(p.minutes)}</td>
                        </tr>`).join('');
                }
            })
            .catch(err => { console.error('載入工時報表失敗:', err); loaded.time = false; });
    }

    function renderProjectChart(projects) {
        const empty = document.getElementById('projectEmpty');
        const canvas = document.getElementById('projectChart');
        if (projectChart) { projectChart.destroy(); projectChart = null; }

        if (!projects || projects.length === 0) {
            empty.classList.remove('d-none');
            canvas.classList.add('d-none');
            return;
        }
        empty.classList.add('d-none');
        canvas.classList.remove('d-none');

        projectChart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: projects.map(p => p.project),
                datasets: [{
                    label: '工時',
                    data: projects.map(p => Math.round(p.minutes / 60 * 10) / 10),
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: item => item.raw + ' 小時' } }
                },
                scales: { x: { beginAtZero: true, title: { display: true, text: '小時' } } }
            }
        });
    }

    // ===== 工具函式 =====
    function numberFormat(num) {
        return new Intl.NumberFormat('zh-TW').format(num || 0);
    }
    function currency(num) {
        return 'NT$ ' + numberFormat(Math.round(num || 0));
    }
    function minutesToHours(min) {
        return (Math.round((min || 0) / 60 * 10) / 10) + ' 小時';
    }
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // 初始載入第一個頁籤
    loadReport('revenue');
});
</script>
@endpush
@endsection
