@extends('layouts.admin')

@section('title', '報價請求管理')

@php
    $breadcrumbs = [
        ['title' => '報價請求管理', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">報價請求管理</h2>
        <p class="text-muted">管理來自網站的報價請求</p>
    </div>
    <div class="col-md-6 text-md-end">
        @include('admin.partials.view-toggle', ['pageKey' => 'quote-requests'])
    </div>
</div>

{{-- 統計卡片 --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-6 fw-semibold text-primary">{{ $totalThisMonth }}</div>
                        <div class="text-muted small text-uppercase fw-semibold">本月請求數</div>
                    </div>
                    <div class="text-primary">
                        <svg class="icon icon-xl">
                            <use xlink:href="/assets/icons/free.svg#cil-inbox"></use>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-6 fw-semibold text-warning">{{ $pendingCount }}</div>
                        <div class="text-muted small text-uppercase fw-semibold">待處理</div>
                    </div>
                    <div class="text-warning">
                        <svg class="icon icon-xl">
                            <use xlink:href="/assets/icons/free.svg#cil-clock"></use>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-6 fw-semibold text-success">{{ $quotedCount }}</div>
                        <div class="text-muted small text-uppercase fw-semibold">已報價</div>
                    </div>
                    <div class="text-success">
                        <svg class="icon icon-xl">
                            <use xlink:href="/assets/icons/free.svg#cil-check-circle"></use>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-6 fw-semibold text-info">{{ $conversionRate }}%</div>
                        <div class="text-muted small text-uppercase fw-semibold">轉換率</div>
                    </div>
                    <div class="text-info">
                        <svg class="icon icon-xl">
                            <use xlink:href="/assets/icons/free.svg#cil-chart-line"></use>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 搜尋與篩選 --}}
<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.quote-requests.index') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text"
                       class="form-control"
                       name="search"
                       placeholder="搜尋編號、姓名或 Email"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">全部狀態</option>
                    @foreach(['pending' => '待處理', 'reviewing' => '審核中', 'quoted' => '已報價', 'accepted' => '已接受', 'rejected' => '已拒絕', 'expired' => '已過期'] as $val => $label)
                        <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
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
                <a href="{{ route('admin.quote-requests.index') }}" class="btn btn-light">清除</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($quoteRequests->count() > 0)
        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>請求編號</th>
                        <th>客戶名稱 / Email</th>
                        <th>服務類型</th>
                        <th>估算金額</th>
                        <th>狀態</th>
                        <th>建立時間</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quoteRequests as $quoteRequest)
                    <tr>
                        <td>
                            <a href="{{ route('admin.quote-requests.show', $quoteRequest) }}">
                                {{ $quoteRequest->request_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $quoteRequest->name }}</div>
                            <small class="text-muted">{{ $quoteRequest->email }}</small>
                        </td>
                        <td>{{ $quoteRequest->project_type ?? '-' }}</td>
                        <td>
                            @if($quoteRequest->estimated_min || $quoteRequest->estimated_max)
                                NT$ {{ number_format($quoteRequest->estimated_min) }} ~ {{ number_format($quoteRequest->estimated_max) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $quoteRequest->status_color }}">
                                {{ $quoteRequest->status_label }}
                            </span>
                        </td>
                        <td>{{ $quoteRequest->created_at->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.quote-requests.show', $quoteRequest) }}"
                               class="btn btn-sm btn-light"
                               data-coreui-toggle="tooltip"
                               title="查看詳情">
                                <svg class="icon">
                                    <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>{{-- /.admin-list --}}

        <div class="admin-grid">
            @foreach($quoteRequests as $quoteRequest)
            <div class="admin-grid-card">
                <div class="admin-grid-card-header">
                    <a href="{{ route('admin.quote-requests.show', $quoteRequest) }}">
                        {{ $quoteRequest->request_number }}
                    </a>
                    <span class="badge bg-{{ $quoteRequest->status_color }}">{{ $quoteRequest->status_label }}</span>
                </div>
                <div class="admin-grid-card-body">
                    <h6>{{ $quoteRequest->name }}</h6>
                    <div class="admin-grid-card-subtitle">{{ $quoteRequest->email }}</div>
                    <dl class="admin-grid-card-meta">
                        <dt>類型</dt>
                        <dd>{{ $quoteRequest->project_type ?? '-' }}</dd>
                        <dt>估算</dt>
                        <dd>
                            @if($quoteRequest->estimated_min || $quoteRequest->estimated_max)
                                NT$ {{ number_format($quoteRequest->estimated_min) }}~{{ number_format($quoteRequest->estimated_max) }}
                            @else
                                -
                            @endif
                        </dd>
                        <dt>建立</dt>
                        <dd>{{ $quoteRequest->created_at->format('Y-m-d H:i') }}</dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    <a href="{{ route('admin.quote-requests.show', $quoteRequest) }}"
                       class="btn btn-sm btn-light"
                       data-coreui-toggle="tooltip"
                       title="查看詳情">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                        </svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>{{-- /.admin-grid --}}

        @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg class="icon icon-xl">
                    <use xlink:href="/assets/icons/free.svg#cil-inbox"></use>
                </svg>
            </div>
            <div>尚無報價請求</div>
            <p class="text-muted mt-2">當客戶透過網站提交報價請求時，會顯示在這裡。</p>
        </div>
        @endif
    </div>

    @if($quoteRequests->hasPages())
    <div class="card-footer">
        {{ $quoteRequests->links() }}
    </div>
    @endif
</div>
@endsection
