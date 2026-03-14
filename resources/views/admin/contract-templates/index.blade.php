@extends('layouts.admin')

@section('title', '合約範本')

@php
    $breadcrumbs = [
        ['title' => '合約管理', 'url' => route('admin.contracts.index')],
        ['title' => '合約範本', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">合約範本</h2>
        <p class="text-muted">管理合約範本，加速合約建立</p>
    </div>
    <div class="col-md-6 text-md-end">
        @include('admin.partials.view-toggle', ['pageKey' => 'contract-templates'])
        @include('admin.partials.sortable-mode', [
            'reorderUrl' => route('admin.contract-templates.reorder'),
            'fetchUrl' => route('admin.contract-templates.index', ['_sortable' => 1]),
            'itemLabel' => '範本',
            'titleField' => 'name',
        ])
        <a href="{{ route('admin.contract-templates.create') }}" class="btn btn-primary">
            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg>
            新增範本
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.contract-templates.index') }}" class="row g-3">
            <div class="col-md-3">
                <select class="form-select" name="type" onchange="this.form.submit()">
                    <option value="">全部類型</option>
                    @foreach(['service' => '服務合約', 'maintenance' => '維護合約', 'retainer' => '長期顧問', 'nda' => '保密協議', 'other' => '其他'] as $val => $label)
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
                <a href="{{ route('admin.contract-templates.index') }}" class="btn btn-light">清除</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($templates->count() > 0)
        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>排序</th>
                        <th>名稱</th>
                        <th>類型</th>
                        <th>說明</th>
                        <th>預設金額</th>
                        <th>狀態</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $template)
                    <tr>
                        <td>{{ $template->order }}</td>
                        <td><strong>{{ $template->name }}</strong></td>
                        <td><span class="badge bg-info">{{ $template->type_label }}</span></td>
                        <td>{{ Str::limit($template->description, 50) ?? '-' }}</td>
                        <td>{{ $template->default_amount ? 'NT$ ' . number_format($template->default_amount) : '-' }}</td>
                        <td>
                            @if($template->is_active)
                                <span class="badge bg-success">啟用</span>
                            @else
                                <span class="badge bg-secondary">停用</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.contracts.create', ['template_id' => $template->id]) }}" class="btn btn-sm btn-outline-success" data-coreui-toggle="tooltip" title="使用此範本建立合約">
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg>
                                </a>
                                <a href="{{ route('admin.contract-templates.edit', $template) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="編輯">
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-pencil"></use></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.contract-templates.destroy', $template) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light text-danger" data-coreui-toggle="tooltip" title="刪除" data-confirm-delete>
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
            @foreach($templates as $template)
            <div class="admin-grid-card">
                <div class="admin-grid-card-header">
                    <span class="badge bg-info">{{ $template->type_label }}</span>
                    @if($template->is_active)
                        <span class="badge bg-success">啟用</span>
                    @else
                        <span class="badge bg-secondary">停用</span>
                    @endif
                </div>
                <div class="admin-grid-card-body">
                    <h6>{{ $template->name }}</h6>
                    <div class="admin-grid-card-subtitle">{{ Str::limit($template->description, 80) ?? '-' }}</div>
                    <dl class="admin-grid-card-meta">
                        @if($template->default_amount)
                        <dt>金額</dt>
                        <dd>NT$ {{ number_format($template->default_amount) }}</dd>
                        @endif
                        <dt>排序</dt>
                        <dd>{{ $template->order }}</dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    <a href="{{ route('admin.contracts.create', ['template_id' => $template->id]) }}"
                       class="btn btn-sm btn-outline-success"
                       data-coreui-toggle="tooltip"
                       title="使用此範本建立合約">
                        <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg>
                    </a>
                    <a href="{{ route('admin.contract-templates.edit', $template) }}"
                       class="btn btn-sm btn-light"
                       data-coreui-toggle="tooltip"
                       title="編輯">
                        <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-pencil"></use></svg>
                    </a>
                    <form method="POST" action="{{ route('admin.contract-templates.destroy', $template) }}" class="d-inline">
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
            </div>
            @endforeach
        </div>{{-- /.admin-grid --}}

        @else
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <div>尚無合約範本</div>
            <a href="{{ route('admin.contract-templates.create') }}" class="btn btn-primary mt-3">新增第一個範本</a>
        </div>
        @endif
    </div>

    @if($templates->hasPages())
    <div class="card-footer">{{ $templates->links() }}</div>
    @endif
</div>
@endsection
