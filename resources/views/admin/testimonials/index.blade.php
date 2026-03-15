@extends('layouts.admin')

@section('title', '客戶評價')

@php
    $breadcrumbs = [
        ['title' => '客戶評價', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">客戶評價</h2>
        <p class="text-muted">管理客戶評價內容</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
            </svg>
            新增評價
        </a>
        @include('admin.partials.view-toggle', ['pageKey' => 'testimonials'])
        @include('admin.partials.sortable-mode', [
            'reorderUrl' => route('admin.testimonials.reorder'),
            'fetchUrl' => route('admin.testimonials.index', ['_sortable' => 1]),
            'itemLabel' => '評價',
            'titleField' => 'client_name',
        ])
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($testimonials->count() > 0)
        <div class="admin-list">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>客戶姓名</th>
                        <th>公司</th>
                        <th>評分</th>
                        <th>精選</th>
                        <th>狀態</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($testimonials as $testimonial)
                    <tr>
                        <td>{{ $testimonial->id }}</td>
                        <td>
                            <strong>{{ $testimonial->client_name }}</strong>
                            @if($testimonial->position)
                                <br><small class="text-muted">{{ $testimonial->position }}</small>
                            @endif
                        </td>
                        <td>{{ $testimonial->company ?? '-' }}</td>
                        <td>
                            <span class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $testimonial->rating)
                                        ★
                                    @else
                                        ☆
                                    @endif
                                @endfor
                            </span>
                        </td>
                        <td>
                            @if($testimonial->is_featured)
                                <span class="badge bg-warning">精選</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($testimonial->is_active)
                                <span class="badge bg-success">啟用</span>
                            @else
                                <span class="badge bg-secondary">停用</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.testimonials.edit', $testimonial) }}"
                                   class="btn btn-sm btn-light"
                                   data-coreui-toggle="tooltip"
                                   title="編輯">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                                    </svg>
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.testimonials.destroy', $testimonial) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('確定要刪除此評價嗎？');">
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
            @foreach($testimonials as $testimonial)
            <div class="admin-grid-card{{ $testimonial->is_featured ? ' featured' : '' }}">
                <div class="admin-grid-card-header">
                    <span class="text-warning">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $testimonial->rating)
                                ★
                            @else
                                ☆
                            @endif
                        @endfor
                    </span>
                    @if($testimonial->is_featured)
                        <span class="badge bg-warning">精選</span>
                    @endif
                </div>
                <div class="admin-grid-card-body">
                    <h6>{{ $testimonial->client_name }}</h6>
                    <div class="admin-grid-card-subtitle">
                        @if($testimonial->position || $testimonial->company)
                            {{ $testimonial->position }}{{ $testimonial->position && $testimonial->company ? ' / ' : '' }}{{ $testimonial->company }}
                        @else
                            -
                        @endif
                    </div>
                    <dl class="admin-grid-card-meta">
                        <dt>狀態</dt>
                        <dd>
                            @if($testimonial->is_active)
                                <span class="badge bg-success">啟用</span>
                            @else
                                <span class="badge bg-secondary">停用</span>
                            @endif
                        </dd>
                    </dl>
                </div>
                <div class="admin-grid-card-footer">
                    <a href="{{ route('admin.testimonials.edit', $testimonial) }}"
                       class="btn btn-sm btn-light"
                       data-coreui-toggle="tooltip"
                       title="編輯">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                        </svg>
                    </a>

                    <form method="POST"
                          action="{{ route('admin.testimonials.destroy', $testimonial) }}"
                          class="d-inline"
                          onsubmit="return confirm('確定要刪除此評價嗎？');">
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
            <div class="empty-state-icon">⭐</div>
            <div>尚無客戶評價</div>
            <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary mt-3">新增第一則評價</a>
        </div>
        @endif
    </div>

    @if($testimonials->hasPages())
    <div class="card-footer">
        {{ $testimonials->links() }}
    </div>
    @endif
</div>
@endsection
