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
        <form method="POST" action="{{ route('admin.seo.generate-missing') }}" class="d-inline" onsubmit="return confirm('為所有缺少 Meta 的內容（文章、作品、服務）自動生成 SEO Meta？')">
            @csrf
            <button type="submit" class="btn btn-success">
                <svg class="icon me-2">
                    <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
                </svg>
                生成缺少的
            </button>
        </form>
        <form method="POST" action="{{ route('admin.seo.regenerate-all') }}" class="d-inline" onsubmit="return confirm('⚠️ 確定要重新生成全部 SEO Meta 嗎？\n\n這會覆蓋所有現有的 Meta Title、Description、OG 標籤等。\n手動編輯過的內容將被覆蓋。')">
            @csrf
            <button type="submit" class="btn btn-warning">
                <svg class="icon me-2">
                    <use xlink:href="/assets/icons/free.svg#cil-reload"></use>
                </svg>
                重新生成全部
            </button>
        </form>
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
        @if($seoMetas->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>關聯內容</th>
                        <th>類型</th>
                        <th>Meta 標題</th>
                        <th>Meta 描述</th>
                        <th>完整度</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($seoMetas as $meta)
                    @php
                        $modelName = $meta->model ? ($meta->model->title ?? $meta->model->name ?? 'Unknown') : '(已刪除)';
                        $modelType = class_basename($meta->model_type);
                        $filledFields = collect([
                            $meta->meta_title, $meta->meta_description, $meta->meta_keywords,
                            $meta->og_title, $meta->og_description, $meta->og_image,
                            $meta->twitter_title, $meta->twitter_description,
                        ])->filter()->count();
                        $totalFields = 8;
                        $completeness = round($filledFields / $totalFields * 100);
                        $color = $completeness >= 80 ? 'success' : ($completeness >= 50 ? 'warning' : 'danger');
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $modelName }}</strong>
                            <br>
                            <small class="text-muted">ID: {{ $meta->model_id }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $modelType }}</span>
                        </td>
                        <td>
                            @if($meta->meta_title)
                                <span class="text-success">✓</span>
                                {{ Str::limit($meta->meta_title, 30) }}
                            @else
                                <span class="text-danger">✗ 缺少</span>
                            @endif
                        </td>
                        <td>
                            @if($meta->meta_description)
                                <span class="text-success">✓</span>
                                {{ Str::limit($meta->meta_description, 40) }}
                            @else
                                <span class="text-danger">✗ 缺少</span>
                            @endif
                        </td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $completeness }}%">
                                    {{ $completeness }}%
                                </div>
                            </div>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.seo.meta.edit', $meta) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="編輯">
                                <svg class="icon">
                                    <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $seoMetas->withQueryString()->links() }}
        </div>
        @else
        <div class="p-4 text-center text-muted">
            暫無 Meta Tags 資料
        </div>
        @endif
    </div>
</div>


@endsection
