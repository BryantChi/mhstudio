@extends('layouts.admin')

@section('title', '客戶管理')

@php
    $breadcrumbs = [
        ['title' => '客戶管理', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">客戶管理</h2>
        <p class="text-muted">管理客戶資料與互動紀錄</p>
    </div>
    <div class="col-md-6 text-md-end">
        @include('admin.partials.view-toggle', ['pageKey' => 'clients'])
        <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
            </svg>
            新增客戶
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.clients.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search"
                       placeholder="搜尋客戶名稱/Email/公司"
                       value="{{ request('search') }}" data-search>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">全部狀態</option>
                    <option value="lead" {{ request('status') == 'lead' ? 'selected' : '' }}>潛在客戶</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>活躍</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>不活躍</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>已歸檔</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="tier">
                    <option value="">全部等級</option>
                    <option value="standard" {{ request('tier') == 'standard' ? 'selected' : '' }}>標準</option>
                    <option value="premium" {{ request('tier') == 'premium' ? 'selected' : '' }}>高級</option>
                    <option value="vip" {{ request('tier') == 'vip' ? 'selected' : '' }}>VIP</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="source">
                    <option value="">全部來源</option>
                    <option value="website" {{ request('source') == 'website' ? 'selected' : '' }}>網站</option>
                    <option value="referral" {{ request('source') == 'referral' ? 'selected' : '' }}>推薦</option>
                    <option value="social" {{ request('source') == 'social' ? 'selected' : '' }}>社群媒體</option>
                    <option value="cold_outreach" {{ request('source') == 'cold_outreach' ? 'selected' : '' }}>主動開發</option>
                    <option value="other" {{ request('source') == 'other' ? 'selected' : '' }}>其他</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-secondary">
                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-search"></use></svg>
                    搜尋
                </button>
                <a href="{{ route('admin.clients.index') }}" class="btn btn-light">清除</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($clients->count() > 0)
        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>名稱</th>
                        <th>聯繫人</th>
                        <th>Email</th>
                        <th>來源</th>
                        <th>等級</th>
                        <th>狀態</th>
                        <th>累計營收</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                    <tr>
                        <td>{{ $client->id }}</td>
                        <td>
                            <strong>
                                <a href="{{ route('admin.clients.show', $client) }}" class="text-decoration-none">
                                    {{ $client->name }}
                                </a>
                            </strong>
                            @if($client->company)
                                <br><small class="text-muted">{{ $client->company }}</small>
                            @endif
                        </td>
                        <td>{{ $client->contact_person ?? '-' }}</td>
                        <td>{{ $client->email ?? '-' }}</td>
                        <td>{{ $client->source_label }}</td>
                        <td>
                            <span class="badge bg-{{ $client->tier_color }}">{{ $client->tier_label }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $client->status_color }}">{{ $client->status_label }}</span>
                        </td>
                        <td>
                            @if($client->total_revenue > 0)
                                <strong class="text-success">NT$ {{ number_format($client->total_revenue) }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.clients.show', $client) }}"
                                   class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="查看詳情">
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-info"></use></svg>
                                </a>
                                <a href="{{ route('admin.clients.edit', $client) }}"
                                   class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="編輯">
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-pencil"></use></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.clients.destroy', $client) }}"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light text-danger"
                                            data-coreui-toggle="tooltip" title="刪除" data-confirm-delete>
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
            @foreach($clients as $client)
            <div class="admin-grid-card">
                <div class="admin-grid-card-header">
                    <span class="badge bg-{{ $client->tier_color }}">{{ $client->tier_label }}</span>
                    <span class="badge bg-{{ $client->status_color }}">{{ $client->status_label }}</span>
                </div>
                <div class="admin-grid-card-body">
                    <h6><a href="{{ route('admin.clients.show', $client) }}">{{ $client->name }}</a></h6>
                    <div class="admin-grid-card-subtitle">
                        @if($client->company){{ $client->company }} &middot; @endif
                        {{ $client->email ?? '-' }}
                    </div>
                    <dl class="admin-grid-card-meta">
                        <dt>來源</dt>
                        <dd>{{ $client->source_label }}</dd>
                        <dt>營收</dt>
                        <dd>
                            @if($client->total_revenue > 0)
                                <strong class="text-success">NT$ {{ number_format($client->total_revenue) }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.clients.show', $client) }}"
                           class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="查看詳情">
                            <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-info"></use></svg>
                        </a>
                        <a href="{{ route('admin.clients.edit', $client) }}"
                           class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="編輯">
                            <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-pencil"></use></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.clients.destroy', $client) }}"
                              class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light text-danger"
                                    data-coreui-toggle="tooltip" title="刪除" data-confirm-delete>
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
            <div class="empty-state-icon">👥</div>
            <div>尚無客戶資料</div>
            <a href="{{ route('admin.clients.create') }}" class="btn btn-primary mt-3">新增第一位客戶</a>
        </div>
        @endif
    </div>

    @if($clients->hasPages())
    <div class="card-footer">
        {{ $clients->links() }}
    </div>
    @endif
</div>
@endsection
