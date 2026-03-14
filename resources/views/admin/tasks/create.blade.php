@extends('layouts.admin')

@section('title', '新增任務')

@php
    $breadcrumbs = [
        ['title' => '任務管理', 'url' => route('admin.tasks.index')],
        ['title' => '新增任務', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">新增任務</h2>
        <p class="text-muted">建立新的任務</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.tasks.store') }}" onsubmit="showLoading()">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><strong>任務內容</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">任務標題 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}" required placeholder="例如：設計首頁 mockup">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">任務描述</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="4" placeholder="任務詳細說明、驗收標準等">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><strong>任務設定</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="project_id" class="form-label">所屬專案</label>
                        <select class="form-select" id="project_id" name="project_id">
                            <option value="">不指定</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id', $selectedProjectId) == $project->id ? 'selected' : '' }}>{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">指派給</label>
                        <select class="form-select" id="assigned_to" name="assigned_to">
                            <option value="">不指派</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">留空表示未指派</small>
                    </div>
                    <input type="hidden" name="status" value="todo">
                    <div class="mb-3">
                        <label for="priority" class="form-label">優先級 <span class="text-danger">*</span></label>
                        <select class="form-select" id="priority" name="priority" required>
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>低</option>
                            <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>中</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>高</option>
                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>緊急</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label">截止日期</label>
                        <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date') }}">
                        <small class="text-muted">選填，設定後看板會標記逾期</small>
                    </div>
                    <div class="mb-3">
                        <label for="estimated_hours" class="form-label">預估工時 (小時)</label>
                        <input type="number" class="form-control" id="estimated_hours" name="estimated_hours"
                               value="{{ old('estimated_hours') }}" min="0" step="0.5">
                        <small class="text-muted">預估工時（小時），例如：2.5</small>
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg> 儲存
                        </button>
                        <a href="{{ route('admin.tasks.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
