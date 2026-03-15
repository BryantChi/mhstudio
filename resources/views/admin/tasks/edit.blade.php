@extends('layouts.admin')

@section('title', '編輯任務')

@php
    $breadcrumbs = [
        ['title' => '任務管理', 'url' => route('admin.tasks.index')],
        ['title' => '編輯任務', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">編輯任務</h2>
        <p class="text-muted">修改任務內容</p>
    </div>
    <div class="col-md-6 text-md-end">
        <form method="POST" action="{{ route('admin.tasks.destroy', $task) }}" class="d-inline"
              onsubmit="return confirm('確定要刪除嗎？');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg> 刪除
            </button>
        </form>
    </div>
</div>

<form method="POST" action="{{ route('admin.tasks.update', $task) }}" onsubmit="showLoading()">
    @csrf @method('PUT')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><strong>任務內容</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">任務標題 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title', $task->title) }}" required placeholder="例如：設計首頁 mockup">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">任務描述</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="4" placeholder="任務詳細說明、驗收標準等">{{ old('description', $task->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            @if($task->timeEntries->isNotEmpty())
            <div class="card mt-3">
                <div class="card-header"><strong>工時紀錄</strong></div>
                <div class="card-body">
                    <p class="text-muted">預估工時：{{ $task->estimated_hours ?? '-' }} 小時 | 實際工時：{{ $task->actual_hours }} 小時</p>
                </div>
            </div>
            @endif
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><strong>任務設定</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">所屬專案</label>
                        <select class="form-select" name="project_id">
                            <option value="">不指定</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id', $task->project_id) == $project->id ? 'selected' : '' }}>{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">指派給</label>
                        <select class="form-select" name="assigned_to">
                            <option value="">不指派</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">留空表示未指派</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">狀態 <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" required>
                            @foreach(['todo' => '待辦', 'in_progress' => '進行中', 'in_review' => '審核中', 'completed' => '已完成'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $task->status) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">優先級 <span class="text-danger">*</span></label>
                        <select class="form-select" name="priority" required>
                            @foreach(['low' => '低', 'medium' => '中', 'high' => '高', 'urgent' => '緊急'] as $val => $label)
                                <option value="{{ $val }}" {{ old('priority', $task->priority) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">截止日期</label>
                        <input type="date" class="form-control" name="due_date"
                               value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
                        <small class="text-muted">選填，設定後看板會標記逾期</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">預估工時 (小時)</label>
                        <input type="number" class="form-control" name="estimated_hours"
                               value="{{ old('estimated_hours', $task->estimated_hours) }}" min="0" step="0.5">
                        <small class="text-muted">預估工時（小時），例如：2.5</small>
                    </div>
                    @if($task->completed_at)
                    <div class="mb-3">
                        <label class="form-label">完成時間</label>
                        <input type="datetime-local" class="form-control" name="completed_at"
                               value="{{ old('completed_at', $task->completed_at->format('Y-m-d\TH:i')) }}">
                    </div>
                    @endif
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg> 更新
                        </button>
                        <a href="{{ route('admin.tasks.index') }}" class="btn btn-light">返回</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
