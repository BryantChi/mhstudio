@extends('layouts.admin')

@section('title', '用戶管理')

@php
    $breadcrumbs = [
        ['title' => '用戶管理', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">用戶管理</h2>
        <p class="text-muted">管理系統用戶與角色權限</p>
    </div>
    <div class="col-md-6 text-md-end">
        @can('create users')
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-user-plus"></use>
            </svg>
            新增用戶
        </a>
        @endcan
        @include('admin.partials.view-toggle', ['pageKey' => 'users'])
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text"
                       class="form-control"
                       name="search"
                       placeholder="搜尋用戶名稱或 Email"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="role">
                    <option value="">全部角色</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
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
                <a href="{{ route('admin.users.index') }}" class="btn btn-light">清除</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($users->count() > 0)
        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>姓名</th>
                        <th>Email</th>
                        <th>角色</th>
                        <th>建立時間</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $user->avatar }}" class="avatar avatar-sm me-2" alt="{{ $user->name }}">
                                <strong>{{ $user->name }}</strong>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge bg-info me-1">{{ $role->name }}</span>
                            @endforeach
                            @if($user->roles->isEmpty())
                                <span class="text-muted">無角色</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                @can('view users')
                                <a href="{{ route('admin.users.show', $user) }}"
                                   class="btn btn-sm btn-light"
                                   data-coreui-toggle="tooltip"
                                   title="查看">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                                    </svg>
                                </a>
                                @endcan

                                @can('edit users')
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="btn btn-sm btn-light"
                                   data-coreui-toggle="tooltip"
                                   title="編輯">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                                    </svg>
                                </a>
                                @endcan

                                @if(auth()->user()->isSuperAdmin() && $user->id !== auth()->id() && !$user->isSuperAdmin() && !session('impersonator_id'))
                                <a href="{{ route('admin.impersonate.start', $user) }}"
                                   class="btn btn-sm btn-light text-warning"
                                   data-coreui-toggle="tooltip"
                                   title="模擬登入"
                                   onclick="return confirm('確定要模擬登入為「{{ $user->name }}」嗎？');">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-user"></use>
                                    </svg>
                                </a>
                                @endif

                                @can('delete users')
                                @if($user->id !== auth()->id())
                                <form method="POST"
                                      action="{{ route('admin.users.destroy', $user) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('確定要刪除此用戶嗎？');">
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
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>{{-- /.admin-list --}}

        <div class="admin-grid">
            @foreach($users as $user)
            <div class="admin-grid-card">
                <div class="admin-grid-card-header">
                    <span>
                        @foreach($user->roles as $role)
                            <span class="badge bg-info me-1">{{ $role->name }}</span>
                        @endforeach
                        @if($user->roles->isEmpty())
                            <span class="text-muted" style="font-size:.78rem">無角色</span>
                        @endif
                    </span>
                </div>
                <div class="admin-grid-card-body">
                    @if($user->avatar)
                        <div class="mb-2">
                            <img src="{{ $user->avatar }}" class="avatar avatar-md" alt="{{ $user->name }}">
                        </div>
                    @endif
                    <h6>{{ $user->name }}</h6>
                    <div class="admin-grid-card-subtitle">{{ $user->email }}</div>
                    <dl class="admin-grid-card-meta">
                        <dt>建立</dt>
                        <dd>{{ $user->created_at->format('Y-m-d') }}</dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    @can('view users')
                    <a href="{{ route('admin.users.show', $user) }}"
                       class="btn btn-sm btn-light"
                       data-coreui-toggle="tooltip"
                       title="查看">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                        </svg>
                    </a>
                    @endcan

                    @can('edit users')
                    <a href="{{ route('admin.users.edit', $user) }}"
                       class="btn btn-sm btn-light"
                       data-coreui-toggle="tooltip"
                       title="編輯">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                        </svg>
                    </a>
                    @endcan

                    @if(auth()->user()->isSuperAdmin() && $user->id !== auth()->id() && !$user->isSuperAdmin() && !session('impersonator_id'))
                    <a href="{{ route('admin.impersonate.start', $user) }}"
                       class="btn btn-sm btn-light text-warning"
                       data-coreui-toggle="tooltip"
                       title="模擬登入"
                       onclick="return confirm('確定要模擬登入為「{{ $user->name }}」嗎？');">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-user"></use>
                        </svg>
                    </a>
                    @endif

                    @can('delete users')
                    @if($user->id !== auth()->id())
                    <form method="POST"
                          action="{{ route('admin.users.destroy', $user) }}"
                          class="d-inline"
                          onsubmit="return confirm('確定要刪除此用戶嗎？');">
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
                    @endif
                    @endcan
                </div>
            </div>
            @endforeach
        </div>{{-- /.admin-grid --}}

        @else
        <div class="empty-state">
            <div class="empty-state-icon">👥</div>
            <div>尚無用戶資料</div>
            @can('create users')
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary mt-3">新增第一個用戶</a>
            @endcan
        </div>
        @endif
    </div>

    @if($users->hasPages())
    <div class="card-footer">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
