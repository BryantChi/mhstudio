@extends('layouts.admin')

@section('title', 'SEO 管理')

@php
    $breadcrumbs = [
        ['title' => 'SEO 管理', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">SEO 管理</h2>
        <p class="text-muted">搜尋引擎優化工具與設定</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded">
                        <svg class="icon icon-xl text-primary">
                            <use xlink:href="/assets/icons/free.svg#cil-tags"></use>
                        </svg>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fs-6 fw-semibold">{{ $stats['total_meta'] ?? 0 }}</div>
                        <div class="text-muted small">Meta Tags</div>
                    </div>
                </div>
            </div>
            <div class="card-footer border-top-0 bg-transparent">
                <a href="{{ route('admin.seo.meta') }}" class="text-decoration-none small">
                    管理 Meta Tags →
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-success bg-opacity-10 p-3 rounded">
                        <svg class="icon icon-xl text-success">
                            <use xlink:href="/assets/icons/free.svg#cil-sitemap"></use>
                        </svg>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fs-6 fw-semibold">{{ $stats['sitemap_urls'] ?? 0 }}</div>
                        <div class="text-muted small">Sitemap URLs</div>
                    </div>
                </div>
            </div>
            <div class="card-footer border-top-0 bg-transparent">
                <a href="{{ route('admin.seo.sitemap-settings') }}" class="text-decoration-none small">
                    Sitemap 設定 →
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-info bg-opacity-10 p-3 rounded">
                        <svg class="icon icon-xl text-info">
                            <use xlink:href="/assets/icons/free.svg#cil-settings"></use>
                        </svg>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fs-6 fw-semibold">{{ $stats['robots_rules'] ?? 0 }}</div>
                        <div class="text-muted small">Robots Rules</div>
                    </div>
                </div>
            </div>
            <div class="card-footer border-top-0 bg-transparent">
                <a href="{{ route('admin.seo.robots-txt') }}" class="text-decoration-none small">
                    編輯 Robots.txt →
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-warning bg-opacity-10 p-3 rounded">
                        <svg class="icon icon-xl text-warning">
                            <use xlink:href="/assets/icons/free.svg#cil-chart-line"></use>
                        </svg>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fs-6 fw-semibold">{{ $stats['seo_score'] ?? 0 }}%</div>
                        <div class="text-muted small">SEO 評分</div>
                    </div>
                </div>
            </div>
            <div class="card-footer border-top-0 bg-transparent">
                <a href="{{ route('admin.seo.analyze') }}" class="text-decoration-none small">
                    SEO 分析 →
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <strong>快速操作</strong>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.seo.meta') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <svg class="icon me-2 text-primary">
                                <use xlink:href="/assets/icons/free.svg#cil-tags"></use>
                            </svg>
                            <strong>Meta Tags 管理</strong>
                            <p class="mb-0 small text-muted">管理頁面 Meta 標籤、Open Graph 和 Twitter Cards</p>
                        </div>
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-chevron-right"></use>
                        </svg>
                    </a>

                    <a href="{{ route('admin.seo.sitemap-settings') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <svg class="icon me-2 text-success">
                                <use xlink:href="/assets/icons/free.svg#cil-sitemap"></use>
                            </svg>
                            <strong>Sitemap 設定</strong>
                            <p class="mb-0 small text-muted">配置 XML Sitemap 自動生成規則</p>
                        </div>
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-chevron-right"></use>
                        </svg>
                    </a>

                    <a href="{{ route('admin.seo.robots-txt') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <svg class="icon me-2 text-info">
                                <use xlink:href="/assets/icons/free.svg#cil-settings"></use>
                            </svg>
                            <strong>Robots.txt 編輯</strong>
                            <p class="mb-0 small text-muted">編輯搜尋引擎爬蟲規則</p>
                        </div>
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-chevron-right"></use>
                        </svg>
                    </a>

                    <a href="{{ route('admin.seo.analyze') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <svg class="icon me-2 text-warning">
                                <use xlink:href="/assets/icons/free.svg#cil-chart-line"></use>
                            </svg>
                            <strong>SEO 分析</strong>
                            <p class="mb-0 small text-muted">檢查網站 SEO 狀況並取得優化建議</p>
                        </div>
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-chevron-right"></use>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <strong>最近的 SEO 活動</strong>
            </div>
            <div class="card-body p-0">
                @if(isset($recent_activities) && count($recent_activities) > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>時間</th>
                                <th>活動</th>
                                <th>頁面</th>
                                <th>操作者</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_activities as $activity)
                            <tr>
                                <td>{{ $activity['time'] ?? '-' }}</td>
                                <td>{{ $activity['action'] ?? '-' }}</td>
                                <td>{{ $activity['page'] ?? '-' }}</td>
                                <td>{{ $activity['user'] ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-4 text-center text-muted">
                    暫無 SEO 活動記錄
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <strong>SEO 工具</strong>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary" onclick="generateSitemap()">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-reload"></use>
                        </svg>
                        重新生成 Sitemap
                    </button>

                    <button type="button" class="btn btn-outline-success" onclick="submitSitemap()">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-paper-plane"></use>
                        </svg>
                        提交到搜尋引擎
                    </button>

                    <button type="button" class="btn btn-outline-info" onclick="checkIndexing()">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-search"></use>
                        </svg>
                        檢查索引狀態
                    </button>

                    <button type="button" class="btn btn-outline-warning" onclick="analyzeSeo()">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-chart-line"></use>
                        </svg>
                        執行 SEO 分析
                    </button>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <strong>SEO 健康度</strong>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Meta Tags 覆蓋率</span>
                        <span class="small fw-semibold">{{ $health['meta_coverage'] ?? 0 }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $health['meta_coverage'] ?? 0 }}%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">圖片 Alt 標籤</span>
                        <span class="small fw-semibold">{{ $health['image_alt'] ?? 0 }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $health['image_alt'] ?? 0 }}%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">內部連結品質</span>
                        <span class="small fw-semibold">{{ $health['internal_links'] ?? 0 }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $health['internal_links'] ?? 0 }}%"></div>
                    </div>
                </div>

                <div class="mb-0">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">頁面載入速度</span>
                        <span class="small fw-semibold">{{ $health['page_speed'] ?? 0 }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $health['page_speed'] ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <strong>SEO 提示</strong>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-2">
                    <small>💡 定期更新 Sitemap 可以幫助搜尋引擎更快發現新內容</small>
                </div>
                <div class="alert alert-warning mb-2">
                    <small>⚠️ 確保所有頁面都有獨特的 Meta 描述</small>
                </div>
                <div class="alert alert-success mb-0">
                    <small>✓ 使用語義化的 HTML 標籤提升 SEO 效果</small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function generateSitemap() {
        if (confirm('確定要重新生成 Sitemap 嗎？')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.seo.generate-sitemap") }}';
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function submitSitemap() {
        var sitemapUrl = '{{ url("sitemap.xml") }}';
        var host = '{{ parse_url(config("app.url"), PHP_URL_HOST) }}';

        // 複製 Sitemap URL 到剪貼簿
        navigator.clipboard.writeText(sitemapUrl).catch(function() {});

        var result = confirm(
            '📋 Sitemap URL 已複製到剪貼簿：\n' + sitemapUrl + '\n\n' +
            '請選擇提交方式：\n' +
            '• 按「確定」→ 開啟 Google Search Console\n' +
            '• 按「取消」→ 開啟 Bing Webmaster Tools\n\n' +
            '💡 提示：也可在 robots.txt 中加入\n' +
            'Sitemap: ' + sitemapUrl
        );

        if (result) {
            window.open('https://search.google.com/search-console/sitemaps?resource_id=' + encodeURIComponent('https://' + host + '/'), '_blank');
        } else {
            window.open('https://www.bing.com/webmasters/sitemaps?siteUrl=' + encodeURIComponent('https://' + host), '_blank');
        }
    }

    function checkIndexing() {
        // 在 Google 搜尋中檢查索引狀態
        window.open('https://www.google.com/search?q=site:{{ parse_url(config("app.url"), PHP_URL_HOST) }}', '_blank');
    }

    function analyzeSeo() {
        window.location.href = '{{ route("admin.seo.analyze") }}';
    }
</script>
@endpush

@push('styles')
<style>
    .icon-xl {
        width: 2.5rem;
        height: 2.5rem;
    }
</style>
@endpush
@endsection
