@extends('layouts.admin')

@section('title', '聯繫訊息')

@php
    $breadcrumbs = [
        ['title' => '聯繫訊息', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">
            聯繫訊息
            @if($unreadCount > 0)
                <span class="badge bg-danger ms-2">{{ $unreadCount }} 未讀</span>
            @endif
        </h2>
        <p class="text-muted">管理訪客聯繫訊息</p>
    </div>
    <div class="col-md-6 text-md-end">
        @include('admin.partials.view-toggle', ['pageKey' => 'contact-messages'])
        @if($unreadCount > 0)
        <form method="POST" action="{{ route('admin.contact-messages.mark-all-read') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-primary">
                <svg class="icon me-2">
                    <use xlink:href="/assets/icons/free.svg#cil-envelope-closed"></use>
                </svg>
                全部標記已讀
            </button>
        </form>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text"
                       class="form-control"
                       name="search"
                       placeholder="搜尋姓名或 Email"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">全部狀態</option>
                    <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>未讀</option>
                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>已讀</option>
                    <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>已回覆</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>已封存</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-secondary">
                    <svg class="icon">
                        <use xlink:href="/assets/icons/free.svg#cil-search"></use>
                    </svg>
                    搜尋
                </button>
                <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-light">清除</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($messages->count() > 0)
        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>姓名</th>
                        <th>Email</th>
                        <th>專案類型</th>
                        <th>狀態</th>
                        <th>送出時間</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $message)
                    <tr class="{{ $message->status === 'unread' ? 'table-warning' : '' }}">
                        <td>{{ $message->id }}</td>
                        <td>
                            @if($message->status === 'unread')
                                <strong>{{ $message->name }}</strong>
                            @else
                                {{ $message->name }}
                            @endif
                        </td>
                        <td>{{ $message->email }}</td>
                        <td>{{ $message->project_type ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $message->status_color }}">
                                {{ $message->status_label }}
                            </span>
                        </td>
                        <td>{{ $message->created_at->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.contact-messages.show', $message) }}"
                                   class="btn btn-sm btn-light"
                                   data-coreui-toggle="tooltip"
                                   title="查看">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                                    </svg>
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.contact-messages.destroy', $message) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('確定要刪除此訊息嗎？');">
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
            @foreach($messages as $message)
            <div class="admin-grid-card{{ $message->status === 'unread' ? ' warning' : '' }}">
                <div class="admin-grid-card-header">
                    <span>{{ $message->project_type ?? '' }}</span>
                    <span class="badge bg-{{ $message->status_color }}">{{ $message->status_label }}</span>
                </div>
                <div class="admin-grid-card-body">
                    <h6>
                        @if($message->status === 'unread')
                            <strong>{{ $message->name }}</strong>
                        @else
                            {{ $message->name }}
                        @endif
                    </h6>
                    <div class="admin-grid-card-subtitle">{{ $message->email }}</div>
                    <dl class="admin-grid-card-meta">
                        <dt>時間</dt>
                        <dd>{{ $message->created_at->format('Y-m-d H:i') }}</dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    <a href="{{ route('admin.contact-messages.show', $message) }}"
                       class="btn btn-sm btn-light"
                       data-coreui-toggle="tooltip"
                       title="查看">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                        </svg>
                    </a>
                    <form method="POST"
                          action="{{ route('admin.contact-messages.destroy', $message) }}"
                          class="d-inline"
                          onsubmit="return confirm('確定要刪除此訊息嗎？');">
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
            <div class="empty-state-icon">📬</div>
            <div>尚無聯繫訊息</div>
        </div>
        @endif
    </div>

    @if($messages->hasPages())
    <div class="card-footer">
        {{ $messages->links() }}
    </div>
    @endif
</div>
@endsection
