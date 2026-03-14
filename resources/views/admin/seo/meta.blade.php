@extends('layouts.admin')

@section('title', 'Meta Tags 管理')

@php
    $breadcrumbs = [
        ['title' => 'SEO 管理', 'url' => route('admin.seo.index')],
        ['title' => 'Meta Tags', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">Meta Tags 管理</h2>
        <p class="text-muted">管理頁面 Meta 標籤</p>
    </div>
    <div class="col-md-6 text-md-end">
        <button type="button" class="btn btn-success" onclick="generateMissingMeta()">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
            </svg>
            批次生成缺少的 Meta
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.seo.meta') }}" class="row g-3">
            <div class="col-md-4">
                <select class="form-select" name="type">
                    <option value="">全部類型</option>
                    <option value="article" {{ request('type') == 'article' ? 'selected' : '' }}>文章</option>
                    <option value="category" {{ request('type') == 'category' ? 'selected' : '' }}>分類</option>
                    <option value="page" {{ request('type') == 'page' ? 'selected' : '' }}>頁面</option>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-select" name="status">
                    <option value="">全部狀態</option>
                    <option value="complete" {{ request('status') == 'complete' ? 'selected' : '' }}>完整</option>
                    <option value="incomplete" {{ request('status') == 'incomplete' ? 'selected' : '' }}>不完整</option>
                    <option value="missing" {{ request('status') == 'missing' ? 'selected' : '' }}>缺少</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-secondary">
                    <svg class="icon">
                        <use xlink:href="/assets/icons/free.svg#cil-search"></use>
                    </svg>
                    篩選
                </button>
                <a href="{{ route('admin.seo.meta') }}" class="btn btn-light">清除</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if(isset($metas) && count($metas) > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>頁面</th>
                        <th>類型</th>
                        <th>Meta 標題</th>
                        <th>Meta 描述</th>
                        <th>完整度</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($metas as $meta)
                    <tr>
                        <td>
                            <strong>{{ $meta['title'] ?? 'Unknown' }}</strong>
                            <br>
                            <small class="text-muted">{{ $meta['url'] ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $meta['type'] ?? '-' }}</span>
                        </td>
                        <td>
                            @if(!empty($meta['meta_title']))
                                <span class="text-success">✓</span>
                                {{ Str::limit($meta['meta_title'], 30) }}
                            @else
                                <span class="text-danger">✗ 缺少</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($meta['meta_description']))
                                <span class="text-success">✓</span>
                                {{ Str::limit($meta['meta_description'], 40) }}
                            @else
                                <span class="text-danger">✗ 缺少</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $completeness = $meta['completeness'] ?? 0;
                                $color = $completeness >= 80 ? 'success' : ($completeness >= 50 ? 'warning' : 'danger');
                            @endphp
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $completeness }}%">
                                    {{ $completeness }}%
                                </div>
                            </div>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-light" onclick="editMeta({{ $meta['id'] ?? 0 }})">
                                <svg class="icon">
                                    <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-4 text-center text-muted">
            暫無 Meta Tags 資料
        </div>
        @endif
    </div>
</div>

<!-- Meta 編輯 Modal -->
<div class="modal fade" id="metaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">編輯 Meta Tags</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="metaForm">
                    <div class="mb-3">
                        <label for="meta_title" class="form-label">Meta 標題</label>
                        <input type="text" class="form-control" id="meta_title" maxlength="60">
                        <div class="form-text">建議 50-60 個字元</div>
                    </div>

                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta 描述</label>
                        <textarea class="form-control" id="meta_description" rows="3" maxlength="160"></textarea>
                        <div class="form-text">建議 150-160 個字元</div>
                    </div>

                    <div class="mb-3">
                        <label for="meta_keywords" class="form-label">Meta 關鍵字</label>
                        <input type="text" class="form-control" id="meta_keywords">
                        <div class="form-text">多個關鍵字用逗號分隔</div>
                    </div>

                    <hr>

                    <h6>Open Graph</h6>

                    <div class="mb-3">
                        <label for="og_title" class="form-label">OG 標題</label>
                        <input type="text" class="form-control" id="og_title">
                    </div>

                    <div class="mb-3">
                        <label for="og_description" class="form-label">OG 描述</label>
                        <textarea class="form-control" id="og_description" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="og_image" class="form-label">OG 圖片 URL</label>
                        <input type="url" class="form-control" id="og_image">
                        <div class="form-text">建議尺寸 1200x630 像素</div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="index_status" checked>
                            <label class="form-check-label" for="index_status">
                                允許搜尋引擎索引
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-coreui-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="saveMeta()">儲存</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function editMeta(id) {
        // 載入 Meta 資料並顯示 Modal
        // 這裡應該透過 AJAX 載入資料
        const modal = new coreui.Modal(document.getElementById('metaModal'));
        modal.show();
    }

    function saveMeta() {
        // 儲存 Meta 資料
        const formData = {
            meta_title: document.getElementById('meta_title').value,
            meta_description: document.getElementById('meta_description').value,
            meta_keywords: document.getElementById('meta_keywords').value,
            og_title: document.getElementById('og_title').value,
            og_description: document.getElementById('og_description').value,
            og_image: document.getElementById('og_image').value,
            index_status: document.getElementById('index_status').checked
        };

        // 透過 AJAX 儲存
        alert('儲存功能待實作');
    }

    function generateMissingMeta() {
        if (confirm('確定要為所有缺少 Meta 的頁面自動生成 Meta Tags 嗎？')) {
            // 實作批次生成邏輯
            alert('批次生成功能待實作');
        }
    }
</script>
@endpush
@endsection
