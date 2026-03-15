@extends('layouts.admin')

@section('title', '新增作品')

@php
    $breadcrumbs = [
        ['title' => '作品集管理', 'url' => route('admin.projects.index')],
        ['title' => '新增作品', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">新增作品</h2>
        <p class="text-muted">建立新的作品集項目</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.projects.store') }}">
    @csrf

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong>基本資訊</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">作品標題 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('title') is-invalid @enderror"
                               id="title"
                               name="title"
                               value="{{ old('title') }}"
                               required
                               maxlength="200">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">網址別名 (Slug)</label>
                        <input type="text"
                               class="form-control @error('slug') is-invalid @enderror"
                               id="slug"
                               name="slug"
                               value="{{ old('slug') }}"
                               maxlength="200">
                        <div class="form-text">留空將自動根據標題生成</div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="excerpt" class="form-label">作品摘要</label>
                        <textarea class="form-control @error('excerpt') is-invalid @enderror"
                                  id="excerpt"
                                  name="excerpt"
                                  rows="3"
                                  maxlength="500">{{ old('excerpt') }}</textarea>
                        <div class="form-text">簡短描述此作品</div>
                        @error('excerpt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">作品詳細描述</label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content"
                                  name="content"
                                  rows="15">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="results" class="form-label">專案成果</label>
                        <textarea class="form-control" id="results" name="results" rows="3" placeholder="例如：下載量提升 150%、營收成長 40%">{{ old('results') }}</textarea>
                        <small class="text-muted">填寫具體成效，將顯示在前台作品集頁面</small>
                    </div>

                    <div class="mb-3">
                        <label for="cover_image" class="form-label">封面圖片</label>
                        <div class="media-picker-preview-container mb-2" style="display: none;">
                            <img id="media-picker-preview-cover_image"
                                 class="img-thumbnail"
                                 style="max-width: 300px; max-height: 200px; display: none;"
                                 alt="Preview">
                        </div>
                        <div class="input-group">
                            <input type="text"
                                   class="form-control @error('cover_image') is-invalid @enderror"
                                   id="cover_image"
                                   name="cover_image"
                                   value="{{ old('cover_image') }}"
                                   placeholder="圖片 URL（可從媒體庫選擇或直接輸入）">
                            <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('cover_image')">
                                <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-image"></use></svg>
                                媒體庫
                            </button>
                        </div>
                        <div class="form-text">從媒體庫選擇圖片或直接輸入圖片網址</div>
                        @error('cover_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong>發佈設定</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">狀態 <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror"
                                id="status"
                                name="status"
                                required>
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>草稿</option>
                            <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>已發布</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">分類</label>
                        <input type="text"
                               class="form-control @error('category') is-invalid @enderror"
                               id="category"
                               name="category"
                               value="{{ old('category') }}"
                               placeholder="例如：Web 應用、行動應用">
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="client" class="form-label">客戶名稱</label>
                        <input type="text"
                               class="form-control @error('client') is-invalid @enderror"
                               id="client"
                               name="client"
                               value="{{ old('client') }}">
                        @error('client')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="url" class="form-label">作品網址</label>
                        <input type="url"
                               class="form-control @error('url') is-invalid @enderror"
                               id="url"
                               name="url"
                               value="{{ old('url') }}"
                               placeholder="https://">
                        @error('url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="github_url" class="form-label">GitHub 網址</label>
                        <input type="url"
                               class="form-control @error('github_url') is-invalid @enderror"
                               id="github_url"
                               name="github_url"
                               value="{{ old('github_url') }}"
                               placeholder="https://github.com/">
                        @error('github_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="tech_stack" class="form-label">技術棧</label>
                        <input type="text"
                               class="form-control @error('tech_stack') is-invalid @enderror"
                               id="tech_stack"
                               name="tech_stack"
                               value="{{ old('tech_stack') }}"
                               placeholder="Laravel, Vue.js, MySQL">
                        <div class="form-text">多個技術請用逗號分隔</div>
                        @error('tech_stack')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="completed_at" class="form-label">完成日期</label>
                        <input type="date"
                               class="form-control @error('completed_at') is-invalid @enderror"
                               id="completed_at"
                               name="completed_at"
                               value="{{ old('completed_at') }}">
                        @error('completed_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="is_featured"
                                   name="is_featured"
                                   value="1"
                                   {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                設為精選作品
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="order" class="form-label">排序</label>
                        <input type="number"
                               class="form-control @error('order') is-invalid @enderror"
                               id="order"
                               name="order"
                               value="{{ old('order', 0) }}"
                               min="0">
                        <div class="form-text">數字越小越前面</div>
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                            儲存
                        </button>
                        <a href="{{ route('admin.projects.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@include('admin.media.partials.picker-modal')

@push('scripts')
<script>
    // 自動生成 Slug
    document.getElementById('title').addEventListener('blur', function() {
        const slugInput = document.getElementById('slug');
        if (!slugInput.value) {
            const title = this.value;
            const slug = title
                .toLowerCase()
                .replace(/[\s_]+/g, '-')
                .replace(/[^\w\-\u4e00-\u9fa5]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '')
                .replace(/-+$/, '');
            slugInput.value = slug;
        }
    });

    // 封面圖片 URL 預覽
    document.getElementById('cover_image').addEventListener('input', function() {
        const url = this.value.trim();
        const preview = document.getElementById('media-picker-preview-cover_image');
        const container = preview?.closest('.media-picker-preview-container');
        if (url && preview) {
            preview.src = url;
            preview.style.display = 'block';
            if (container) container.style.display = 'block';
        } else if (preview) {
            preview.style.display = 'none';
            if (container) container.style.display = 'none';
        }
    });
</script>
@endpush
@endsection
