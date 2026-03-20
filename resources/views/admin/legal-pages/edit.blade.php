@extends('layouts.admin')

@section('title', '編輯法律頁面')

@php
    $breadcrumbs = [
        ['title' => '法律頁面管理', 'url' => route('admin.legal-pages.index')],
        ['title' => '編輯: ' . $legalPage->title, 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col">
        <h2 class="mb-0">編輯法律頁面</h2>
        <p class="text-muted">{{ $legalPage->title }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.legal-pages.update', $legalPage) }}">
    @csrf
    @method('PUT')
    <div class="row">
        {{-- 左側：主要內容 --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><strong>頁面內容</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">頁面標題 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title', $legalPage->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">URL Slug</label>
                        <div class="input-group">
                            <span class="input-group-text">/legal/</span>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                   id="slug" name="slug" value="{{ old('slug', $legalPage->slug) }}">
                        </div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">內容 <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content" name="content" rows="25">{{ old('content', $legalPage->content) }}</textarea>
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
                               id="meta_title" name="meta_title"
                               value="{{ old('meta_title', $legalPage->meta_title) }}"
                               placeholder="留空則使用頁面標題">
                        @error('meta_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta 描述</label>
                        <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                  id="meta_description" name="meta_description" rows="3"
                                  placeholder="留空則自動擷取內容前 160 字">{{ old('meta_description', $legalPage->meta_description) }}</textarea>
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
                                <option value="{{ $val }}" {{ old('type', $legalPage->type) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="order" class="form-label">排序</label>
                        <input type="number" class="form-control @error('order') is-invalid @enderror"
                               id="order" name="order" value="{{ old('order', $legalPage->order) }}" min="0">
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $legalPage->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">啟用頁面</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <small class="text-muted">
                        建立時間：{{ $legalPage->created_at->format('Y-m-d H:i') }}<br>
                        最後更新：{{ $legalPage->updated_at->format('Y-m-d H:i') }}
                    </small>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg>
                    儲存變更
                </button>
                <a href="{{ route('legal.show', $legalPage->slug) }}" class="btn btn-outline-secondary" target="_blank">
                    <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-external-link"></use></svg>
                    前台預覽
                </a>
                <a href="{{ route('admin.legal-pages.index') }}" class="btn btn-light">返回列表</a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
@include('admin.partials.tinymce', ['selector' => 'content'])
@endpush

@push('styles')
<style>
#content { font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, monospace; font-size: .875rem; }
</style>
@endpush
@endsection
