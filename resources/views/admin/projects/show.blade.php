@extends('layouts.admin')

@section('title', '作品詳情')

@php
    $breadcrumbs = [
        ['title' => '作品集管理', 'url' => route('admin.projects.index')],
        ['title' => '作品詳情', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">作品詳情</h2>
        <p class="text-muted">查看作品資訊</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.projects.clients', $project) }}" class="btn btn-info">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-people"></use>
            </svg>
            客戶管理
        </a>

        <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-primary">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
            </svg>
            編輯
        </a>

        <a href="{{ route('admin.projects.index') }}" class="btn btn-light">返回列表</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>作品內容</strong>
                <div>
                    <span class="badge bg-{{ $project->status_color }}">{{ $project->status_label }}</span>
                    @if($project->is_featured)
                        <span class="badge bg-warning ms-2">精選</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                {{-- 圖片庫 Grid --}}
                @if($project->images->isNotEmpty())
                <div class="mb-4">
                    <div class="row g-2">
                        @foreach($project->images as $image)
                        <div class="col-4 col-md-3">
                            <div class="position-relative border rounded overflow-hidden" style="aspect-ratio: 4/3;">
                                <img src="{{ $image->image_url }}"
                                     alt="{{ $image->alt_text ?? $project->title }}"
                                     class="w-100 h-100"
                                     style="object-fit: cover;">
                                @if($loop->first)
                                <span class="badge bg-primary position-absolute top-0 start-0 m-1" style="font-size: 0.65rem;">封面</span>
                                @endif
                            </div>
                            @if($image->caption)
                            <small class="text-muted d-block mt-1">{{ $image->caption }}</small>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @elseif($project->getRawOriginal('cover_image'))
                <div class="mb-4">
                    <img src="{{ $project->getRawOriginal('cover_image') }}"
                         class="img-fluid rounded"
                         alt="{{ $project->title }}">
                </div>
                @endif

                <h3 class="mb-3">{{ $project->title }}</h3>

                @if($project->excerpt)
                <div class="alert alert-light border mb-3">
                    <strong>摘要：</strong>{{ $project->excerpt }}
                </div>
                @endif

                <div class="content-preview">
                    {!! nl2br(e($project->content)) !!}
                </div>
            </div>
        </div>

        @if($project->tech_stack)
        <div class="card mt-3">
            <div class="card-header">
                <strong>技術棧</strong>
            </div>
            <div class="card-body">
                @foreach($project->tech_stack as $tech)
                    <span class="badge bg-info me-1 mb-1 fs-6">{{ $tech }}</span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ===== 里程碑 ===== --}}
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>里程碑</strong>
                <span class="text-muted">
                    進度：{{ $project->progress_percentage }}%
                    ({{ $project->milestones->where('status', 'completed')->count() }}/{{ $project->milestones->count() }})
                </span>
            </div>
            <div class="card-body">
                {{-- 進度條 --}}
                @if($project->milestones->isNotEmpty())
                <div class="progress mb-4" style="height: 8px;">
                    <div class="progress-bar bg-success" role="progressbar"
                         style="width: {{ $project->progress_percentage }}%"
                         aria-valuenow="{{ $project->progress_percentage }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                @endif

                {{-- 里程碑列表 --}}
                @if($project->milestones->isNotEmpty())
                <div class="table-responsive mb-4">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="40">排序</th>
                                <th>標題</th>
                                <th>狀態</th>
                                <th>截止日期</th>
                                <th>完成時間</th>
                                <th class="text-end">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($project->milestones as $milestone)
                            <tr>
                                <td>{{ $milestone->order }}</td>
                                <td>
                                    <strong>{{ $milestone->title }}</strong>
                                    @if($milestone->description)
                                        <br><small class="text-muted">{{ Str::limit($milestone->description, 80) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $milestone->status_color }}">
                                        {{ $milestone->status_label }}
                                    </span>
                                    @if($milestone->is_overdue)
                                        <span class="badge bg-danger ms-1">已逾期</span>
                                    @endif
                                </td>
                                <td>{{ $milestone->due_date ? $milestone->due_date->format('Y-m-d') : '-' }}</td>
                                <td>{{ $milestone->completed_at ? $milestone->completed_at->format('Y-m-d H:i') : '-' }}</td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        @if($milestone->status !== 'completed')
                                        <form method="POST" action="{{ route('admin.projects.milestones.update', $milestone) }}" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            @if($milestone->status === 'pending')
                                                <input type="hidden" name="status" value="in_progress">
                                                <button type="submit" class="btn btn-sm btn-outline-info" data-coreui-toggle="tooltip" title="開始進行">
                                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-media-play"></use></svg>
                                                </button>
                                            @else
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn btn-sm btn-outline-success" data-coreui-toggle="tooltip" title="標記完成">
                                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-check-circle"></use></svg>
                                                </button>
                                            @endif
                                        </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.projects.milestones.destroy', $milestone) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('確定要刪除此里程碑嗎？');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" data-coreui-toggle="tooltip" title="刪除">
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
                @else
                <p class="text-muted text-center mb-4">尚無里程碑</p>
                @endif

                {{-- 新增里程碑表單 --}}
                <div class="border rounded p-3 bg-light">
                    <h6 class="mb-3">新增里程碑</h6>
                    <form method="POST" action="{{ route('admin.projects.milestones.store', $project) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">標題 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" required placeholder="里程碑名稱">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">狀態</label>
                                <select class="form-select" name="status">
                                    <option value="pending">待開始</option>
                                    <option value="in_progress">進行中</option>
                                    <option value="completed">已完成</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">截止日期</label>
                                <input type="date" class="form-control" name="due_date">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">排序</label>
                                <input type="number" class="form-control" name="order" value="{{ ($project->milestones->max('order') ?? 0) + 1 }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">描述</label>
                                <textarea class="form-control" name="description" rows="2" placeholder="選填描述..."></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg>
                                    新增里程碑
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ===== 專案檔案 ===== --}}
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>專案檔案</strong>
                <span class="badge bg-secondary">{{ $project->files->count() }} 個檔案</span>
            </div>
            <div class="card-body">
                @if($project->files->isNotEmpty())
                <div class="table-responsive mb-4">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>檔案名稱</th>
                                <th>大小</th>
                                <th>類型</th>
                                <th>上傳者</th>
                                <th>上傳時間</th>
                                <th>說明</th>
                                <th class="text-end">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($project->files as $file)
                            <tr>
                                <td>
                                    <svg class="icon me-1">
                                        <use xlink:href="/assets/icons/free.svg#{{ $file->icon }}"></use>
                                    </svg>
                                    {{ $file->original_name }}
                                </td>
                                <td>{{ $file->human_size }}</td>
                                <td><span class="badge bg-light text-dark">{{ $file->mime_type }}</span></td>
                                <td>{{ $file->uploader?->name ?? '-' }}</td>
                                <td>{{ $file->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $file->description ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ $file->url }}" target="_blank" class="btn btn-sm btn-outline-primary" data-coreui-toggle="tooltip" title="預覽/下載">
                                            <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-cloud-download"></use></svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.projects.files.destroy', $file) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('確定要刪除此檔案嗎？');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" data-coreui-toggle="tooltip" title="刪除">
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
                @else
                <p class="text-muted text-center mb-4">尚無檔案</p>
                @endif

                {{-- 上傳檔案表單 --}}
                <div class="border rounded p-3 bg-light">
                    <h6 class="mb-3">上傳檔案</h6>
                    <form method="POST" action="{{ route('admin.projects.files.store', $project) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">選擇檔案 <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="file" required>
                                <div class="form-text">最大 50MB</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">說明</label>
                                <input type="text" class="form-control" name="description" placeholder="檔案說明（選填）">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-cloud-upload"></use></svg>
                                    上傳檔案
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ===== 留言 ===== --}}
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>留言與備註</strong>
                <span class="badge bg-secondary">{{ $project->comments->count() }} 則</span>
            </div>
            <div class="card-body">
                {{-- 新增留言表單 --}}
                <div class="border rounded p-3 bg-light mb-4">
                    <h6 class="mb-3">新增留言</h6>
                    <form method="POST" action="{{ route('admin.projects.comments.store', $project) }}">
                        @csrf
                        <div class="mb-3">
                            <textarea class="form-control" name="content" rows="3" required placeholder="輸入留言內容..."></textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_internal" value="1" id="isInternal">
                                <label class="form-check-label" for="isInternal">
                                    <span class="badge bg-warning text-dark">內部備註</span>
                                    <small class="text-muted ms-1">（客戶不可見）</small>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-send"></use></svg>
                                送出留言
                            </button>
                        </div>
                    </form>
                </div>

                {{-- 留言列表 --}}
                @if($project->comments->isNotEmpty())
                <div class="list-group">
                    @foreach($project->comments as $comment)
                    <div class="list-group-item {{ $comment->is_internal ? 'border-start border-warning border-3' : '' }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <img src="{{ $comment->user->avatar }}" alt="{{ $comment->user->name }}" class="rounded-circle me-2" width="28" height="28">
                                <strong>{{ $comment->user->name }}</strong>
                                @if($comment->is_internal)
                                    <span class="badge bg-warning text-dark ms-2">內部備註</span>
                                @endif
                            </div>
                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="ps-5" style="white-space: pre-line;">{{ $comment->content }}</div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted text-center">尚無留言</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <strong>基本資訊</strong>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <th width="100">作品 ID</th>
                            <td>{{ $project->id }}</td>
                        </tr>
                        <tr>
                            <th>狀態</th>
                            <td>
                                <span class="badge bg-{{ $project->status_color }}">
                                    {{ $project->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>分類</th>
                            <td>
                                @if($project->category)
                                    <span class="badge bg-secondary">{{ $project->category }}</span>
                                @else
                                    <span class="text-muted">未分類</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>客戶</th>
                            <td>{{ $project->client ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>網址</th>
                            <td>
                                @if($project->url)
                                    <a href="{{ $project->url }}" target="_blank" class="text-break">
                                        {{ $project->url }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>GitHub</th>
                            <td>
                                @if($project->github_url)
                                    <a href="{{ $project->github_url }}" target="_blank" class="text-break">
                                        {{ $project->github_url }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Slug</th>
                            <td>
                                <a href="{{ url('projects/' . $project->slug) }}" target="_blank" class="text-break">
                                    {{ $project->slug }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>完成日期</th>
                            <td>
                                @if($project->completed_at)
                                    {{ $project->completed_at->format('Y-m-d') }}
                                @else
                                    <span class="text-muted">未設定</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>排序</th>
                            <td>{{ $project->order }}</td>
                        </tr>
                        <tr>
                            <th>建立時間</th>
                            <td>{{ $project->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        <tr>
                            <th>最後更新</th>
                            <td>{{ $project->updated_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>設定</strong>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <svg class="icon me-2 {{ $project->is_featured ? 'text-warning' : 'text-muted' }}">
                        <use xlink:href="/assets/icons/free.svg#cil-star"></use>
                    </svg>
                    精選作品：
                    <strong>{{ $project->is_featured ? '是' : '否' }}</strong>
                </div>
            </div>
        </div>

        {{-- 客戶存取 --}}
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>客戶存取</strong>
                <a href="{{ route('admin.projects.clients', $project) }}" class="btn btn-sm btn-outline-primary">管理</a>
            </div>
            <div class="card-body">
                @if($project->clients && $project->clients->isNotEmpty())
                    @foreach($project->clients as $client)
                    <div class="d-flex align-items-center mb-2">
                        <img src="{{ $client->avatar }}" alt="{{ $client->name }}" class="rounded-circle me-2" width="24" height="24">
                        <span>{{ $client->name }}</span>
                        <span class="badge bg-{{ $client->pivot->role === 'owner' ? 'primary' : 'secondary' }} ms-auto">
                            {{ $client->pivot->role === 'owner' ? '擁有者' : '檢視者' }}
                        </span>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted text-center mb-0">尚未指派客戶</p>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <form method="POST"
                      action="{{ route('admin.projects.destroy', $project) }}"
                      onsubmit="return confirm('確定要刪除此作品嗎？此操作無法復原。');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                        </svg>
                        刪除作品
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .content-preview {
        line-height: 1.8;
        font-size: 1.1rem;
        color: #333;
    }
</style>
@endpush
@endsection
