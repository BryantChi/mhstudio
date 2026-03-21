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

{{-- 快捷統計標籤 --}}
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('admin.projects.index') }}" class="badge {{ !request()->hasAny(['status','visibility','featured','category','search']) ? 'bg-primary' : 'bg-light text-dark' }} text-decoration-none" style="font-size:.8125rem;padding:.45rem .75rem;">
        全部 <span class="ms-1 opacity-75">{{ $counts['total'] }}</span>
    </a>
    <a href="{{ route('admin.projects.index', ['status' => 'published']) }}" class="badge {{ request('status') == 'published' ? 'bg-success' : 'bg-light text-dark' }} text-decoration-none" style="font-size:.8125rem;padding:.45rem .75rem;">
        已發布 <span class="ms-1 opacity-75">{{ $counts['published'] }}</span>
    </a>
    <a href="{{ route('admin.projects.index', ['status' => 'draft']) }}" class="badge {{ request('status') == 'draft' ? 'bg-secondary' : 'bg-light text-dark' }} text-decoration-none" style="font-size:.8125rem;padding:.45rem .75rem;">
        草稿 <span class="ms-1 opacity-75">{{ $counts['draft'] }}</span>
    </a>
    <span class="border-start mx-1"></span>
    <a href="{{ route('admin.projects.index', ['visibility' => 'public']) }}" class="badge {{ request('visibility') == 'public' ? 'bg-success' : 'bg-light text-dark' }} text-decoration-none" style="font-size:.8125rem;padding:.45rem .75rem;">
        公開 <span class="ms-1 opacity-75">{{ $counts['public'] }}</span>
    </a>
    @if($counts['showcase'] > 0)
    <a href="{{ route('admin.projects.index', ['visibility' => 'showcase']) }}" class="badge {{ request('visibility') == 'showcase' ? 'bg-info' : 'bg-light text-dark' }} text-decoration-none" style="font-size:.8125rem;padding:.45rem .75rem;">
        僅展示 <span class="ms-1 opacity-75">{{ $counts['showcase'] }}</span>
    </a>
    @endif
    @if($counts['unlisted'] > 0)
    <a href="{{ route('admin.projects.index', ['visibility' => 'unlisted']) }}" class="badge {{ request('visibility') == 'unlisted' ? 'bg-warning text-dark' : 'bg-light text-dark' }} text-decoration-none" style="font-size:.8125rem;padding:.45rem .75rem;">
        僅限連結 <span class="ms-1 opacity-75">{{ $counts['unlisted'] }}</span>
    </a>
    @endif
    @if($counts['hidden'] > 0)
    <a href="{{ route('admin.projects.index', ['visibility' => 'hidden']) }}" class="badge {{ request('visibility') == 'hidden' ? 'bg-dark' : 'bg-light text-dark' }} text-decoration-none" style="font-size:.8125rem;padding:.45rem .75rem;">
        隱藏 <span class="ms-1 opacity-75">{{ $counts['hidden'] }}</span>
    </a>
    @endif
    @if($counts['featured'] > 0)
    <span class="border-start mx-1"></span>
    <a href="{{ route('admin.projects.index', ['featured' => '1']) }}" class="badge {{ request('featured') == '1' ? 'bg-warning text-dark' : 'bg-light text-dark' }} text-decoration-none" style="font-size:.8125rem;padding:.45rem .75rem;">
        <svg class="me-1" style="width:12px;height:12px;vertical-align:-1px;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="currentColor" stroke="none"/></svg>
        精選 <span class="ms-1 opacity-75">{{ $counts['featured'] }}</span>
    </a>
    @endif
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.projects.index') }}" class="row g-2 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label small text-muted mb-1">關鍵字</label>
                <input type="text"
                       class="form-control"
                       name="search"
                       placeholder="搜尋標題、客戶、摘要..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <label class="form-label small text-muted mb-1">分類</label>
                <select class="form-select" name="category">
                    <option value="">全部分類</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <label class="form-label small text-muted mb-1">可見性</label>
                <select class="form-select" name="visibility">
                    <option value="">全部可見性</option>
                    <option value="public" {{ request('visibility') == 'public' ? 'selected' : '' }}>公開</option>
                    <option value="showcase" {{ request('visibility') == 'showcase' ? 'selected' : '' }}>僅展示</option>
                    <option value="unlisted" {{ request('visibility') == 'unlisted' ? 'selected' : '' }}>僅限連結</option>
                    <option value="hidden" {{ request('visibility') == 'hidden' ? 'selected' : '' }}>隱藏</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <label class="form-label small text-muted mb-1">排序</label>
                <select class="form-select" name="sort">
                    <option value="default" {{ request('sort', 'default') == 'default' ? 'selected' : '' }}>預設（精選優先）</option>
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>最新建立</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>最早建立</option>
                    <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>標題 A→Z</option>
                    <option value="order" {{ request('sort') == 'order' ? 'selected' : '' }}>排序編號</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-secondary flex-fill">
                        <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-search"></use></svg>
                        搜尋
                    </button>
                    @if(request()->hasAny(['search','status','category','visibility','featured','sort']))
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-light" data-coreui-toggle="tooltip" title="清除篩選">
                        <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-x"></use></svg>
                    </a>
                    @endif
                </div>
            </div>
            {{-- 保留隱藏的篩選值（從快捷標籤帶過來的） --}}
            @if(request('status') && !request('visibility'))
            <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            @if(request('featured'))
            <input type="hidden" name="featured" value="{{ request('featured') }}">
            @endif
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
