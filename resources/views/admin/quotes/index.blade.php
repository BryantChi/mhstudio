@extends('layouts.admin')

@section('title', '報價單管理')

@php $breadcrumbs = [['title' => '報價單管理', 'url' => '#']]; @endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">報價單管理</h2>
        <p class="text-muted">管理報價單與項目明細</p>
    </div>
    <div class="col-md-6 text-md-end">
        @include('admin.partials.view-toggle', ['pageKey' => 'quotes'])
        <a href="{{ route('admin.quotes.create') }}" class="btn btn-primary">
            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg> 新增報價單
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.quotes.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="搜尋編號/標題/客戶" value="{{ request('search') }}" data-search>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">全部狀態</option>
                    @foreach(['draft' => '草稿', 'sent' => '已送出', 'accepted' => '已接受', 'rejected' => '已拒絕', 'expired' => '已過期'] as $val => $label)
                        <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-secondary">搜尋</button>
                <a href="{{ route('admin.quotes.index') }}" class="btn btn-light">清除</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($quotes->count() > 0)
        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>報價編號</th>
                        <th>標題</th>
                        <th>客戶</th>
                        <th>狀態</th>
                        <th>金額</th>
                        <th>有效期限</th>
                        <th>建立日期</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotes as $quote)
                    <tr>
                        <td><a href="{{ route('admin.quotes.show', $quote) }}">{{ $quote->quote_number }}</a></td>
                        <td>{{ $quote->title }}</td>
                        <td><a href="{{ route('admin.clients.show', $quote->client) }}">{{ $quote->client->name }}</a></td>
                        <td><span class="badge bg-{{ $quote->status_color }}">{{ $quote->status_label }}</span></td>
                        <td>NT$ {{ number_format($quote->total) }}</td>
                        <td>
                            @if($quote->valid_until)
                                {{ $quote->valid_until->format('Y-m-d') }}
                                @if($quote->valid_until->isPast())
                                    <span class="badge bg-danger">已過期</span>
                                @elseif($quote->valid_until->diffInDays(now()) <= 7)
                                    <span class="badge bg-warning">即將過期</span>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $quote->created_at->format('Y-m-d') }}</td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.quotes.show', $quote) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="查看詳情">
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-info"></use></svg>
                                </a>
                                <a href="{{ route('admin.quotes.edit', $quote) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="編輯">
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-pencil"></use></svg>
                                </a>
                                @if($quote->status === 'accepted' && !$quote->invoice)
                                <form method="POST" action="{{ route('admin.quotes.convert', $quote) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" data-coreui-toggle="tooltip" title="轉為發票" data-confirm-delete>
                                        <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-arrow-right"></use></svg>
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.quotes.destroy', $quote) }}" class="d-inline">
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
            @foreach($quotes as $quote)
            <div class="admin-grid-card">
                <div class="admin-grid-card-header">
                    <a href="{{ route('admin.quotes.show', $quote) }}" class="text-decoration-none fw-semibold">{{ $quote->quote_number }}</a>
                    <span class="badge bg-{{ $quote->status_color }}">{{ $quote->status_label }}</span>
                </div>
                <div class="admin-grid-card-body">
                    <h6><a href="{{ route('admin.quotes.show', $quote) }}">{{ $quote->title }}</a></h6>
                    <div class="admin-grid-card-subtitle">
                        <a href="{{ route('admin.clients.show', $quote->client) }}" class="text-decoration-none">{{ $quote->client->name }}</a>
                    </div>
                    <div class="admin-grid-card-price">NT$ {{ number_format($quote->total) }}</div>
                    <dl class="admin-grid-card-meta">
                        <dt>有效期限</dt>
                        <dd>
                            @if($quote->valid_until)
                                {{ $quote->valid_until->format('Y-m-d') }}
                                @if($quote->valid_until->isPast())
                                    <span class="badge bg-danger">已過期</span>
                                @elseif($quote->valid_until->diffInDays(now()) <= 7)
                                    <span class="badge bg-warning">即將過期</span>
                                @endif
                            @else
                                -
                            @endif
                        </dd>
                        <dt>建立</dt>
                        <dd>{{ $quote->created_at->format('Y-m-d') }}</dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.quotes.show', $quote) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="查看詳情">
                            <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-info"></use></svg>
                        </a>
                        <a href="{{ route('admin.quotes.edit', $quote) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="編輯">
                            <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-pencil"></use></svg>
                        </a>
                        @if($quote->status === 'accepted' && !$quote->invoice)
                        <form method="POST" action="{{ route('admin.quotes.convert', $quote) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success" data-coreui-toggle="tooltip" title="轉為發票" data-confirm-delete>
                                <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-arrow-right"></use></svg>
                            </button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('admin.quotes.destroy', $quote) }}" class="d-inline">
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
            <div class="empty-state-icon">💰</div>
            <div>尚無報價單</div>
            <a href="{{ route('admin.quotes.create') }}" class="btn btn-primary mt-3">建立第一份報價單</a>
        </div>
        @endif
    </div>

    @if($quotes->hasPages())
    <div class="card-footer">{{ $quotes->links() }}</div>
    @endif
</div>
@endsection
