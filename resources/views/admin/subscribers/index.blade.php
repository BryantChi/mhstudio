@extends('layouts.admin')

@section('title', '電子報訂閱')

@php
    $breadcrumbs = [
        ['title' => '電子報訂閱', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">
            電子報訂閱
            @if($activeCount > 0)
                <span class="badge bg-success ms-2">{{ $activeCount }} 位訂閱中</span>
            @endif
        </h2>
        <p class="text-muted">管理電子報訂閱者</p>
    </div>
    <div class="col-md-6 text-md-end">
        @include('admin.partials.view-toggle', ['pageKey' => 'subscribers'])
        <a href="{{ route('admin.subscribers.export') }}" class="btn btn-outline-primary">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-cloud-download"></use>
            </svg>
            匯出 CSV
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.subscribers.index') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text"
                       class="form-control"
                       name="search"
                       placeholder="搜尋 Email 或姓名"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">全部狀態</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>訂閱中</option>
                    <option value="unsubscribed" {{ request('status') == 'unsubscribed' ? 'selected' : '' }}>已取消</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-secondary">
                    <svg class="icon">
                        <use xlink:href="/assets/icons/free.svg#cil-search"></use>
                    </svg>
                    搜尋
                </button>
                <a href="{{ route('admin.subscribers.index') }}" class="btn btn-light">清除</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($subscribers->count() > 0)
        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>Email</th>
                        <th>姓名</th>
                        <th>狀態</th>
                        <th>訂閱時間</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subscribers as $subscriber)
                    <tr>
                        <td>{{ $subscriber->id }}</td>
                        <td>{{ $subscriber->email }}</td>
                        <td>{{ $subscriber->name ?? '-' }}</td>
                        <td>
                            @if($subscriber->status === 'active')
                                <span class="badge bg-success">訂閱中</span>
                            @else
                                <span class="badge bg-secondary">已取消</span>
                            @endif
                        </td>
                        <td>{{ $subscriber->subscribed_at ? $subscriber->subscribed_at->format('Y-m-d H:i') : $subscriber->created_at->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <form method="POST"
                                      action="{{ route('admin.subscribers.destroy', $subscriber) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('確定要刪除此訂閱者嗎？');">
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
            @foreach($subscribers as $subscriber)
            <div class="admin-grid-card">
                <div class="admin-grid-card-header">
                    <span></span>
                    @if($subscriber->status === 'active')
                        <span class="badge bg-success">訂閱中</span>
                    @else
                        <span class="badge bg-secondary">已取消</span>
                    @endif
                </div>
                <div class="admin-grid-card-body">
                    <h6>{{ $subscriber->email }}</h6>
                    <div class="admin-grid-card-subtitle">{{ $subscriber->name ?? '-' }}</div>
                    <dl class="admin-grid-card-meta">
                        <dt>訂閱日期</dt>
                        <dd>{{ $subscriber->subscribed_at ? $subscriber->subscribed_at->format('Y-m-d H:i') : $subscriber->created_at->format('Y-m-d H:i') }}</dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    <form method="POST"
                          action="{{ route('admin.subscribers.destroy', $subscriber) }}"
                          class="d-inline"
                          onsubmit="return confirm('確定要刪除此訂閱者嗎？');">
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
            <div class="empty-state-icon">📧</div>
            <div>尚無訂閱者</div>
        </div>
        @endif
    </div>

    @if($subscribers->hasPages())
    <div class="card-footer">
        {{ $subscribers->links() }}
    </div>
    @endif
</div>
@endsection
