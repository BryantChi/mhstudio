@extends('layouts.admin')

@section('title', '工時追蹤')

@php $breadcrumbs = [['title' => '工時追蹤', 'url' => '#']]; @endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">工時追蹤</h2>
        <p class="text-muted">記錄與管理工作時間</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.time-entries.report') }}" class="btn btn-info">
            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-chart"></use></svg> 工時報表
        </a>
    </div>
</div>

{{-- 統計卡片 --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="fs-4 fw-semibold">{{ sprintf('%d:%02d', intdiv($weekMinutes, 60), $weekMinutes % 60) }}</div>
                <div>本週工時</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="fs-4 fw-semibold">{{ sprintf('%d:%02d', intdiv($monthMinutes, 60), $monthMinutes % 60) }}</div>
                <div>本月工時</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        {{-- 計時器 --}}
        <div class="card {{ $runningEntry ? 'text-white bg-success' : 'bg-light' }}" id="timerCard">
            <div class="card-body">
                @if($runningEntry)
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-4 fw-semibold" id="timerDisplay">{{ $runningEntry->duration_formatted }}</div>
                        <div>{{ $runningEntry->description ?? '計時中...' }}</div>
                        @if($runningEntry->project) <small>{{ $runningEntry->project->title }}</small> @endif
                    </div>
                    <button class="btn btn-light btn-sm" id="stopTimer" data-id="{{ $runningEntry->id }}">
                        <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-media-stop"></use></svg> 停止
                    </button>
                </div>
                @else
                <div class="fs-4 fw-semibold">0:00</div>
                <div>無計時中</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 啟動計時器 --}}
<div class="card mb-3">
    <div class="card-header"><strong>啟動計時器 / 手動新增工時</strong></div>
    <div class="card-body">
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#timerTab">計時器</a></li>
            <li class="nav-item"><a class="nav-link" data-coreui-toggle="tab" href="#manualTab">手動輸入</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="timerTab">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">專案</label>
                        <select class="form-select form-select-sm" id="timerProject">
                            <option value="">不指定</option>
                            @foreach($projects as $p) <option value="{{ $p->id }}">{{ $p->title }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">任務</label>
                        <select class="form-select form-select-sm" id="timerTask" name="task_id">
                            <option value="">-- 選擇任務（選填）--</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">描述</label>
                        <input type="text" class="form-control form-control-sm" id="timerDescription" placeholder="正在進行的工作，例如：前端切版">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">計費</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" id="timerBillable" checked>
                            <label class="form-check-label" for="timerBillable">可計費</label>
                        </div>
                    </div>
                    <div class="col-md-auto d-flex align-items-end">
                        <button type="button" class="btn btn-success btn-sm" id="startTimerBtn" {{ $runningEntry ? 'disabled' : '' }}>
                            <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-media-play"></use></svg> 開始計時
                        </button>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="manualTab">
                <form method="POST" action="{{ route('admin.time-entries.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">專案</label>
                            <select class="form-select form-select-sm" name="project_id" id="manualProject">
                                <option value="">不指定</option>
                                @foreach($projects as $p) <option value="{{ $p->id }}">{{ $p->title }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">任務</label>
                            <select class="form-select form-select-sm" name="task_id" id="manualTask">
                                <option value="">-- 選擇任務（選填）--</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">描述</label>
                            <input type="text" class="form-control form-control-sm" name="description" placeholder="工作內容描述">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">開始時間 <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control form-control-sm" name="started_at" required value="{{ old('started_at', date('Y-m-d') . 'T09:00') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">結束時間 <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control form-control-sm" name="ended_at" required value="{{ old('ended_at', date('Y-m-d') . 'T18:00') }}">
                            <small class="text-muted">結束時間需晚於開始時間</small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">時薪（NT$）</label>
                            <input type="number" class="form-control form-control-sm" name="hourly_rate" step="1" min="0" placeholder="0">
                            <small class="text-muted">時薪（NT$），用於計算計費金額</small>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">計費</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" name="is_billable" value="1" checked>
                            </div>
                        </div>
                        <div class="col-md-auto d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm">新增</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- 篩選 --}}
