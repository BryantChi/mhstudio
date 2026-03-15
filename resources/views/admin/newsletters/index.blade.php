@extends('layouts.admin')

@section('title', '電子報管理')

@php
    $breadcrumbs = [
        ['title' => '電子報管理', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">電子報管理</h2>
        <p class="text-muted">管理電子報內容與發送紀錄</p>
    </div>
    <div class="col-md-6 text-md-end">
        @include('admin.partials.view-toggle', ['pageKey' => 'newsletters'])
        <a href="{{ route('admin.newsletters.create') }}" class="btn btn-primary">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
            </svg>
            建立電子報
        </a>
    </div>
</div>

{{-- 狀態篩選 --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.newsletters.index') }}" class="row g-3 align-items-center">
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                    <option value="">全部狀態</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>草稿</option>
                    <option value="sending" {{ request('status') == 'sending' ? 'selected' : '' }}>發送中</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>已發送</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>失敗</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>排程中</option>
                </select>
            </div>
            <div class="col-md-auto">
                @if(request('status'))
                <a href="{{ route('admin.newsletters.index') }}" class="btn btn-sm btn-light">清除</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($newsletters->count() > 0)
        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>主旨</th>
                        <th>狀態</th>
                        <th>收件人數</th>
                        <th>成功 / 失敗</th>
                        <th>建立者</th>
                        <th>建立時間</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($newsletters as $newsletter)
                    <tr>
                        <td>{{ $newsletter->id }}</td>
                        <td>
                            <a href="{{ $newsletter->status === 'draft' ? route('admin.newsletters.edit', $newsletter) : route('admin.newsletters.show', $newsletter) }}">
                                {{ $newsletter->subject }}
                            </a>
                        </td>
                        <td>
                            @switch($newsletter->status)
                                @case('draft')
                                    <span class="badge bg-secondary">草稿</span>
                                    @break
                                @case('sending')
                                    <span class="badge bg-info">發送中</span>
                                    @break
                                @case('sent')
                                    <span class="badge bg-success">已發送</span>
                                    @break
                                @case('failed')
                                    <span class="badge bg-danger">失敗</span>
                                    @break
                                @case('scheduled')
                                    <span class="badge bg-warning text-dark">排程中</span>
                                    @break
                            @endswitch
                        </td>
                        <td>{{ number_format($newsletter->total_recipients) }}</td>
                        <td>
                            @if($newsletter->status !== 'draft')
                                <span class="text-success">{{ number_format($newsletter->sent_count) }}</span>
                                /
                                <span class="text-danger">{{ number_format($newsletter->failed_count) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $newsletter->creator->name ?? '-' }}</td>
                        <td>{{ $newsletter->created_at->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                @if($newsletter->status === 'draft')
                                <a href="{{ route('admin.newsletters.edit', $newsletter) }}"
                                   class="btn btn-sm btn-light"
                                   data-coreui-toggle="tooltip"
                                   title="編輯">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                                    </svg>
                                </a>
                                @endif

                                @if($newsletter->status !== 'draft')
                                <a href="{{ route('admin.newsletters.show', $newsletter) }}"
                                   class="btn btn-sm btn-light"
                                   data-coreui-toggle="tooltip"
                                   title="查看報告">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-chart-line"></use>
                                    </svg>
                                </a>
                                @endif

                                <a href="{{ route('admin.newsletters.preview', $newsletter) }}"
                                   class="btn btn-sm btn-light"
                                   target="_blank"
                                   data-coreui-toggle="tooltip"
                                   title="預覽">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-external-link"></use>
                                    </svg>
                                </a>

                                @if($newsletter->status === 'draft')
                                <form method="POST"
                                      action="{{ route('admin.newsletters.destroy', $newsletter) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('確定要刪除此電子報嗎？');">
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
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>{{-- /.admin-list --}}

        <div class="admin-grid">
            @foreach($newsletters as $newsletter)
            <div class="admin-grid-card">
                <div class="admin-grid-card-header">
                    @switch($newsletter->status)
                        @case('draft')
                            <span class="badge bg-secondary">草稿</span>
                            @break
                        @case('sending')
                            <span class="badge bg-info">發送中</span>
                            @break
                        @case('sent')
                            <span class="badge bg-success">已發送</span>
                            @break
                        @case('failed')
                            <span class="badge bg-danger">失敗</span>
                            @break
                        @case('scheduled')
                            <span class="badge bg-warning text-dark">排程中</span>
                            @break
                    @endswitch
                    <span class="text-muted small">{{ number_format($newsletter->total_recipients) }} 收件人</span>
                </div>
                <div class="admin-grid-card-body">
                    <h6>
                        <a href="{{ $newsletter->status === 'draft' ? route('admin.newsletters.edit', $newsletter) : route('admin.newsletters.show', $newsletter) }}">
                            {{ $newsletter->subject }}
                        </a>
                    </h6>
                    <div class="admin-grid-card-subtitle">{{ $newsletter->creator->name ?? '-' }}</div>
                    <dl class="admin-grid-card-meta">
                        @if($newsletter->status !== 'draft')
                        <dt>成功</dt>
                        <dd class="text-success">{{ number_format($newsletter->sent_count) }}</dd>
                        <dt>失敗</dt>
                        <dd class="text-danger">{{ number_format($newsletter->failed_count) }}</dd>
                        @endif
                        <dt>建立</dt>
                        <dd>{{ $newsletter->created_at->format('Y-m-d H:i') }}</dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    <a href="{{ route('admin.newsletters.preview', $newsletter) }}"
                       class="btn btn-sm btn-light"
                       target="_blank"
                       data-coreui-toggle="tooltip"
                       title="預覽">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-external-link"></use>
                        </svg>
                    </a>

                    @if($newsletter->status === 'draft')
                    <a href="{{ route('admin.newsletters.edit', $newsletter) }}"
                       class="btn btn-sm btn-light"
                       data-coreui-toggle="tooltip"
                       title="編輯">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                        </svg>
                    </a>
                    @endif

                    @if($newsletter->status !== 'draft')
                    <a href="{{ route('admin.newsletters.show', $newsletter) }}"
                       class="btn btn-sm btn-light"
                       data-coreui-toggle="tooltip"
                       title="查看報告">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-chart-line"></use>
                        </svg>
                    </a>
                    @endif

                    @if($newsletter->status === 'draft')
                    <form method="POST"
                          action="{{ route('admin.newsletters.destroy', $newsletter) }}"
                          class="d-inline"
                          onsubmit="return confirm('確定要刪除此電子報嗎？');">
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
                </div>
            </div>
            @endforeach
        </div>{{-- /.admin-grid --}}

        @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg class="icon icon-xl">
                    <use xlink:href="/assets/icons/free.svg#cil-send"></use>
                </svg>
            </div>
            <div>尚無電子報</div>
            <a href="{{ route('admin.newsletters.create') }}" class="btn btn-sm btn-primary mt-2">建立第一封電子報</a>
        </div>
        @endif
    </div>

    @if($newsletters->hasPages())
    <div class="card-footer">
        {{ $newsletters->links() }}
    </div>
    @endif
</div>
@endsection
