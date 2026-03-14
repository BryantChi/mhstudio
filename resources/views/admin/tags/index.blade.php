@extends('layouts.admin')

@section('title', '標籤管理')

@php
    $breadcrumbs = [
        ['title' => '標籤管理', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">標籤管理</h2>
        <p class="text-muted">管理文章標籤</p>
    </div>
    <div class="col-md-6 text-md-end">
        @can('create tags')
        <a href="{{ route('admin.tags.create') }}" class="btn btn-primary">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
            </svg>
            新增標籤
        </a>
        @endcan
        @include('admin.partials.view-toggle', ['pageKey' => 'tags'])
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.tags.index') }}" class="row g-3">
            <div class="col-md-6">
                <input type="text"
                       class="form-control"
                       name="search"
                       placeholder="搜尋標籤名稱或描述"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-secondary">
                    <svg class="icon">
                        <use xlink:href="/assets/icons/free.svg#cil-search"></use>
                    </svg>
                    搜尋
                </button>
                <a href="{{ route('admin.tags.index') }}" class="btn btn-light">清除</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($tags->count() > 0)
        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>標籤名稱</th>
                        <th>顏色</th>
                        <th>使用次數</th>
                        <th>建立時間</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tags as $tag)
                    <tr>
                        <td>{{ $tag->id }}</td>
                        <td>
                            <span class="badge" style="background-color: {{ $tag->color }}">
                                {{ $tag->name }}
                            </span>
                        </td>
                        <td>
                            <span class="color-preview" style="background-color: {{ $tag->color }}"></span>
                            <code class="ms-2">{{ $tag->color }}</code>
                        </td>
                        <td>
                            <svg class="icon text-muted">
                                <use xlink:href="/assets/icons/free.svg#cil-tag"></use>
                            </svg>
                            {{ $tag->count }}
                        </td>
                        <td>{{ $tag->created_at->format('Y-m-d') }}</td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                @can('edit tags')
                                <a href="{{ route('admin.tags.edit', $tag) }}"
                                   class="btn btn-sm btn-light"
                                   data-coreui-toggle="tooltip"
                                   title="編輯">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                                    </svg>
                                </a>
                                @endcan

                                @can('delete tags')
                                <form method="POST"
                                      action="{{ route('admin.tags.destroy', $tag) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('確定要刪除此標籤嗎？\n\n注意：已關聯的文章將移除此標籤。');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-light text-danger"
                                            data-coreui-toggle="tooltip"
                                            title="刪除"
                                            {{ $tag->count > 0 ? 'disabled' : '' }}>
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
            @foreach($tags as $tag)
            <div class="admin-grid-card">
                <div class="admin-grid-card-header">
                    <span class="badge" style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                </div>
                <div class="admin-grid-card-body">
                    <h6>{{ $tag->name }}</h6>
                    @if($tag->description)
                        <div class="admin-grid-card-subtitle">{{ $tag->description }}</div>
                    @endif
                    <div class="mb-2">
                        <span class="color-preview" style="background-color: {{ $tag->color }}"></span>
                        <code class="ms-2">{{ $tag->color }}</code>
                    </div>
                    <dl class="admin-grid-card-meta">
                        <dt>使用次數</dt>
                        <dd>{{ $tag->count }}</dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    @can('edit tags')
                    <a href="{{ route('admin.tags.edit', $tag) }}"
                       class="btn btn-sm btn-light"
                       data-coreui-toggle="tooltip"
                       title="編輯">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                        </svg>
                    </a>
                    @endcan

                    @can('delete tags')
                    <form method="POST"
                          action="{{ route('admin.tags.destroy', $tag) }}"
                          class="d-inline"
                          onsubmit="return confirm('確定要刪除此標籤嗎？\n\n注意：已關聯的文章將移除此標籤。');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-sm btn-light text-danger"
                                data-coreui-toggle="tooltip"
                                title="刪除"
                                {{ $tag->count > 0 ? 'disabled' : '' }}>
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
            <div class="empty-state-icon">🏷️</div>
            <div>尚無標籤資料</div>
            @can('create tags')
            <a href="{{ route('admin.tags.create') }}" class="btn btn-primary mt-3">新增第一個標籤</a>
            @endcan
        </div>
        @endif
    </div>

    @if($tags->hasPages())
    <div class="card-footer">
        {{ $tags->links() }}
    </div>
    @endif
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <strong>標籤雲</strong>
            </div>
            <div class="card-body">
                @if($tags->count() > 0)
                <div class="tag-cloud">
                    @foreach($tags as $tag)
                        <span class="tag-item badge me-2 mb-2"
                              style="background-color: {{ $tag->color }}; font-size: {{ 0.875 + ($tag->count * 0.125) }}rem;">
                            {{ $tag->name }}
                            <span class="badge bg-light text-dark ms-1">{{ $tag->count }}</span>
                        </span>
                    @endforeach
                </div>
                @else
                <p class="text-muted mb-0">尚無標籤資料</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .color-preview {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 3px;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }
    .tag-cloud {
        line-height: 2.5;
    }
    .tag-item {
        cursor: default;
        transition: transform 0.2s;
    }
    .tag-item:hover {
        transform: scale(1.1);
    }
</style>
@endpush
@endsection
