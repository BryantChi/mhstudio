@extends('layouts.admin')

@section('title', '客戶專案管理 - ' . $project->title)

@php
    $breadcrumbs = [
        ['title' => '作品集管理', 'url' => route('admin.projects.index')],
        ['title' => $project->title, 'url' => route('admin.projects.show', $project)],
        ['title' => '客戶管理', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">客戶專案管理</h2>
        <p class="text-muted">管理「{{ $project->title }}」的客戶存取權限</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-light">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-arrow-left"></use>
            </svg>
            返回作品詳情
        </a>
    </div>
</div>

{{-- 目前客戶列表 --}}
<div class="card mb-4">
    <div class="card-header">
        <strong>目前客戶</strong>
        <span class="badge bg-info ms-2">{{ $project->clients->count() }} 位</span>
    </div>
    <div class="card-body">
        @if($project->clients->isEmpty())
            <div class="text-center text-muted py-4">
                <p>尚未指派任何客戶</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>用戶</th>
                            <th>Email</th>
                            <th>角色</th>
                            <th>指派時間</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->clients as $client)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $client->avatar }}" alt="{{ $client->name }}" class="rounded-circle me-2" width="32" height="32">
                                    <strong>{{ $client->name }}</strong>
                                </div>
                            </td>
                            <td>{{ $client->email }}</td>
                            <td>
                                <span class="badge bg-{{ $client->pivot->role === 'owner' ? 'primary' : 'secondary' }}">
                                    {{ $client->pivot->role === 'owner' ? '擁有者' : '檢視者' }}
                                </span>
                            </td>
                            <td>{{ $client->pivot->created_at ? \Carbon\Carbon::parse($client->pivot->created_at)->format('Y-m-d H:i') : '-' }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.projects.clients.update', $project) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('確定要移除此客戶的存取權限嗎？');">
                                    @csrf
                                    @method('PUT')
                                    {{-- 傳送所有客戶但排除目前這位 --}}
                                    @foreach($project->clients as $otherClient)
                                        @if($otherClient->id !== $client->id)
                                            <input type="hidden" name="clients[{{ $loop->index }}][user_id]" value="{{ $otherClient->id }}">
                                            <input type="hidden" name="clients[{{ $loop->index }}][role]" value="{{ $otherClient->pivot->role }}">
                                        @endif
                                    @endforeach
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <svg class="icon">
                                            <use xlink:href="/assets/icons/free.svg#cil-x-circle"></use>
                                        </svg>
                                        移除
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- 新增客戶 --}}
<div class="card">
    <div class="card-header">
        <strong>新增客戶</strong>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.projects.clients.update', $project) }}" id="addClientForm">
            @csrf
            @method('PUT')

            {{-- 保留現有客戶 --}}
            @foreach($project->clients as $existingClient)
                <input type="hidden" name="clients[{{ $loop->index }}][user_id]" value="{{ $existingClient->id }}">
                <input type="hidden" name="clients[{{ $loop->index }}][role]" value="{{ $existingClient->pivot->role }}">
            @endforeach

            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="new_user_id" class="form-label">選擇用戶</label>
                    <select class="form-select" name="clients[{{ $project->clients->count() }}][user_id]" id="new_user_id" required>
                        <option value="">請選擇用戶...</option>
                        @foreach($users as $user)
                            @unless($project->clients->contains('id', $user->id))
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endunless
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="new_role" class="form-label">角色</label>
                    <select class="form-select" name="clients[{{ $project->clients->count() }}][role]" id="new_role">
                        <option value="owner">擁有者</option>
                        <option value="viewer">檢視者</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-user-plus"></use>
                        </svg>
                        新增客戶
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
