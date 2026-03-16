@extends('layouts.admin')

@section('title', 'Sitemap 設定')

@php
    $breadcrumbs = [
        ['title' => 'SEO 管理', 'url' => route('admin.seo.index')],
        ['title' => 'Sitemap 設定', 'url' => '#']
    ];

    $sitemapPath = public_path('sitemap.xml');
    $sitemapExists = file_exists($sitemapPath);
    $sitemapSize = $sitemapExists ? filesize($sitemapPath) : 0;
    $sitemapDate = $sitemapExists ? date('Y-m-d H:i:s', filemtime($sitemapPath)) : null;
    $sitemapUrls = 0;
    if ($sitemapExists) {
        $sitemapUrls = substr_count(file_get_contents($sitemapPath), '<url>');
    }
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">Sitemap 設定</h2>
        <p class="text-muted">管理 XML Sitemap 生成與提交</p>
    </div>
    <div class="col-md-6 text-md-end">
        <form method="POST" action="{{ route('admin.seo.generate-sitemap') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success" onclick="return confirm('確定要重新生成 Sitemap 嗎？')">
                <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-reload"></use></svg>
                立即生成 Sitemap
            </button>
        </form>
        @if($sitemapExists)
        <a href="{{ url('sitemap.xml') }}" target="_blank" class="btn btn-light">
            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-external-link"></use></svg>
            查看 Sitemap
        </a>
        @endif
    </div>
</div>

{{-- Sitemap 狀態 --}}
<div class="card mb-4">
    <div class="card-header">
        <strong>
            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-sitemap"></use></svg>
            Sitemap 狀態
        </strong>
    </div>
    <div class="card-body">
        @if($sitemapExists)
        <div class="row">
            <div class="col-md-3 col-6 mb-3">
                <div class="text-muted small">狀態</div>
                <div><span class="badge bg-success">已生成</span></div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="text-muted small">URL 數量</div>
                <div class="fw-semibold">{{ $sitemapUrls }}</div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="text-muted small">檔案大小</div>
                <div class="fw-semibold">{{ number_format($sitemapSize / 1024, 1) }} KB</div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="text-muted small">最後生成</div>
                <div class="fw-semibold">{{ $sitemapDate }}</div>
            </div>
        </div>
        <div class="mt-2">
            <div class="text-muted small mb-1">Sitemap URL</div>
            <div class="input-group">
                <input type="text" class="form-control form-control-sm" value="{{ url('sitemap.xml') }}" readonly id="sitemapUrl">
                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('sitemapUrl').value); this.textContent='已複製'; setTimeout(() => this.textContent='複製', 1500);">
                    複製
                </button>
            </div>
        </div>
        @else
        <div class="text-center py-3">
            <div class="text-muted mb-2">尚未生成 Sitemap</div>
            <form method="POST" action="{{ route('admin.seo.generate-sitemap') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">立即生成</button>
            </form>
        </div>
        @endif
    </div>
</div>

{{-- Sitemap 設定 --}}
<form method="POST" action="{{ route('admin.seo.sitemap-settings.update') }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><strong>包含內容</strong></div>
                <div class="card-body">
                    <p class="text-muted small mb-3">選擇要包含在 Sitemap 中的內容類型。</p>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="include_articles" name="include_articles" value="1" {{ setting('sitemap_include_articles', '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="include_articles"><strong>文章</strong> — 已發布的部落格文章</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="include_projects" name="include_projects" value="1" {{ setting('sitemap_include_projects', '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="include_projects"><strong>作品集</strong> — 已發布的作品</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="include_services" name="include_services" value="1" {{ setting('sitemap_include_services', '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="include_services"><strong>服務頁面</strong> — 各項服務詳情</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="include_legal" name="include_legal" value="1" {{ setting('sitemap_include_legal', '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="include_legal"><strong>法律頁面</strong> — 隱私權政策、服務條款等</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><strong>SEO 優先順序</strong></div>
                <div class="card-body">
                    <p class="text-muted small mb-3">設定各頁面類型在 Sitemap 中的 priority（0.0 ~ 1.0）。</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">首頁</label>
                            <select class="form-select form-select-sm" name="priority_home">
                                @foreach(['1.0','0.9','0.8','0.7','0.6','0.5'] as $p)
                                <option value="{{ $p }}" {{ setting('sitemap_priority_home', '1.0') == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">文章</label>
                            <select class="form-select form-select-sm" name="priority_articles">
                                @foreach(['1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3'] as $p)
                                <option value="{{ $p }}" {{ setting('sitemap_priority_articles', '0.7') == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">作品</label>
                            <select class="form-select form-select-sm" name="priority_projects">
                                @foreach(['1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3'] as $p)
                                <option value="{{ $p }}" {{ setting('sitemap_priority_projects', '0.7') == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">服務</label>
                            <select class="form-select form-select-sm" name="priority_services">
                                @foreach(['1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3'] as $p)
                                <option value="{{ $p }}" {{ setting('sitemap_priority_services', '0.6') == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><strong>提交到搜尋引擎</strong></div>
                <div class="card-body">
                    <p class="text-muted small mb-3">建議在 robots.txt 中加入 Sitemap URL，搜尋引擎會自動抓取。</p>

                    <a href="https://search.google.com/search-console" target="_blank" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-external-link"></use></svg>
                        Google Search Console
                    </a>
                    <a href="https://www.bing.com/webmasters" target="_blank" class="btn btn-outline-secondary btn-sm w-100 mb-3">
                        <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-external-link"></use></svg>
                        Bing Webmaster Tools
                    </a>

                    <div class="alert alert-info small mb-0">
                        <strong>💡 提示：</strong>在 robots.txt 末尾加入：<br>
                        <code>Sitemap: {{ url('sitemap.xml') }}</code>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100">
                        <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg>
                        儲存設定
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
