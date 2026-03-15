@extends('layouts.admin')

@section('title', '法律頁面管理')

@php
    $breadcrumbs = [
        ['title' => '法律頁面管理', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">法律頁面管理</h2>
        <p class="text-muted">管理隱私權政策、服務條款等法律文件</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.legal-pages.create') }}" class="btn btn-primary">
            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg>
            新增頁面
        </a>
        @include('admin.partials.view-toggle', ['pageKey' => 'legal-pages'])
        @include('admin.partials.sortable-mode', [
            'reorderUrl' => route('admin.legal-pages.reorder'),
            'fetchUrl' => route('admin.legal-pages.index', ['_sortable' => 1]),
            'itemLabel' => '法律頁面',
            'titleField' => 'title',
        ])
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.legal-pages.index') }}" class="row g-3">
            <div class="col-md-3">
                <select class="form-select" name="type" onchange="this.form.submit()">
                    <option value="">全部類型</option>
                    @foreach(\App\Models\LegalPage::TYPES as $val => $label)
                        <option value="{{ $val }}" {{ request('type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="is_active" onchange="this.form.submit()">
                    <option value="">全部狀態</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>啟用</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>停用</option>
                </select>
            </div>
            <div class="col-md-auto">
                <a href="{{ route('admin.legal-pages.index') }}" class="btn btn-light">清除</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($pages->count() > 0)
        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="50">排序</th>
                        <th>標題</th>
                        <th>類型</th>
                        <th>Slug</th>
                        <th>狀態</th>
                        <th>更新時間</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pages as $page)
                    <tr>
                        <td>{{ $page->order }}</td>
                        <td><strong>{{ $page->title }}</strong></td>
                        <td><span class="badge bg-{{ $page->type_color }}">{{ $page->type_label }}</span></td>
                        <td><code>/legal/{{ $page->slug }}</code></td>
                        <td>
                            @if($page->is_active)
                                <span class="badge bg-success">啟用</span>
                            @else
                                <span class="badge bg-secondary">停用</span>
                            @endif
                        </td>
                        <td>{{ $page->updated_at->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('legal.show', $page->slug) }}"
                                   class="btn btn-sm btn-light"
                                   target="_blank"
                                   data-coreui-toggle="tooltip"
                                   title="前台預覽">
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-external-link"></use></svg>
                                </a>
                                <a href="{{ route('admin.legal-pages.edit', $page) }}"
                                   class="btn btn-sm btn-light"
                                   data-coreui-toggle="tooltip"
                                   title="編輯">
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-pencil"></use></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.legal-pages.destroy', $page) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-light text-danger"
                                            data-coreui-toggle="tooltip"
                                            title="刪除"
                                            data-confirm-delete>
                                        <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg>
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
            @foreach($pages as $page)
            <div class="admin-grid-card">
                <div class="admin-grid-card-header">
                    <span class="badge bg-{{ $page->type_color }}">{{ $page->type_label }}</span>
                    @if($page->is_active)
                        <span class="badge bg-success">啟用</span>
                    @else
                        <span class="badge bg-secondary">停用</span>
                    @endif
                </div>
                <div class="admin-grid-card-body">
                    <h6>{{ $page->title }}</h6>
                    <div class="admin-grid-card-subtitle"><code>/legal/{{ $page->slug }}</code></div>
                    <dl class="admin-grid-card-meta">
                        <dt>排序</dt>
                        <dd>{{ $page->order }}</dd>
                        <dt>更新</dt>
                        <dd>{{ $page->updated_at->format('Y-m-d') }}</dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    <a href="{{ route('legal.show', $page->slug) }}" class="btn btn-sm btn-light" target="_blank" data-coreui-toggle="tooltip" title="前台預覽">
                        <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-external-link"></use></svg>
                    </a>
                    <a href="{{ route('admin.legal-pages.edit', $page) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="編輯">
                        <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-pencil"></use></svg>
                    </a>
                    <form method="POST" action="{{ route('admin.legal-pages.destroy', $page) }}" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-light text-danger" data-coreui-toggle="tooltip" title="刪除" data-confirm-delete>
                            <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>{{-- /.admin-grid --}}

        @else
        <div class="empty-state">
            <div class="empty-state-icon">📜</div>
            <div>尚無法律頁面</div>
            <a href="{{ route('admin.legal-pages.create') }}" class="btn btn-primary mt-3">新增第一個法律頁面</a>
        </div>
        @endif
    </div>

    @if($pages->hasPages())
    <div class="card-footer">{{ $pages->links() }}</div>
    @endif
</div>
@endsection
