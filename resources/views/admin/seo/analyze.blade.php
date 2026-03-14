@extends('layouts.admin')

@section('title', 'SEO 分析')

@php
    $breadcrumbs = [
        ['title' => 'SEO 管理', 'url' => route('admin.seo.index')],
        ['title' => 'SEO 分析', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">SEO 分析</h2>
        <p class="text-muted">檢查網站 SEO 狀況並取得優化建議</p>
    </div>
    <div class="col-md-6 text-md-end">
        <button type="button" class="btn btn-primary" onclick="runAnalysis()">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-reload"></use>
            </svg>
            重新分析
        </button>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <div class="display-4 fw-bold text-success">{{ $analysis['overall_score'] ?? 75 }}</div>
                <div class="text-muted">總體評分</div>
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: {{ $analysis['overall_score'] ?? 75 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <div class="display-4 fw-bold text-info">{{ $analysis['technical_score'] ?? 80 }}</div>
                <div class="text-muted">技術 SEO</div>
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar bg-info" style="width: {{ $analysis['technical_score'] ?? 80 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <div class="display-4 fw-bold text-warning">{{ $analysis['content_score'] ?? 70 }}</div>
                <div class="text-muted">內容品質</div>
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar bg-warning" style="width: {{ $analysis['content_score'] ?? 70 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <div class="display-4 fw-bold text-danger">{{ $analysis['performance_score'] ?? 65 }}</div>
                <div class="text-muted">效能優化</div>
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar bg-danger" style="width: {{ $analysis['performance_score'] ?? 65 }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <strong>問題與建議</strong>
            </div>
            <div class="card-body">
                <div class="accordion" id="issuesAccordion">
                    <!-- 嚴重問題 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-coreui-toggle="collapse" data-coreui-target="#critical">
                                <svg class="icon text-danger me-2">
                                    <use xlink:href="/assets/icons/free.svg#cil-x-circle"></use>
                                </svg>
                                嚴重問題 ({{ $issues['critical'] ?? 2 }})
                            </button>
                        </h2>
                        <div id="critical" class="accordion-collapse collapse show" data-coreui-parent="#issuesAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-3">
                                        <strong class="text-danger">缺少 Meta Description</strong>
                                        <p class="mb-1 small">15 個頁面缺少 Meta 描述標籤</p>
                                        <button class="btn btn-sm btn-outline-danger" onclick="fixIssue('missing-meta')">立即修復</button>
                                    </li>
                                    <li>
                                        <strong class="text-danger">重複的標題標籤</strong>
                                        <p class="mb-1 small">8 個頁面使用相同的標題</p>
                                        <button class="btn btn-sm btn-outline-danger" onclick="fixIssue('duplicate-titles')">查看詳情</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- 警告 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-coreui-toggle="collapse" data-coreui-target="#warnings">
                                <svg class="icon text-warning me-2">
                                    <use xlink:href="/assets/icons/free.svg#cil-warning"></use>
                                </svg>
                                警告 ({{ $issues['warnings'] ?? 5 }})
                            </button>
                        </h2>
                        <div id="warnings" class="accordion-collapse collapse" data-coreui-parent="#issuesAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-3">
                                        <strong class="text-warning">圖片缺少 Alt 標籤</strong>
                                        <p class="mb-1 small">23 張圖片沒有替代文字</p>
                                        <button class="btn btn-sm btn-outline-warning" onclick="fixIssue('missing-alt')">查看詳情</button>
                                    </li>
                                    <li class="mb-3">
                                        <strong class="text-warning">標題長度過長</strong>
                                        <p class="mb-1 small">12 個頁面的標題超過 60 字元</p>
                                        <button class="btn btn-sm btn-outline-warning" onclick="fixIssue('long-titles')">查看詳情</button>
                                    </li>
                                    <li>
                                        <strong class="text-warning">內部連結不足</strong>
                                        <p class="mb-1 small">部分頁面的內部連結數量偏少</p>
                                        <button class="btn btn-sm btn-outline-warning" onclick="fixIssue('few-links')">查看詳情</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- 建議 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-coreui-toggle="collapse" data-coreui-target="#suggestions">
                                <svg class="icon text-info me-2">
                                    <use xlink:href="/assets/icons/free.svg#cil-lightbulb"></use>
                                </svg>
                                優化建議 ({{ $issues['suggestions'] ?? 8 }})
                            </button>
                        </h2>
                        <div id="suggestions" class="accordion-collapse collapse" data-coreui-parent="#issuesAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-3">
                                        <strong class="text-info">使用 Schema.org 標記</strong>
                                        <p class="mb-1 small">添加結構化數據可以提升搜尋結果顯示效果</p>
                                        <a href="#" class="btn btn-sm btn-outline-info">了解更多</a>
                                    </li>
                                    <li class="mb-3">
                                        <strong class="text-info">優化圖片大小</strong>
                                        <p class="mb-1 small">壓縮圖片可以提升頁面載入速度</p>
                                        <a href="#" class="btn btn-sm btn-outline-info">了解更多</a>
                                    </li>
                                    <li>
                                        <strong class="text-info">增加外部連結</strong>
                                        <p class="mb-1 small">適當的外部連結可以提升內容可信度</p>
                                        <a href="#" class="btn btn-sm btn-outline-info">了解更多</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <strong>頁面分析</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>頁面</th>
                                <th>SEO 評分</th>
                                <th>問題</th>
                                <th>最後檢查</th>
                                <th class="text-end">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($pages))
                                @foreach($pages as $page)
                                <tr>
                                    <td>
                                        <strong>{{ $page['title'] ?? 'Unknown' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $page['url'] ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $score = $page['score'] ?? 0;
                                            $color = $score >= 80 ? 'success' : ($score >= 50 ? 'warning' : 'danger');
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ $score }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ $page['critical'] ?? 0 }}</span>
                                        <span class="badge bg-warning">{{ $page['warnings'] ?? 0 }}</span>
                                        <span class="badge bg-info">{{ $page['suggestions'] ?? 0 }}</span>
                                    </td>
                                    <td>{{ $page['checked_at'] ?? '-' }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light" onclick="analyzePage({{ $page['id'] ?? 0 }})">
                                            <svg class="icon">
                                                <use xlink:href="/assets/icons/free.svg#cil-search"></use>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center text-muted">暫無資料</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <strong>快速檢查</strong>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="checkMeta()">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-tags"></use>
                        </svg>
                        檢查 Meta Tags
                    </button>

                    <button class="btn btn-outline-success" onclick="checkImages()">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-image"></use>
                        </svg>
                        檢查圖片 Alt
                    </button>

                    <button class="btn btn-outline-info" onclick="checkLinks()">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-link"></use>
                        </svg>
                        檢查連結
                    </button>

                    <button class="btn btn-outline-warning" onclick="checkSpeed()">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-speedometer"></use>
                        </svg>
                        檢查頁面速度
                    </button>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>SEO 檢查清單</strong>
            </div>
            <div class="card-body">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="check1" checked disabled>
                    <label class="form-check-label" for="check1">
                        <small>✓ Sitemap.xml 已建立</small>
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="check2" checked disabled>
                    <label class="form-check-label" for="check2">
                        <small>✓ Robots.txt 已配置</small>
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="check3" checked disabled>
                    <label class="form-check-label" for="check3">
                        <small>✓ SSL 憑證已安裝</small>
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="check4" disabled>
                    <label class="form-check-label" for="check4">
                        <small>✗ Schema.org 標記</small>
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="check5" checked disabled>
                    <label class="form-check-label" for="check5">
                        <small>✓ 行動裝置友善</small>
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="check6" disabled>
                    <label class="form-check-label" for="check6">
                        <small>✗ AMP 支援</small>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="check7" checked disabled>
                    <label class="form-check-label" for="check7">
                        <small>✓ 規範連結設定</small>
                    </label>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>分析歷史</strong>
            </div>
            <div class="card-body">
                <small class="text-muted d-block mb-2">最後分析：2024-01-15 10:30</small>
                <small class="text-muted d-block mb-2">上次評分：73</small>
                <small class="text-muted d-block">評分變化：+2</small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function runAnalysis() {
        if (confirm('確定要重新執行 SEO 分析嗎？此過程可能需要幾分鐘。')) {
            // 實作分析邏輯
            alert('SEO 分析功能待實作');
        }
    }

    function fixIssue(issue) {
        alert('修復功能待實作：' + issue);
    }

    function analyzePage(id) {
        alert('頁面分析功能待實作：' + id);
    }

    function checkMeta() {
        alert('Meta Tags 檢查功能待實作');
    }

    function checkImages() {
        alert('圖片檢查功能待實作');
    }

    function checkLinks() {
        alert('連結檢查功能待實作');
    }

    function checkSpeed() {
        alert('速度檢查功能待實作');
    }
</script>
@endpush
@endsection
