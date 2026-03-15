@extends('layouts.admin')

@section('title', '發票管理')

@php $breadcrumbs = [['title' => '發票管理', 'url' => '#']]; @endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">發票管理</h2>
        <p class="text-muted">管理發票與付款追蹤</p>
    </div>
    <div class="col-md-6 text-md-end">
        @include('admin.partials.view-toggle', ['pageKey' => 'invoices'])
        <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">
            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg> 新增發票
        </a>
    </div>
</div>

{{-- 統計卡片 --}}
<div class="row mb-4">
    <div class="col-md-3">
        <a href="{{ route('admin.invoices.index', ['status' => 'paid']) }}" class="text-decoration-none">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="fs-4 fw-semibold">NT$ {{ number_format($stats['total_revenue']) }}</div>
                    <div>總營收</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.invoices.index', ['status' => 'paid']) }}" class="text-decoration-none">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="fs-4 fw-semibold">NT$ {{ number_format($stats['month_revenue']) }}</div>
                    <div>本月營收</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.invoices.index', ['status' => 'sent']) }}" class="text-decoration-none">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="fs-4 fw-semibold">NT$ {{ number_format($stats['pending_amount']) }}</div>
                    <div>待收款項</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.invoices.index', ['status' => 'overdue']) }}" class="text-decoration-none">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <div class="fs-4 fw-semibold">{{ $stats['overdue_count'] }}</div>
                    <div>逾期發票</div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="搜尋編號/標題/客戶" value="{{ request('search') }}" data-search>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">全部狀態</option>
                    @foreach(['draft' => '草稿', 'sent' => '已送出', 'paid' => '已付款', 'partially_paid' => '部分付款', 'overdue' => '已逾期', 'cancelled' => '已取消'] as $val => $label)
                        <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-secondary">搜尋</button>
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-light">清除</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($invoices->count() > 0)
        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>發票編號</th>
                        <th>標題</th>
                        <th>客戶</th>
                        <th>狀態</th>
                        <th>金額</th>
                        <th>已付</th>
                        <th>到期日</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr class="{{ $invoice->is_overdue ? 'table-danger' : '' }}">
                        <td><a href="{{ route('admin.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                        <td>{{ $invoice->title }}</td>
                        <td><a href="{{ route('admin.clients.show', $invoice->client) }}">{{ $invoice->client->name }}</a></td>
                        <td>
                            <span class="badge bg-{{ $invoice->status_color }}">{{ $invoice->status_label }}</span>
                            @if($invoice->is_overdue)
                                <span class="badge bg-danger">逾期</span>
                            @endif
                        </td>
                        <td>NT$ {{ number_format($invoice->total) }}</td>
                        <td>NT$ {{ number_format($invoice->paid_amount) }}</td>
                        <td>{{ $invoice->due_date->format('Y-m-d') }}</td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="查看詳情">
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-info"></use></svg>
                                </a>
                                <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="編輯">
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-pencil"></use></svg>
                                </a>
                                @if(!in_array($invoice->status, ['paid', 'cancelled', 'draft']))
                                <form method="POST" action="{{ route('admin.invoices.record-payment', $invoice) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="amount" value="{{ $invoice->balance_due }}">
                                    <input type="hidden" name="payment_method" value="bank_transfer">
                                    <button type="submit" class="btn btn-sm btn-outline-success" data-coreui-toggle="tooltip" title="標記全額付清" data-confirm-delete>
                                        <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-check-circle"></use></svg>
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.invoices.destroy', $invoice) }}" class="d-inline">
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
            @foreach($invoices as $invoice)
            <div class="admin-grid-card{{ $invoice->is_overdue ? ' danger' : '' }}">
                <div class="admin-grid-card-header">
                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-decoration-none fw-semibold">{{ $invoice->invoice_number }}</a>
                    <div>
                        <span class="badge bg-{{ $invoice->status_color }}">{{ $invoice->status_label }}</span>
                        @if($invoice->is_overdue)
                            <span class="badge bg-danger">逾期</span>
                        @endif
                    </div>
                </div>
                <div class="admin-grid-card-body">
                    <h6><a href="{{ route('admin.invoices.show', $invoice) }}">{{ $invoice->title }}</a></h6>
                    <div class="admin-grid-card-subtitle">
                        <a href="{{ route('admin.clients.show', $invoice->client) }}" class="text-decoration-none">{{ $invoice->client->name }}</a>
                    </div>
                    <div class="admin-grid-card-price">NT$ {{ number_format($invoice->total) }}</div>
                    <dl class="admin-grid-card-meta">
                        <dt>已付</dt>
                        <dd>NT$ {{ number_format($invoice->paid_amount) }}</dd>
                        <dt>到期</dt>
                        <dd>{{ $invoice->due_date->format('Y-m-d') }}</dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="查看詳情">
                            <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-info"></use></svg>
                        </a>
                        <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="編輯">
                            <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-pencil"></use></svg>
                        </a>
                        @if(!in_array($invoice->status, ['paid', 'cancelled', 'draft']))
                        <form method="POST" action="{{ route('admin.invoices.record-payment', $invoice) }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="amount" value="{{ $invoice->balance_due }}">
                            <input type="hidden" name="payment_method" value="bank_transfer">
                            <button type="submit" class="btn btn-sm btn-outline-success" data-coreui-toggle="tooltip" title="標記全額付清" data-confirm-delete>
                                <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-check-circle"></use></svg>
                            </button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('admin.invoices.destroy', $invoice) }}" class="d-inline">
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
            <div class="empty-state-icon">🧾</div>
            <div>尚無發票資料</div>
            <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary mt-3">建立第一張發票</a>
        </div>
        @endif
    </div>

    @if($invoices->hasPages())
    <div class="card-footer">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
