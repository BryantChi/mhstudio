@extends('layouts.admin')

@section('title', '用戶詳情')

@php
    $breadcrumbs = [
        ['title' => '用戶管理', 'url' => route('admin.users.index')],
        ['title' => '用戶詳情', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">用戶詳情</h2>
        <p class="text-muted">查看用戶資訊</p>
    </div>
    <div class="col-md-6 text-md-end">
        @can('edit users')
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
            </svg>
            編輯
        </a>
        @endcan

        <a href="{{ route('admin.users.index') }}" class="btn btn-light">返回列表</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <img src="{{ $user->avatar }}" class="rounded-circle mb-3" width="120" height="120" alt="{{ $user->name }}">
                <h4>{{ $user->name }}</h4>
                <p class="text-muted">{{ $user->email }}</p>

                <div class="mt-3">
                    @foreach($user->roles as $role)
                        <span class="badge bg-info me-1">{{ $role->name }}</span>
                    @endforeach
                    @if($user->roles->isEmpty())
                        <span class="text-muted">無角色</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <strong>基本資訊</strong>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <th width="200">用戶 ID</th>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <th>姓名</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Email 驗證時間</th>
                            <td>
                                @if($user->email_verified_at)
                                    {{ $user->email_verified_at->format('Y-m-d H:i:s') }}
                                    <span class="badge bg-success ms-2">已驗證</span>
                                @else
                                    <span class="badge bg-warning">未驗證</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>建立時間</th>
                            <td>{{ $user->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>最後更新時間</th>
                            <td>{{ $user->updated_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>角色與權限</strong>
            </div>
            <div class="card-body">
                <h6>角色：</h6>
                <div class="mb-3">
                    @forelse($user->roles as $role)
                        <span class="badge bg-info me-1">{{ $role->name }}</span>
                    @empty
                        <span class="text-muted">無角色</span>
                    @endforelse
                </div>

                @if($user->permissions->count() > 0)
                <h6 class="mt-4">直接權限：</h6>
                <div>
                    @foreach($user->permissions as $permission)
                        <span class="badge bg-secondary me-1">{{ $permission->name }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