<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.time-entries.index') }}" class="row g-2">
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="project_id">
                    <option value="">全部專案</option>
                    @foreach($projects as $p) <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->title }}</option> @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="user_id">
                    <option value="">全部人員</option>
                    @foreach($users as $u) <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option> @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from') }}" placeholder="開始日期">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to') }}" placeholder="結束日期">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-sm btn-secondary">篩選</button>
                <a href="{{ route('admin.time-entries.index') }}" class="btn btn-sm btn-light">清除</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($timeEntries->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>日期</th>
                        <th>人員</th>
                        <th>專案</th>
                        <th>描述</th>
                        <th>時長</th>
                        <th>計費</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeEntries as $entry)
                    <tr>
                        <td>{{ $entry->started_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $entry->user->name }}</td>
                        <td>{{ $entry->project?->title ?? '-' }}</td>
                        <td>{{ $entry->description ?? '-' }}</td>
                        <td>
                            <strong>{{ $entry->duration_formatted }}</strong>
                            @if($entry->is_running) <span class="badge bg-success">進行中</span> @endif
                        </td>
                        <td>
                            @if($entry->is_billable)
                                <span class="badge bg-success">可計費</span>
                            @else
                                <span class="badge bg-secondary">不計費</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($entry->is_running)
                            <button class="btn btn-sm btn-warning stop-entry" data-id="{{ $entry->id }}">停止</button>
                            @else
                            <form method="POST" action="{{ route('admin.time-entries.destroy', $entry) }}" class="d-inline"
                                  onsubmit="return confirm('確定要刪除嗎？');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger">
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state"><div class="empty-state-icon">⏱️</div><div>尚無工時紀錄</div></div>
        @endif
    </div>

    @if($timeEntries->hasPages())
    <div class="card-footer">{{ $timeEntries->links() }}</div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const adminPrefix = '{{ config("admin.prefix", "admin") }}';

    // 啟動計時器
    document.getElementById('startTimerBtn')?.addEventListener('click', function() {
        fetch(`/${adminPrefix}/time-entries/start`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({
                project_id: document.getElementById('timerProject').value || null,
                task_id: document.getElementById('timerTask').value || null,
                description: document.getElementById('timerDescription').value || null,
                is_billable: document.getElementById('timerBillable').checked,
            })
        }).then(r => r.json()).then(data => {
            if (data.success) { location.reload(); }
        });
    });

    // 停止計時器
    document.querySelectorAll('#stopTimer, .stop-entry').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`/${adminPrefix}/time-entries/${id}/stop`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            }).then(r => r.json()).then(data => {
                if (data.success) { location.reload(); }
            });
        });
    });

    // 專案→任務動態載入
    function loadTasksForProject(projectSelect, taskSelect) {
        const projectId = projectSelect.value;
        taskSelect.innerHTML = '<option value="">-- 選擇任務（選填）--</option>';
        if (!projectId) return;

        taskSelect.innerHTML = '<option value="">載入中...</option>';
        fetch(`/${adminPrefix}/api/projects/${projectId}/tasks`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(tasks => {
            taskSelect.innerHTML = '<option value="">-- 選擇任務（選填）--</option>';
            tasks.forEach(task => {
                const option = document.createElement('option');
                option.value = task.id;
                option.textContent = task.title;
                taskSelect.appendChild(option);
            });
        })
        .catch(() => {
            taskSelect.innerHTML = '<option value="">-- 選擇任務（選填）--</option>';
        });
    }

    // Timer tab: project → task
    const timerProject = document.getElementById('timerProject');
    const timerTask = document.getElementById('timerTask');
    if (timerProject && timerTask) {
        timerProject.addEventListener('change', () => loadTasksForProject(timerProject, timerTask));
    }

    // Manual tab: project → task
    const manualProject = document.getElementById('manualProject');
    const manualTask = document.getElementById('manualTask');
    if (manualProject && manualTask) {
        manualProject.addEventListener('change', () => loadTasksForProject(manualProject, manualTask));
    }

    // 計時器自動更新
    @if($runningEntry)
    const startTime = new Date('{{ $runningEntry->started_at->toISOString() }}');
    setInterval(() => {
        const now = new Date();
        const diff = Math.floor((now - startTime) / 1000);
        const hours = Math.floor(diff / 3600);
        const minutes = Math.floor((diff % 3600) / 60);
        const seconds = diff % 60;
        const display = document.getElementById('timerDisplay');
        if (display) display.textContent = `${hours}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }, 1000);
    @endif
});
</script>
@endpush
@endsection
