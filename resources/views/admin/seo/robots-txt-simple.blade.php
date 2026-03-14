@extends('layouts.admin')

@section('title', 'Robots.txt 編輯')

@php
    $breadcrumbs = [
        ['title' => 'SEO 管理', 'url' => route('admin.seo.index')],
        ['title' => 'Robots.txt', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">Robots.txt 編輯</h2>
        <p class="text-muted">編輯搜尋引擎爬蟲規則</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ url('robots.txt') }}" target="_blank" class="btn btn-light">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-external-link"></use>
            </svg>
            查看當前 Robots.txt
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.seo.robots-txt.update') }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <strong>Robots.txt 內容</strong>
                </div>
                <div class="card-body">
                    <textarea class="form-control font-monospace @error('content') is-invalid @enderror"
                              name="content"
                              rows="20"
                              required>{{ old('content', $content) }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted mt-2 d-block">
                        直接編輯 robots.txt 內容。常見範例：<br>
                        <code>User-agent: *</code><br>
                        <code>Disallow: /admin</code><br>
                        <code>Sitemap: {{ url('sitemap.xml') }}</code>
                    </small>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-save"></use>
                        </svg>
                        儲存
                    </button>
                    <a href="{{ route('admin.seo.index') }}" class="btn btn-light">取消</a>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <strong>快速範本</strong>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadPreset('allow_all')">
                            允許所有
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadPreset('block_admin')">
                            封鎖後台
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadPreset('standard')">
                            標準設定
                        </button>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>說明</strong>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <strong>User-agent</strong>: 指定爬蟲<br>
                        <strong>Disallow</strong>: 禁止訪問的路徑<br>
                        <strong>Allow</strong>: 允許訪問的路徑<br>
                        <strong>Sitemap</strong>: Sitemap 位置<br>
                        <strong>Crawl-delay</strong>: 爬蟲延遲（秒）
                    </small>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    function loadPreset(preset) {
        const presets = {
            'allow_all': `User-agent: *\nAllow: /\n\nSitemap: {{ url('sitemap.xml') }}`,
            'block_admin': `User-agent: *\nDisallow: /admin\nDisallow: /api\n\nSitemap: {{ url('sitemap.xml') }}`,
            'standard': `User-agent: *\nDisallow: /admin\nDisallow: /api\nDisallow: /login\n\nSitemap: {{ url('sitemap.xml') }}`
        };

        if (presets[preset]) {
            document.querySelector('textarea[name="content"]').value = presets[preset];
        }
    }
</script>
@endpush
@endsection
