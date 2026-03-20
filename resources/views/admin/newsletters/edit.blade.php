@extends('layouts.admin')

@section('title', '編輯電子報')

@php
    $breadcrumbs = [
        ['title' => '電子報管理', 'url' => route('admin.newsletters.index')],
        ['title' => '編輯電子報', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">編輯電子報</h2>
        <p class="text-muted">修改電子報內容</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.newsletters.preview', $newsletter) }}" class="btn btn-light" target="_blank">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-external-link"></use>
            </svg>
            預覽
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.newsletters.update', $newsletter) }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong>電子報內容</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="subject" class="form-label">主旨 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('subject') is-invalid @enderror"
                               id="subject"
                               name="subject"
                               value="{{ old('subject', $newsletter->subject) }}"
                               required
                               maxlength="255">
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">內容 <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content"
                                  name="content"
                                  rows="20"
                                  required>{{ old('content', $newsletter->content) }}</textarea>
                        <div class="form-text">支援 HTML 格式。可使用 &lt;h2&gt;、&lt;p&gt;、&lt;a&gt;、&lt;img&gt;、&lt;ul&gt;、&lt;ol&gt; 等標籤。</div>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong>發送資訊</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">狀態</label>
                        <div><span class="badge bg-secondary">草稿</span></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">目前訂閱人數</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <svg class="icon">
                                    <use xlink:href="/assets/icons/free.svg#cil-people"></use>
                                </svg>
                            </span>
                            <input type="text" class="form-control" value="{{ number_format($subscriberCount) }} 位" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">建立時間</label>
                        <input type="text" class="form-control" value="{{ $newsletter->created_at->format('Y-m-d H:i') }}" readonly>
                    </div>
                </div>
            </div>

            {{-- 發送測試郵件 --}}
            <div class="card mt-3">
                <div class="card-header">
                    <strong>發送測試郵件</strong>
                </div>
                <div class="card-body">
                    <p class="small text-muted">發送一封測試郵件以確認內容正確。</p>
                    <button type="button" class="btn btn-outline-info w-100" data-coreui-toggle="modal" data-coreui-target="#testEmailModal">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-envelope-letter"></use>
                        </svg>
                        發送測試郵件
                    </button>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="/assets/icons/free.svg#cil-save"></use>
                            </svg>
                            儲存變更
                        </button>
                        <button type="button" class="btn btn-success" data-coreui-toggle="modal" data-coreui-target="#sendConfirmModal">
                            <svg class="icon me-2">
                                <use xlink:href="/assets/icons/free.svg#cil-send"></use>
                            </svg>
                            發送給所有訂閱者
                        </button>
                        <a href="{{ route('admin.newsletters.index') }}" class="btn btn-light">返回列表</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- 發送確認 Modal --}}
<div class="modal fade" id="sendConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">確認發送電子報</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>確定要將此電子報發送給 <strong>{{ number_format($subscriberCount) }}</strong> 位訂閱者嗎？</p>
                <p class="text-muted small">發送後將無法編輯或撤回。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-coreui-dismiss="modal">取消</button>
                <form method="POST" action="{{ route('admin.newsletters.send', $newsletter) }}">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-send"></use>
                        </svg>
                        確認發送
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- 測試郵件 Modal --}}
<div class="modal fade" id="testEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.newsletters.send-test', $newsletter) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">發送測試郵件</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="test_email" class="form-label">收件 Email</label>
                        <input type="email"
                               class="form-control"
                               id="test_email"
                               name="email"
                               value="{{ auth()->user()->email }}"
                               required
                               placeholder="輸入測試收件人 Email">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-info">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-send"></use>
                        </svg>
                        發送測試
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
@include('admin.partials.tinymce', ['selector' => 'content'])
@endpush
@endsection
