@extends('layouts.admin')

@section('title', '作品集管理')

@php
    $breadcrumbs = [
        ['title' => '作品集管理', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">作品集管理</h2>
        <p class="text-muted">管理作品集內容</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
            </svg>
            新增作品
        </a>
        @include('admin.partials.sortable-mode', [
            'reorderUrl' => route('admin.projects.reorder'),
            'fetchUrl' => route('admin.projects.index', ['_sortable' => 1]),
            'itemLabel' => '作品',
            'titleField' => 'title',
        ])
        @include('admin.partials.view-toggle', ['pageKey' => 'projects'])
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.projects.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text"
                       class="form-control"
                       name="search"
                       placeholder="搜尋作品標題或客戶"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">全部狀態</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>草稿</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>已發布</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="category">
                    <option value="">全部分類</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="visibility">
                    <option value="">全部可見性</option>
                    <option value="public" {{ request('visibility') == 'public' ? 'selected' : '' }}>公開</option>
                    <option value="showcase" {{ request('visibility') == 'showcase' ? 'selected' : '' }}>僅展示</option>
                    <option value="unlisted" {{ request('visibility') == 'unlisted' ? 'selected' : '' }}>僅限連結</option>
                    <option value="hidden" {{ request('visibility') == 'hidden' ? 'selected' : '' }}>隱藏</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-secondary">
                    <svg class="icon">
                        <use xlink:href="/assets/icons/free.svg#cil-search"></use>
                    </svg>
                    搜尋
                </button>
                <a href="{{ route('admin.projects.index') }}" class="btn btn-light">清除</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($projects->count() > 0)

        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>標題</th>
                        <th>客戶</th>
                        <th>分類</th>
                        <th>技術棧</th>
                        <th>狀態</th>
                        <th>完成日期</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                    <tr>
                        <td>{{ $project->id }}</td>
                        <td>
                            <strong>{{ $project->title }}</strong>
                            @if($project->is_featured)
                                <span class="badge bg-warning ms-2">精選</span>
                            @endif
                        </td>
                        <td>{{ $project->client ?? '-' }}</td>
                        <td>
                            @if($project->category)
                                <span class="badge bg-secondary">{{ $project->category }}</span>
                            @else
                                <span class="text-muted">未分類</span>
                            @endif
                        </td>
                        <td>
                            @if($project->tech_stack)
                                @foreach(array_slice($project->tech_stack, 0, 3) as $tech)
                                    <span class="badge bg-info me-1">{{ $tech }}</span>
                                @endforeach
                                @if(count($project->tech_stack) > 3)
                                    <span class="badge bg-light text-dark">+{{ count($project->tech_stack) - 3 }}</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $project->status_color }}">{{ $project->status_label }}</span>
                            @if($project->visibility !== 'public')
                            <span class="badge bg-{{ $project->visibility_color }}">{{ $project->visibility_label }}</span>
                            @endif
                        </td>
                        <td>{{ $project->completed_at ? $project->completed_at->format('Y-m-d') : '-' }}</td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.projects.show', $project) }}"
                                   class="btn btn-sm btn-light"
                                   data-coreui-toggle="tooltip"
                                   title="查看">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                                    </svg>
                                </a>

                                <a href="{{ route('admin.projects.clients', $project) }}"
                                   class="btn btn-sm btn-light"
                                   data-coreui-toggle="tooltip"
                                   title="客戶管理">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-people"></use>
                                    </svg>
                                </a>

                                <a href="{{ route('admin.projects.edit', $project) }}"
                                   class="btn btn-sm btn-light"
                                   data-coreui-toggle="tooltip"
                                   title="編輯">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                                    </svg>
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.projects.destroy', $project) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('確定要刪除此作品嗎？');">
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
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>{{-- /.admin-list --}}

        <div class="admin-grid">
            @foreach($projects as $project)
            <div class="admin-grid-card{{ $project->is_featured ? ' featured' : '' }}">
                <div class="admin-grid-card-header">
                    @if($project->category)
                        <span class="badge bg-secondary">{{ $project->category }}</span>
                    @else
                        <span class="badge bg-light text-dark">未分類</span>
                    @endif
                    <span class="badge bg-{{ $project->status_color }}">{{ $project->status_label }}</span>
                </div>
                <div class="admin-grid-card-body">
                    <h6>
                        {{ $project->title }}
                        @if($project->is_featured)
                            <span class="badge bg-warning ms-1">精選</span>
                        @endif
                    </h6>
                    <div class="admin-grid-card-subtitle">{{ $project->client ?? '-' }}</div>
                    @if($project->tech_stack)
                        <div class="mb-2">
                            @foreach($project->tech_stack as $tech)
                                <span class="badge bg-info me-1 mb-1">{{ $tech }}</span>
                            @endforeach
                        </div>
                    @endif
                    <dl class="admin-grid-card-meta">
                        <dt>完成日期</dt>
                        <dd>{{ $project->completed_at ? $project->completed_at->format('Y-m-d') : '-' }}</dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    <a href="{{ route('admin.projects.show', $project) }}"
                       class="btn btn-sm btn-light"
                       data-coreui-toggle="tooltip"
                       title="查看">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                        </svg>
                    </a>
                    <a href="{{ route('admin.projects.clients', $project) }}"
                       class="btn btn-sm btn-light"
                       data-coreui-toggle="tooltip"
                       title="客戶管理">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-people"></use>
                        </svg>
                    </a>
                    <a href="{{ route('admin.projects.edit', $project) }}"
                       class="btn btn-sm btn-light"
                       data-coreui-toggle="tooltip"
                       title="編輯">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                        </svg>
                    </a>
                    <form method="POST"
                          action="{{ route('admin.projects.destroy', $project) }}"
                          class="d-inline"
                          onsubmit="return confirm('確定要刪除此作品嗎？');">
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
                </div>
            </div>
            @endforeach
        </div>{{-- /.admin-grid --}}

        @else
        <div class="empty-state">
            <div class="empty-state-icon">🎨</div>
            <div>尚無作品資料</div>
            <a href="{{ route('admin.projects.create') }}" class="btn btn-primary mt-3">新增第一個作品</a>
        </div>
        @endif
    </div>

    @if($projects->hasPages())
    <div class="card-footer">
        {{ $projects->links() }}
    </div>
    @endif
</div>
@endsection
