@extends('layouts.admin')

@section('title', '訊息詳情')

@php
    $breadcrumbs = [
        ['title' => '聯繫訊息', 'url' => route('admin.contact-messages.index')],
        ['title' => '訊息詳情', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">訊息詳情</h2>
        <p class="text-muted">查看聯繫訊息內容</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-light">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-arrow-left"></use>
            </svg>
            返回列表
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>訊息內容</strong>
                <span class="badge bg-{{ $message->status_color }}">{{ $message->status_label }}</span>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <th width="150">姓名</th>
                            <td>{{ $message->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>
                                <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                            </td>
                        </tr>
                        <tr>
                            <th>專案類型</th>
                            <td>{{ $message->project_type ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>送出時間</th>
                            <td>{{ $message->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @if($message->read_at)
                        <tr>
                            <th>已讀時間</th>
                            <td>{{ $message->read_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @endif
                        @if($message->replied_at)
                        <tr>
                            <th>回覆時間</th>
                            <td>{{ $message->replied_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>

                <hr>

                <h6>訊息內容：</h6>
                <div class="content-preview p-3 bg-light rounded">
                    {!! nl2br(e($message->message)) !!}
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>狀態更新</strong>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.contact-messages.update', $message) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="status" class="form-label">狀態</label>
                        <select class="form-select @error('status') is-invalid @enderror"
                                id="status"
                                name="status">
                            <option value="unread" {{ old('status', $message->status) == 'unread' ? 'selected' : '' }}>未讀</option>
                            <option value="read" {{ old('status', $message->status) == 'read' ? 'selected' : '' }}>已讀</option>
                            <option value="replied" {{ old('status', $message->status) == 'replied' ? 'selected' : '' }}>已回覆</option>
                            <option value="archived" {{ old('status', $message->status) == 'archived' ? 'selected' : '' }}>已封存</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="admin_notes" class="form-label">管理員備註</label>
                        <textarea class="form-control @error('admin_notes') is-invalid @enderror"
                                  id="admin_notes"
                                  name="admin_notes"
                                  rows="4">{{ old('admin_notes', $message->admin_notes) }}</textarea>
                        @error('admin_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-save"></use>
                        </svg>
                        儲存
                    </button>
                </form>
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
                            <th width="100">訊息 ID</th>
                            <td>{{ $message->id }}</td>
                        </tr>
                        <tr>
                            <th>狀態</th>
                            <td>
                                <span class="badge bg-{{ $message->status_color }}">
                                    {{ $message->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>送出時間</th>
                            <td>{{ $message->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @if($message->read_at)
                        <tr>
                            <th>已讀時間</th>
                            <td>{{ $message->read_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @endif
                        @if($message->replied_at)
                        <tr>
                            <th>回覆時間</th>
                            <td>{{ $message->replied_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        @if($message->admin_notes)
        <div class="card mt-3">
            <div class="card-header">
                <strong>管理員備註</strong>
            </div>
            <div class="card-body">
                {!! nl2br(e($message->admin_notes)) !!}
            </div>
        </div>
        @endif

        <div class="card mt-3">
            <div class="card-body">
                <form method="POST"
                      action="{{ route('admin.contact-messages.destroy', $message) }}"
                      onsubmit="return confirm('確定要刪除此訊息嗎？此操作無法復原。');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                        </svg>
                        刪除訊息
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
