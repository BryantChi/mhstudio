@extends('layouts.admin')

@section('title', '合約管理')

@php
    $breadcrumbs = [['title' => '合約管理', 'url' => '#']];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">合約管理</h2>
        <p class="text-muted">管理合約與範本</p>
    </div>
    <div class="col-md-6 text-md-end">
        @include('admin.partials.view-toggle', ['pageKey' => 'contracts'])
        <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary">
            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg>
            新增合約
        </a>
    </div>
</div>

{{-- 即將到期提醒 --}}
@if($expiringSoon->isNotEmpty())
<div class="alert alert-warning">
    <strong>即將到期合約：</strong>
    @foreach($expiringSoon as $ec)
        <span class="badge bg-warning text-dark me-1">
            {{ $ec->contract_number }} - {{ $ec->client->name }}
            ({{ $ec->end_date->format('Y-m-d') }})
        </span>
    @endforeach
</div>
@endif

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.contracts.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="搜尋合約編號/標題/客戶"
                       value="{{ request('search') }}" data-search>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">全部狀態</option>
                    @foreach(['draft' => '草稿', 'sent' => '已送出', 'signed' => '已簽署', 'active' => '執行中', 'completed' => '已完成', 'cancelled' => '已取消'] as $val => $label)
                        <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="type">
                    <option value="">全部類型</option>
                    @foreach(['service' => '服務合約', 'maintenance' => '維護合約', 'retainer' => '長期顧問', 'nda' => '保密協議', 'other' => '其他'] as $val => $label)
                        <option value="{{ $val }}" {{ request('type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="client_id" onchange="this.form.submit()">
                    <option value="">所有客戶</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-secondary">搜尋</button>
                <a href="{{ route('admin.contracts.index') }}" class="btn btn-light">清除</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($contracts->count() > 0)
        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>合約編號</th>
                        <th>標題</th>
                        <th>客戶</th>
                        <th>類型</th>
                        <th>狀態</th>
                        <th>總金額</th>
                        <th>付款狀態</th>
                        <th>期間</th>
                        <th>剩餘天數</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contracts as $contract)
                    <tr class="{{ $contract->is_expiring_soon ? 'table-warning' : '' }}">
                        <td><a href="{{ route('admin.contracts.show', $contract) }}">{{ $contract->contract_number }}</a></td>
                        <td>{{ $contract->title }}</td>
                        <td><a href="{{ route('admin.clients.show', $contract->client) }}">{{ $contract->client->name }}</a></td>
                        <td>{{ $contract->type_label }}</td>
                        <td>
                            <span class="badge bg-{{ $contract->status_color }}">{{ $contract->status_label }}</span>
                            @if($contract->is_expiring_soon)
                                <span class="badge bg-warning text-dark">即將到期</span>
                            @endif
                        </td>
                        <td>{{ $contract->total > 0 ? 'NT$ ' . number_format($contract->total) : '-' }}</td>
                        <td>
                            @if($contract->total > 0)
                                <span class="badge bg-{{ $contract->payment_status_color }}">{{ $contract->payment_status_label }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            {{ $contract->start_date?->format('Y-m-d') ?? '-' }}
                            @if($contract->end_date) ~ {{ $contract->end_date->format('Y-m-d') }} @endif
                        </td>
                        <td>
                            @if($contract->end_date)
                                @php $daysLeft = now()->diffInDays($contract->end_date, false); @endphp
                                @if($daysLeft < 0)
                                    <span class="text-danger fw-bold">已逾期 {{ abs(intval($daysLeft)) }} 天</span>
                                @elseif($daysLeft <= 30)
                                    <span class="text-warning fw-bold">{{ intval($daysLeft) }} 天</span>
                                @else
                                    <span>{{ intval($daysLeft) }} 天</span>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="查看詳情">
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-info"></use></svg>
                                </a>
                                <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="編輯">
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-pencil"></use></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.contracts.duplicate', $contract) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="複製">
                                        <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-copy"></use></svg>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.contracts.destroy', $contract) }}" class="d-inline">
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
            @foreach($contracts as $contract)
            <div class="admin-grid-card{{ $contract->is_expiring_soon ? ' warning' : '' }}">
                <div class="admin-grid-card-header">
                    <a href="{{ route('admin.contracts.show', $contract) }}" class="text-decoration-none fw-semibold">{{ $contract->contract_number }}</a>
                    <div>
                        <span class="badge bg-{{ $contract->status_color }}">{{ $contract->status_label }}</span>
                        @if($contract->is_expiring_soon)
                            <span class="badge bg-warning text-dark">即將到期</span>
                        @endif
                    </div>
                </div>
                <div class="admin-grid-card-body">
                    <h6><a href="{{ route('admin.contracts.show', $contract) }}">{{ $contract->title }}</a></h6>
                    <div class="admin-grid-card-subtitle">
                        <a href="{{ route('admin.clients.show', $contract->client) }}" class="text-decoration-none">{{ $contract->client->name }}</a>
                        &middot; {{ $contract->type_label }}
                    </div>
                    @if($contract->total > 0)
                        <div class="admin-grid-card-price">NT$ {{ number_format($contract->total) }}</div>
                    @endif
                    <dl class="admin-grid-card-meta">
                        <dt>期間</dt>
                        <dd>
                            {{ $contract->start_date?->format('Y-m-d') ?? '-' }}
                            @if($contract->end_date) ~ {{ $contract->end_date->format('Y-m-d') }} @endif
                        </dd>
                        <dt>剩餘</dt>
                        <dd>
                            @if($contract->end_date)
                                @php $daysLeft = now()->diffInDays($contract->end_date, false); @endphp
                                @if($daysLeft < 0)
                                    <span class="text-danger fw-bold">已逾期 {{ abs(intval($daysLeft)) }} 天</span>
                                @elseif($daysLeft <= 30)
                                    <span class="text-warning fw-bold">{{ intval($daysLeft) }} 天</span>
                                @else
                                    <span>{{ intval($daysLeft) }} 天</span>
                                @endif
                            @else
                                -
                            @endif
                        </dd>
                        <dt>付款</dt>
                        <dd>
                            @if($contract->total > 0)
                                <span class="badge bg-{{ $contract->payment_status_color }}">{{ $contract->payment_status_label }}</span>
                            @else
                                -
                            @endif
                        </dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="查看詳情">
                            <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-info"></use></svg>
                        </a>
                        <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="編輯">
                            <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-pencil"></use></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.contracts.duplicate', $contract) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="複製">
                                <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-copy"></use></svg>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.contracts.destroy', $contract) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light text-danger" data-coreui-toggle="tooltip" title="刪除" data-confirm-delete>
                                <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>{{-- /.admin-grid --}}
        @else
        <div class="empty-state">
            <div class="empty-state-icon">📄</div>
            <div>尚無合約資料</div>
            <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary mt-3">新增第一份合約</a>
        </div>
        @endif
    </div>

    @if($contracts->hasPages())
    <div class="card-footer">{{ $contracts->links() }}</div>
    @endif
</div>
@endsection
