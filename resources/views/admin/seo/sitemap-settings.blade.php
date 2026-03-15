@extends('layouts.admin')

@section('title', 'Sitemap 設定')

@php
    $breadcrumbs = [
        ['title' => 'SEO 管理', 'url' => route('admin.seo.index')],
        ['title' => 'Sitemap 設定', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">Sitemap 設定</h2>
        <p class="text-muted">配置 XML Sitemap 自動生成規則</p>
    </div>
    <div class="col-md-6 text-md-end">
        <button type="button" class="btn btn-success" onclick="generateNow()">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-reload"></use>
            </svg>
            立即生成 Sitemap
        </button>
        <a href="{{ url('sitemap.xml') }}" target="_blank" class="btn btn-light">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-external-link"></use>
            </svg>
            查看 Sitemap
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.seo.sitemap-settings.update') }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong>包含內容</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="include_articles" name="include_articles" checked>
                            <label class="form-check-label" for="include_articles">
                                <strong>文章</strong>
                            </label>
                        </div>
                        <div class="ms-4 mt-2">
                            <label class="small text-muted">文章狀態</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="articles_published" name="articles_status[]" value="published" checked>
                                <label class="form-check-label small" for="articles_published">已發布</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="articles_scheduled" name="articles_status[]" value="scheduled">
                                <label class="form-check-label small" for="articles_scheduled">排程發布</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="include_categories" name="include_categories" checked>
                            <label class="form-check-label" for="include_categories">
                                <strong>分類頁面</strong>
                            </label>
                        </div>
                        <div class="ms-4 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="categories_active" name="categories_status[]" value="active" checked>
                                <label class="form-check-label small" for="categories_active">僅啟用的分類</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="include_tags" name="include_tags" checked>
                            <label class="form-check-label" for="include_tags">
                                <strong>標籤頁面</strong>
                            </label>
                        </div>
                        <div class="ms-4 mt-2">
                            <label class="small text-muted">最小使用次數</label>
                            <input type="number" class="form-control form-control-sm" name="tags_min_count" value="1" min="1" style="max-width: 100px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="include_pages" name="include_pages" checked>
                            <label class="form-check-label" for="include_pages">
                                <strong>靜態頁面</strong>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>優先級設定</strong>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">首頁</label>
                        <div class="col-sm-9">
                            <input type="range" class="form-range" name="priority_home" min="0" max="1" step="0.1" value="1.0" oninput="this.nextElementSibling.value = this.value">
                            <output>1.0</output>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">文章頁</label>
                        <div class="col-sm-9">
                            <input type="range" class="form-range" name="priority_articles" min="0" max="1" step="0.1" value="0.8" oninput="this.nextElementSibling.value = this.value">
                            <output>0.8</output>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">分類頁</label>
                        <div class="col-sm-9">
                            <input type="range" class="form-range" name="priority_categories" min="0" max="1" step="0.1" value="0.6" oninput="this.nextElementSibling.value = this.value">
                            <output>0.6</output>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">標籤頁</label>
                        <div class="col-sm-9">
                            <input type="range" class="form-range" name="priority_tags" min="0" max="1" step="0.1" value="0.5" oninput="this.nextElementSibling.value = this.value">
                            <output>0.5</output>
                        </div>
                    </div>

                    <div class="row">
                        <label class="col-sm-3 col-form-label">其他頁面</label>
                        <div class="col-sm-9">
                            <input type="range" class="form-range" name="priority_pages" min="0" max="1" step="0.1" value="0.4" oninput="this.nextElementSibling.value = this.value">
                            <output>0.4</output>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>更新頻率</strong>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">首頁</label>
                        <div class="col-sm-9">
                            <select class="form-select" name="changefreq_home">
                                <option value="always">隨時</option>
                                <option value="hourly">每小時</option>
                                <option value="daily" selected>每天</option>
                                <option value="weekly">每週</option>
                                <option value="monthly">每月</option>
                                <option value="yearly">每年</option>
                                <option value="never">從不</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">文章頁</label>
                        <div class="col-sm-9">
                            <select class="form-select" name="changefreq_articles">
                                <option value="always">隨時</option>
                                <option value="hourly">每小時</option>
                                <option value="daily">每天</option>
                                <option value="weekly" selected>每週</option>
                                <option value="monthly">每月</option>
                                <option value="yearly">每年</option>
                                <option value="never">從不</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <label class="col-sm-3 col-form-label">分類/標籤頁</label>
                        <div class="col-sm-9">
                            <select class="form-select" name="changefreq_taxonomies">
                                <option value="always">隨時</option>
                                <option value="hourly">每小時</option>
                                <option value="daily">每天</option>
                                <option value="weekly" selected>每週</option>
                                <option value="monthly">每月</option>
                                <option value="yearly">每年</option>
                                <option value="never">從不</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong>自動生成</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto_generate" name="auto_generate" checked>
                            <label class="form-check-label" for="auto_generate">
                                啟用自動生成
                            </label>
                        </div>
                        <small class="text-muted">內容更新時自動重新生成 Sitemap</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">排程生成</label>
                        <select class="form-select" name="schedule">
                            <option value="hourly">每小時</option>
                            <option value="daily" selected>每天</option>
                            <option value="weekly">每週</option>
                            <option value="manual">手動</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">最大 URL 數量</label>
                        <input type="number" class="form-control" name="max_urls" value="50000" min="1" max="50000">
                        <small class="text-muted">單個 Sitemap 文件的最大 URL 數</small>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>Sitemap 索引</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="use_index" name="use_index">
                            <label class="form-check-label" for="use_index">
                                使用 Sitemap 索引
                            </label>
                        </div>
                        <small class="text-muted">為大型網站建立 Sitemap 索引文件</small>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">分割方式</label>
                        <select class="form-select" name="split_by">
                            <option value="type" selected>依內容類型</option>
                            <option value="count">依數量</option>
                            <option value="date">依日期</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>提交設定</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto_submit_google" name="auto_submit_google">
                            <label class="form-check-label" for="auto_submit_google">
                                自動提交到 Google
                            </label>
                        </div>
                    </div>

                    <div class="mb-0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto_submit_bing" name="auto_submit_bing">
                            <label class="form-check-label" for="auto_submit_bing">
                                自動提交到 Bing
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="/assets/icons/free.svg#cil-save"></use>
                            </svg>
                            儲存設定
                        </button>
                        <a href="{{ route('admin.seo.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    function generateNow() {
        if (confirm('確定要立即生成 Sitemap 嗎？')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.seo.generate-sitemap") }}';
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush
@endsection
