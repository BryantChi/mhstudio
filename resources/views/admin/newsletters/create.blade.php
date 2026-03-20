@extends('layouts.admin')

@section('title', '建立電子報')

@php
    $breadcrumbs = [
        ['title' => '電子報管理', 'url' => route('admin.newsletters.index')],
        ['title' => '建立電子報', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">建立電子報</h2>
        <p class="text-muted">撰寫電子報內容</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.newsletters.store') }}">
    @csrf

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
                               value="{{ old('subject') }}"
                               required
                               maxlength="255"
                               placeholder="輸入電子報主旨">
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
                                  required
                                  placeholder="輸入電子報 HTML 內容">{{ old('content') }}</textarea>
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
                        <label class="form-label">目前訂閱人數</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <svg class="icon">
                                    <use xlink:href="/assets/icons/free.svg#cil-people"></use>
                                </svg>
                            </span>
                            <input type="text" class="form-control" value="{{ number_format($subscriberCount) }} 位" readonly>
                        </div>
                        <div class="form-text">發送時將寄給所有活躍訂閱者</div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="/assets/icons/free.svg#cil-save"></use>
                            </svg>
                            儲存為草稿
                        </button>
                        <a href="{{ route('admin.newsletters.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@push('scripts')
@include('admin.partials.tinymce', ['selector' => 'content'])
@endpush
@endsection
