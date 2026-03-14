@extends('layouts.admin')

@section('title', '任務管理')

@php $breadcrumbs = [['title' => '任務管理', 'url' => '#']]; @endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">任務管理</h2>
        <p class="text-muted">管理專案任務與進度</p>
    </div>
    <div class="col-md-6 text-md-end">
        <div class="btn-group me-2">
            <a href="{{ route('admin.tasks.index', array_merge(request()->except('view'), ['view' => 'board'])) }}"
               class="btn btn-outline-secondary {{ $view === 'board' ? 'active' : '' }}">
                <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-view-column"></use></svg> 看板
            </a>
            <a href="{{ route('admin.tasks.index', array_merge(request()->except('view'), ['view' => 'list'])) }}"
               class="btn btn-outline-secondary {{ $view === 'list' ? 'active' : '' }}">
                <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-list"></use></svg> 列表
            </a>
        </div>
        <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary">
            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg> 新增任務
        </a>
    </div>
</div>

{{-- 篩選 --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.tasks.index') }}" class="row g-2 align-items-center">
            <input type="hidden" name="view" value="{{ $view }}">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="search" placeholder="搜尋任務" value="{{ request('search') }}" data-search>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="project_id">
                    <option value="">全部專案</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>{{ $project->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="assigned_to">
                    <option value="">全部人員</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="priority">
                    <option value="">全部優先級</option>
                    @foreach(['low' => '低', 'medium' => '中', 'high' => '高', 'urgent' => '緊急'] as $val => $label)
                        <option value="{{ $val }}" {{ request('priority') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-sm btn-secondary">篩選</button>
                <a href="{{ route('admin.tasks.index', ['view' => $view]) }}" class="btn btn-sm btn-light">清除</a>
            </div>
        </form>
    </div>
</div>

@if($view === 'board')
{{-- 看板視圖 --}}
<div class="row" id="taskBoard">
    @foreach([
        ['status' => 'todo', 'title' => '待辦', 'color' => 'secondary', 'tasks' => $todoTasks],
        ['status' => 'in_progress', 'title' => '進行中', 'color' => 'primary', 'tasks' => $inProgressTasks],
        ['status' => 'in_review', 'title' => '審核中', 'color' => 'info', 'tasks' => $inReviewTasks],
        ['status' => 'completed', 'title' => '已完成', 'color' => 'success', 'tasks' => $completedTasks],
    ] as $column)
    <div class="col-md-3">
        <div class="card">
            <div class="card-header bg-{{ $column['color'] }} text-white d-flex justify-content-between">
                <strong>{{ $column['title'] }}</strong>
                <span class="badge bg-white text-{{ $column['color'] }}">{{ $column['tasks']->count() }}</span>
            </div>
            <div class="card-body p-2 task-column" data-status="{{ $column['status'] }}" style="min-height: 200px;">
                @foreach($column['tasks'] as $task)
                <div class="card mb-2 task-card" data-task-id="{{ $task->id }}" draggable="true">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <a href="{{ route('admin.tasks.edit', $task) }}" class="text-decoration-none fw-semibold small">
                                {{ $task->title }}
                            </a>
                            <span class="badge bg-{{ $task->priority_color }}">{{ $task->priority_label }}</span>
                        </div>
                        @if($task->project)
                        <small class="text-muted d-block">{{ $task->project->title }}</small>
                        @endif
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">
                                @if($task->assignee) {{ $task->assignee->name }} @endif
                            </small>
                            @if($task->due_date)
                            <small class="{{ $task->is_overdue ? 'text-danger fw-bold' : 'text-muted' }}">
                                {{ $task->due_date->format('m/d') }}
                            </small>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
{{-- 列表視圖 --}}
<div class="card">
    <div class="card-body p-0">
        @if($tasks->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>標題</th>
                        <th>專案</th>
                        <th>指派給</th>
                        <th>優先級</th>
                        <th>狀態</th>
                        <th>截止日</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                    <tr class="{{ $task->is_overdue ? 'table-danger' : '' }}">
                        <td>{{ $task->id }}</td>
                        <td>
                            <a href="{{ route('admin.tasks.edit', $task) }}">{{ $task->title }}</a>
                            @if($task->is_overdue) <span class="badge bg-danger ms-1">逾期</span> @endif
                        </td>
                        <td>{{ $task->project?->title ?? '-' }}</td>
                        <td>{{ $task->assignee?->name ?? '-' }}</td>
                        <td><span class="badge bg-{{ $task->priority_color }}">{{ $task->priority_label }}</span></td>
                        <td><span class="badge bg-{{ $task->status_color }}">{{ $task->status_label }}</span></td>
                        <td>{{ $task->due_date?->format('Y-m-d') ?? '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-sm btn-light" data-coreui-toggle="tooltip" title="編輯">
                                <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-pencil"></use></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.tasks.destroy', $task) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger" data-coreui-toggle="tooltip" title="刪除" data-confirm-delete>
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state"><div class="empty-state-icon">✅</div><div>尚無任務</div></div>
        @endif
    </div>
    @if($tasks && $tasks->hasPages())
    <div class="card-footer">{{ $tasks->links() }}</div>
    @endif
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 看板拖曳功能
    let draggedTask = null;

    document.querySelectorAll('.task-card').forEach(card => {
        card.addEventListener('dragstart', function(e) {
            draggedTask = this;
            this.style.opacity = '0.5';
            e.dataTransfer.effectAllowed = 'move';
        });
        card.addEventListener('dragend', function() {
            this.style.opacity = '1';
            draggedTask = null;
            document.querySelectorAll('.task-column').forEach(col => col.classList.remove('bg-light'));
        });
    });

    document.querySelectorAll('.task-column').forEach(column => {
        column.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('bg-light');
        });
        column.addEventListener('dragleave', function() {
            this.classList.remove('bg-light');
        });
        column.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('bg-light');
            if (draggedTask) {
                this.appendChild(draggedTask);
                const taskId = draggedTask.dataset.taskId;
                const newStatus = this.dataset.status;
                // AJAX update
                fetch(`/{{ config('admin.prefix', 'admin') }}/tasks/${taskId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ status: newStatus })
                }).then(r => r.json()).then(data => {
                    if (data.success && window.showToast) {
                        window.showToast(data.message, 'success');
                    }
                    // Update column counts
                    document.querySelectorAll('.task-column').forEach(col => {
                        const count = col.querySelectorAll('.task-card').length;
                        col.closest('.card').querySelector('.badge').textContent = count;
                    });
                }).catch(() => {
                    if (window.showToast) {
                        window.showToast('狀態更新失敗，請重新整理頁面再試', 'danger');
                    }
                });
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
    .task-column { transition: background-color 0.2s; }
    .task-card { cursor: grab; transition: opacity 0.2s; }
    .task-card:active { cursor: grabbing; }
</style>
@endpush
@endsection
