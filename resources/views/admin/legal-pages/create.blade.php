@extends('layouts.admin')

@section('title', '新增法律頁面')

@php
    $breadcrumbs = [
        ['title' => '法律頁面管理', 'url' => route('admin.legal-pages.index')],
        ['title' => '新增', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col">
        <h2 class="mb-0">新增法律頁面</h2>
        <p class="text-muted">建立隱私權政策、服務條款等法律文件</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.legal-pages.store') }}">
    @csrf
    <div class="row">
        {{-- 左側：主要內容 --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><strong>頁面內容</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">頁面標題 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">URL Slug</label>
                        <div class="input-group">
                            <span class="input-group-text">/legal/</span>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                   id="slug" name="slug" value="{{ old('slug') }}"
                                   placeholder="自動從標題生成">
                        </div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">內容 <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content" name="content" rows="25">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">支援 HTML 格式，可直接貼上法律文件內容。</div>
                    </div>
                </div>
            </div>

            {{-- SEO 設定 --}}
            <div class="card mb-4">
                <div class="card-header"><strong>SEO 設定</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="meta_title" class="form-label">Meta 標題</label>
                        <input type="text" class="form-control @error('meta_title') is-invalid @enderror"
                               id="meta_title" name="meta_title" value="{{ old('meta_title') }}"
                               placeholder="留空則使用頁面標題">
                        @error('meta_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta 描述</label>
                        <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                  id="meta_description" name="meta_description" rows="3"
                                  placeholder="留空則自動擷取內容前 160 字">{{ old('meta_description') }}</textarea>
                        @error('meta_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- 右側：設定 --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><strong>頁面設定</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">頁面類型 <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            @foreach($types as $val => $label)
                                <option value="{{ $val }}" {{ old('type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="order" class="form-label">排序</label>
                        <input type="number" class="form-control" id="order" name="order"
                               value="{{ old('order', 0) }}" readonly>
                        <div class="form-text">排序由列表頁的「拖曳排序」功能管理</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">啟用頁面</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg>
                    建立頁面
                </button>
                <a href="{{ route('admin.legal-pages.index') }}" class="btn btn-light">取消</a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
@include('admin.partials.tinymce', ['selector' => 'content'])
<script>
// 自動從標題生成 slug
document.getElementById('title')?.addEventListener('input', function() {
    var slugField = document.getElementById('slug');
    if (!slugField.value || slugField.dataset.autoGenerate !== 'false') {
        slugField.value = this.value.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
        slugField.dataset.autoGenerate = 'true';
    }
});
document.getElementById('slug')?.addEventListener('input', function() {
    this.dataset.autoGenerate = 'false';
});
</script>
@endpush

@push('styles')
<style>
#content { font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, monospace; font-size: .875rem; }
</style>
@endpush
@endsection
