@extends('layouts.admin')

@section('title', '文章管理')

@php
    $breadcrumbs = [
        ['title' => '文章管理', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">文章管理</h2>
        <p class="text-muted">管理系統文章內容</p>
    </div>
    <div class="col-md-6 text-md-end">
        @can('create articles')
        <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
            </svg>
            新增文章
        </a>
        @endcan
        @include('admin.partials.view-toggle', ['pageKey' => 'articles'])
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.articles.index') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text"
                       class="form-control"
                       name="search"
                       placeholder="搜尋文章標題或內容"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">全部狀態</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>草稿</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>已發布</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>排程</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>封存</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="category_id">
                    <option value="">全部分類</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-secondary">
                    <svg class="icon">
                        <use xlink:href="/assets/icons/free.svg#cil-search"></use>
                    </svg>
                    搜尋
                </button>
                <a href="{{ route('admin.articles.index') }}" class="btn btn-light">清除</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($articles->count() > 0)

        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>標題</th>
                        <th>分類</th>
                        <th>作者</th>
                        <th>狀態</th>
                        <th>瀏覽</th>
                        <th>建立時間</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($articles as $article)
                    <tr>
                        <td>{{ $article->id }}</td>
                        <td>
                            <strong>{{ $article->title }}</strong>
                            @if($article->is_featured)
                                <span class="badge bg-warning ms-2">精選</span>
                            @endif
                        </td>
                        <td>
                            @if($article->category)
                                <span class="badge bg-secondary">{{ $article->category->name }}</span>
                            @else
                                <span class="text-muted">未分類</span>
                            @endif
                        </td>
                        <td>{{ $article->author->name ?? 'Unknown' }}</td>
                        <td>
                            <span class="badge bg-{{ $article->status_color }}">
                                {{ $article->status_label }}
                            </span>
                        </td>
                        <td>
                            <svg class="icon text-muted">
                                <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                            </svg>
                            {{ number_format($article->views_count) }}
                        </td>
                        <td>{{ $article->created_at->format('Y-m-d') }}</td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                @can('view articles')
                                <a href="{{ route('admin.articles.show', $article) }}"
                                   class="btn btn-sm btn-light"
                                   data-coreui-toggle="tooltip"
                                   title="查看">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                                    </svg>
                                </a>
                                @endcan

                                @can('edit articles')
                                <a href="{{ route('admin.articles.edit', $article) }}"
                                   class="btn btn-sm btn-light"
                                   data-coreui-toggle="tooltip"
                                   title="編輯">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                                    </svg>
                                </a>
                                @endcan

                                @can('delete articles')
                                <form method="POST"
                                      action="{{ route('admin.articles.destroy', $article) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('確定要刪除此文章嗎？');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-light text-danger"
                                            data-coreui-toggle="tooltip"
                                            title="刪除">
                                        <svg class="icon">
                                            <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                                        </svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>{{-- /.admin-list --}}

        <div class="admin-grid">
            @foreach($articles as $article)
            <div class="admin-grid-card{{ $article->is_featured ? ' featured' : '' }}">
                <div class="admin-grid-card-header">
                    @if($article->category)
                        <span class="badge bg-secondary">{{ $article->category->name }}</span>
                    @else
                        <span class="badge bg-light text-dark">未分類</span>
                    @endif
                    <span class="badge bg-{{ $article->status_color }}">{{ $article->status_label }}</span>
                </div>
                <div class="admin-grid-card-body">
                    <h6>
                        {{ $article->title }}
                        @if($article->is_featured)
                            <span class="badge bg-warning ms-1">精選</span>
                        @endif
                    </h6>
                    <div class="admin-grid-card-subtitle">{{ $article->author->name ?? 'Unknown' }}</div>
                    <dl class="admin-grid-card-meta">
                        <dt>瀏覽</dt>
                        <dd>{{ number_format($article->views_count) }}</dd>
                        <dt>日期</dt>
                        <dd>{{ $article->created_at->format('Y-m-d') }}</dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    @can('view articles')
                    <a href="{{ route('admin.articles.show', $article) }}"
                       class="btn btn-sm btn-light"
                       data-coreui-toggle="tooltip"
                       title="查看">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                        </svg>
                    </a>
                    @endcan
                    @can('edit articles')
                    <a href="{{ route('admin.articles.edit', $article) }}"
                       class="btn btn-sm btn-light"
                       data-coreui-toggle="tooltip"
                       title="編輯">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                        </svg>
                    </a>
                    @endcan
                    @can('delete articles')
                    <form method="POST"
                          action="{{ route('admin.articles.destroy', $article) }}"
                          class="d-inline"
                          onsubmit="return confirm('確定要刪除此文章嗎？');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-sm btn-light text-danger"
                                data-coreui-toggle="tooltip"
                                title="刪除">
                            <svg class="icon">
                                <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                            </svg>
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
            @endforeach
        </div>{{-- /.admin-grid --}}

        @else
        <div class="empty-state">
            <div class="empty-state-icon">📝</div>
            <div>尚無文章資料</div>
            @can('create articles')
            <a href="{{ route('admin.articles.create') }}" class="btn btn-primary mt-3">新增第一篇文章</a>
            @endcan
        </div>
        @endif
    </div>

    @if($articles->hasPages())
    <div class="card-footer">
        {{ $articles->links() }}
    </div>
    @endif
</div>
@endsection
