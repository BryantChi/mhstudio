@extends('layouts.admin')

@section('title', '分類詳情')

@php
    $breadcrumbs = [
        ['title' => '分類管理', 'url' => route('admin.categories.index')],
        ['title' => '分類詳情', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">分類詳情</h2>
        <p class="text-muted">查看分類資訊</p>
    </div>
    <div class="col-md-6 text-md-end">
        @can('edit categories')
        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
            </svg>
            編輯
        </a>
        @endcan

        <a href="{{ route('admin.categories.index') }}" class="btn btn-light">返回列表</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>分類資訊</strong>
                <div>
                    @if($category->status === 'active')
                        <span class="badge bg-success">啟用</span>
                    @else
                        <span class="badge bg-secondary">停用</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($category->image)
                <div class="mb-4">
                    <img src="{{ $category->image }}"
                         class="img-fluid rounded"
                         alt="{{ $category->name }}"
                         style="max-height: 300px;">
                </div>
                @endif

                <div class="mb-4">
                    @if($category->icon)
                        <i class="{{ $category->icon }} me-2" style="color: {{ $category->color ?? '#6c757d' }}; font-size: 2rem;"></i>
                    @endif
                    <h3 class="d-inline-block mb-0" style="color: {{ $category->color ?? '#6c757d' }}">
                        {{ $category->name }}
                    </h3>
                </div>

                @if($category->description)
                <div class="alert alert-light border mb-3">
                    <strong>描述：</strong>{{ $category->description }}
                </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">網址別名 (Slug)</label>
                        <div class="p-2 bg-light rounded">
                            <code>{{ $category->slug }}</code>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">分類網址</label>
                        <div class="p-2 bg-light rounded">
                            <a href="{{ url('categories/' . $category->slug) }}" target="_blank">
                                {{ url('categories/' . $category->slug) }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($category->children && $category->children->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <strong>子分類（{{ $category->children->count() }}）</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>名稱</th>
                                <th>文章數</th>
                                <th>狀態</th>
                                <th>排序</th>
                                <th class="text-end">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($category->children as $child)
                            <tr>
                                <td>
                                    @if($child->icon)
                                        <i class="{{ $child->icon }} me-2" style="color: {{ $child->color ?? '#6c757d' }}"></i>
                                    @endif
                                    {{ $child->name }}
                                </td>
                                <td>{{ $child->articles_count ?? 0 }}</td>
                                <td>
                                    @if($child->status === 'active')
                                        <span class="badge bg-success">啟用</span>
                                    @else
                                        <span class="badge bg-secondary">停用</span>
                                    @endif
                                </td>
                                <td>{{ $child->order }}</td>
                                <td class="text-end">
                                    @can('view categories')
                                    <a href="{{ route('admin.categories.show', $child) }}" class="btn btn-sm btn-light">
                                        <svg class="icon">
                                            <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                                        </svg>
                                    </a>
                                    @endcan
                                    @can('edit categories')
                                    <a href="{{ route('admin.categories.edit', $child) }}" class="btn btn-sm btn-light">
                                        <svg class="icon">
                                            <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                                        </svg>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if($category->articles && $category->articles->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <strong>此分類的文章（{{ $category->articles->count() }}）</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>標題</th>
                                <th>作者</th>
                                <th>狀態</th>
                                <th>瀏覽</th>
                                <th>發布時間</th>
                                <th class="text-end">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($category->articles->take(10) as $article)
                            <tr>
                                <td>{{ $article->title }}</td>
                                <td>{{ $article->author->name ?? 'Unknown' }}</td>
                                <td>
                                    <span class="badge bg-{{ $article->status_color }}">
                                        {{ $article->status_label }}
                                    </span>
                                </td>
                                <td>{{ number_format($article->views_count) }}</td>
                                <td>{{ $article->published_at ? $article->published_at->format('Y-m-d') : '-' }}</td>
                                <td class="text-end">
                                    @can('view articles')
                                    <a href="{{ route('admin.articles.show', $article) }}" class="btn btn-sm btn-light">
                                        <svg class="icon">
                                            <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                                        </svg>
                                    </a>
                                    @endcan
                                    @can('edit articles')
                                    <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-sm btn-light">
                                        <svg class="icon">
                                            <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                                        </svg>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($category->articles->count() > 10)
                <div class="card-footer">
                    <a href="{{ route('admin.articles.index', ['category_id' => $category->id]) }}" class="btn btn-sm btn-light">
                        查看全部 {{ $category->articles->count() }} 篇文章
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($category->meta)
        <div class="card mt-3">
            <div class="card-header">
                <strong>Meta 資訊</strong>
            </div>
            <div class="card-body">
                <pre class="mb-0"><code>{{ json_encode($category->meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <strong>基本資訊</strong>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <th width="100">分類 ID</th>
                            <td>{{ $category->id }}</td>
                        </tr>
                        <tr>
                            <th>父分類</th>
                            <td>
                                @if($category->parent)
                                    <a href="{{ route('admin.categories.show', $category->parent) }}">
                                        {{ $category->parent->name }}
                                    </a>
                                @else
                                    <span class="text-muted">頂層分類</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>狀態</th>
                            <td>
                                @if($category->status === 'active')
                                    <span class="badge bg-success">啟用</span>
                                @else
                                    <span class="badge bg-secondary">停用</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>排序</th>
                            <td>{{ $category->order }}</td>
                        </tr>
                        <tr>
                            <th>顏色</th>
                            <td>
                                <span class="badge" style="background-color: {{ $category->color ?? '#6c757d' }}">
                                    {{ $category->color ?? '#6c757d' }}
                                </span>
                            </td>
                        </tr>
                        @if($category->icon)
                        <tr>
                            <th>圖標</th>
                            <td>
                                <i class="{{ $category->icon }}" style="font-size: 1.5rem; color: {{ $category->color ?? '#6c757d' }}"></i>
                                <code class="ms-2">{{ $category->icon }}</code>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th>建立時間</th>
                            <td>{{ $category->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        <tr>
                            <th>最後更新</th>
                            <td>{{ $category->updated_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>統計資訊</strong>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <svg class="icon icon-xl text-primary mb-2">
                                <use xlink:href="/assets/icons/free.svg#cil-file"></use>
                            </svg>
                            <div class="fs-5 fw-semibold">{{ $category->articles_count ?? 0 }}</div>
                            <div class="small text-muted">文章數量</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <svg class="icon icon-xl text-success mb-2">
                                <use xlink:href="/assets/icons/free.svg#cil-folder"></use>
                            </svg>
                            <div class="fs-5 fw-semibold">{{ $category->children_count ?? 0 }}</div>
                            <div class="small text-muted">子分類數量</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @can('delete categories')
        <div class="card mt-3">
            <div class="card-body">
                @if(($category->children_count ?? 0) > 0)
                <div class="alert alert-warning mb-3">
                    <small>⚠️ 此分類有 {{ $category->children_count }} 個子分類，無法刪除</small>
                </div>
                @endif
                @if(($category->articles_count ?? 0) > 0)
                <div class="alert alert-warning mb-3">
                    <small>⚠️ 此分類有 {{ $category->articles_count }} 篇文章，無法刪除</small>
                </div>
                @endif
                <form method="POST"
                      action="{{ route('admin.categories.destroy', $category) }}"
                      onsubmit="return confirm('確定要刪除此分類嗎？此操作無法復原。');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="btn btn-danger w-100"
                            {{ (($category->children_count ?? 0) > 0 || ($category->articles_count ?? 0) > 0) ? 'disabled' : '' }}>
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                        </svg>
                        刪除分類
                    </button>
                </form>
            </div>
        </div>
        @endcan
    </div>
</div>

@push('styles')
<style>
    .icon-xl {
        width: 2rem;
        height: 2rem;
    }
</style>
@endpush
@endsection
