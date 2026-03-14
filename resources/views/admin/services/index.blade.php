@extends('layouts.admin')

@section('title', '服務管理')

@php
    $breadcrumbs = [
        ['title' => '服務管理', 'url' => '#']
    ];

    $typeLabels = [
        'website' => '網站方案',
        'hosting' => '主機代管',
        'maintenance' => '維護服務',
        'addon' => '加值服務',
        'consulting' => '顧問服務',
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">服務管理</h2>
        <p class="text-muted">管理所有服務項目、方案定價與包含項目</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
            </svg>
            新增服務
        </a>
        @include('admin.partials.view-toggle', ['pageKey' => 'services'])
        @include('admin.partials.sortable-mode', [
            'reorderUrl' => route('admin.services.reorder'),
            'fetchUrl' => route('admin.services.index', ['_sortable' => 1]),
            'itemLabel' => '服務',
            'titleField' => 'title',
        ])
    </div>
</div>

{{-- Type Tabs --}}
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ !$type ? 'active' : '' }}" href="{{ route('admin.services.index') }}">
            全部 <span class="badge bg-secondary ms-1">{{ $typeCounts['all'] }}</span>
        </a>
    </li>
    @foreach($typeLabels as $key => $label)
    @if($typeCounts[$key] > 0)
    <li class="nav-item">
        <a class="nav-link {{ $type === $key ? 'active' : '' }}" href="{{ route('admin.services.index', ['type' => $key]) }}">
            {{ $label }} <span class="badge bg-secondary ms-1">{{ $typeCounts[$key] }}</span>
        </a>
    </li>
    @endif
    @endforeach
</ul>

<div class="card">
    <div class="card-body p-0">
        @if($services->count() > 0)
        @php
            $typeColors = [
                'website' => 'primary',
                'hosting' => 'info',
                'maintenance' => 'warning',
                'addon' => 'secondary',
                'consulting' => 'success',
            ];
        @endphp

        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>標題</th>
                        <th>類型</th>
                        <th>價格</th>
                        <th>週期</th>
                        <th>排序</th>
                        <th class="text-center">推薦</th>
                        <th class="text-center">首頁</th>
                        <th class="text-center">狀態</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($services as $service)
                    <tr>
                        <td>{{ $service->id }}</td>
                        <td>
                            <strong>{{ $service->title }}</strong>
                            @if($service->subtitle)
                                <br><small class="text-muted">{{ $service->subtitle }}</small>
                            @endif
                            @if($service->icon)
                                <br><small class="text-muted"><code>{{ $service->icon }}</code></small>
                            @endif
                        </td>
                        <td>
                            @if($service->type)
                                <span class="badge bg-{{ $typeColors[$service->type] ?? 'secondary' }}">{{ $service->type_label }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $service->formatted_price ?: ($service->price_range ?: '-') }}</td>
                        <td>{{ $service->billing_cycle_label ?: '-' }}</td>
                        <td>{{ $service->order }}</td>
                        <td class="text-center">
                            @if($service->is_featured)
                                <span class="badge bg-primary">推薦</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($service->show_on_homepage)
                                <svg class="icon text-success"><use xlink:href="/assets/icons/free.svg#cil-check-alt"></use></svg>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($service->is_active)
                                <span class="badge bg-success">啟用</span>
                            @else
                                <span class="badge bg-secondary">停用</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.services.edit', $service) }}"
                                   class="btn btn-sm btn-light"
                                   data-coreui-toggle="tooltip"
                                   title="編輯">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                                    </svg>
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.services.destroy', $service) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('確定要刪除「{{ $service->title }}」嗎？');">
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
            @foreach($services as $service)
            <div class="admin-grid-card{{ $service->is_featured ? ' featured' : '' }}">
                <div class="admin-grid-card-header">
                    @if($service->type)
                        <span class="badge bg-{{ $typeColors[$service->type] ?? 'secondary' }}">{{ $service->type_label }}</span>
                    @else
                        <span></span>
                    @endif
                    @if($service->is_active)
                        <span class="badge bg-success">啟用</span>
                    @else
                        <span class="badge bg-secondary">停用</span>
                    @endif
                </div>
                <div class="admin-grid-card-body">
                    <h6>
                        {{ $service->title }}
                        @if($service->is_featured)
                            <span class="badge bg-primary ms-1">推薦</span>
                        @endif
                        @if($service->show_on_homepage)
                            <span class="badge bg-info ms-1">首頁</span>
                        @endif
                    </h6>
                    @if($service->subtitle)
                        <div class="admin-grid-card-subtitle">{{ $service->subtitle }}</div>
                    @endif
                    <div class="admin-grid-card-price">{{ $service->formatted_price ?: ($service->price_range ?: '-') }}</div>
                    <dl class="admin-grid-card-meta">
                        <dt>週期</dt>
                        <dd>{{ $service->billing_cycle_label ?: '-' }}</dd>
                        <dt>排序</dt>
                        <dd>{{ $service->order }}</dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    <a href="{{ route('admin.services.edit', $service) }}"
                       class="btn btn-sm btn-light"
                       data-coreui-toggle="tooltip"
                       title="編輯">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                        </svg>
                    </a>
                    <form method="POST"
                          action="{{ route('admin.services.destroy', $service) }}"
                          class="d-inline"
                          onsubmit="return confirm('確定要刪除「{{ $service->title }}」嗎？');">
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
            <div class="empty-state-icon">🛠</div>
            <div>尚無服務項目</div>
            <a href="{{ route('admin.services.create') }}" class="btn btn-primary mt-3">新增第一個服務</a>
        </div>
        @endif
    </div>

    @if($services->hasPages())
    <div class="card-footer">
        {{ $services->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
