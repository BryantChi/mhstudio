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
    <div class="col-lg-4 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <div class="display-4 fw-bold text-primary">{{ count($issues) }}</div>
                <div class="text-muted">偵測到的問題</div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                @php
                    $warningCount = collect($issues)->where('severity', 'warning')->count();
                @endphp
                <div class="display-4 fw-bold text-warning">{{ $warningCount }}</div>
                <div class="text-muted">警告</div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                @php
                    $infoCount = collect($issues)->where('severity', 'info')->count();
                @endphp
                <div class="display-4 fw-bold text-info">{{ $infoCount }}</div>
                <div class="text-muted">優化建議</div>
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
                @if(count($issues) > 0)
                    @foreach($issues as $issue)
                    <div class="d-flex align-items-start mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="flex-shrink-0 me-3">
                            @if($issue['severity'] === 'warning')
                                <svg class="icon icon-lg text-warning"><use xlink:href="/assets/icons/free.svg#cil-warning"></use></svg>
                            @elseif($issue['severity'] === 'danger')
                                <svg class="icon icon-lg text-danger"><use xlink:href="/assets/icons/free.svg#cil-x-circle"></use></svg>
                            @else
                                <svg class="icon icon-lg text-info"><use xlink:href="/assets/icons/free.svg#cil-info"></use></svg>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <strong class="text-{{ $issue['severity'] }}">{{ $issue['title'] }}</strong>
                            <p class="mb-1 small text-muted">{{ $issue['description'] }}</p>
                            @if(!empty($issue['action']))
                                <form method="POST" action="{{ $issue['action'] }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $issue['severity'] }}">自動修復</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center text-muted py-4">
                        <svg class="icon icon-xl text-success mb-2"><use xlink:href="/assets/icons/free.svg#cil-check-circle"></use></svg>
                        <p>太棒了！目前沒有偵測到 SEO 問題。</p>
                    </div>
                @endif
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
                    <a href="{{ route('admin.seo.meta') }}" class="btn btn-outline-primary">
                        <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-tags"></use></svg>
                        管理 Meta Tags
                    </a>
                    <a href="https://pagespeed.web.dev/analysis?url={{ urlencode(config('app.url')) }}" target="_blank" class="btn btn-outline-warning">
                        <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-speedometer"></use></svg>
                        PageSpeed Insights
                    </a>
                    <a href="https://search.google.com/test/rich-results?url={{ urlencode(config('app.url')) }}" target="_blank" class="btn btn-outline-info">
                        <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-code"></use></svg>
                        結構化資料測試
                    </a>
                    <a href="https://search.google.com/test/mobile-friendly?url={{ urlencode(config('app.url')) }}" target="_blank" class="btn btn-outline-success">
                        <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-mobile"></use></svg>
                        行動裝置友善度
                    </a>
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
                <strong>外部 SEO 工具</strong>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="https://search.google.com/search-console" target="_blank" class="btn btn-sm btn-outline-secondary">Google Search Console</a>
                    <a href="https://www.bing.com/webmasters" target="_blank" class="btn btn-sm btn-outline-secondary">Bing Webmaster Tools</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function runAnalysis() {
        window.location.reload();
    }
</script>
@endpush
@endsection
