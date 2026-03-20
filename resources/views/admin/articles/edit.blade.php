@extends('layouts.admin')

@section('title', '編輯文章')

@php
    $breadcrumbs = [
        ['title' => '文章管理', 'url' => route('admin.articles.index')],
        ['title' => '編輯文章', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">編輯文章</h2>
        <p class="text-muted">修改文章內容</p>
    </div>
    <div class="col-md-6 text-md-end">
        @can('view articles')
        <a href="{{ route('admin.articles.show', $article) }}" class="btn btn-light">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-info"></use>
            </svg>
            查看
        </a>
        @endcan
    </div>
</div>

<form method="POST" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong>基本資訊</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">文章標題 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('title') is-invalid @enderror"
                               id="title"
                               name="title"
                               value="{{ old('title', $article->title) }}"
                               required
                               maxlength="200">
                        <div class="form-text">建議 60 字元以內，有利於 SEO</div>
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
                               value="{{ old('slug', $article->slug) }}"
                               maxlength="200">
                        <div class="form-text">
                            當前網址：{{ url('articles/' . $article->slug) }}
                        </div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="excerpt" class="form-label">文章摘要</label>
                        <textarea class="form-control @error('excerpt') is-invalid @enderror"
                                  id="excerpt"
                                  name="excerpt"
                                  rows="3"
                                  maxlength="500">{{ old('excerpt', $article->excerpt) }}</textarea>
                        <div class="form-text">建議 160 字元以內，用於搜尋引擎描述</div>
                        @error('excerpt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">文章內容 <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content"
                                  name="content"
                                  rows="15"
                                  required>{{ old('content', $article->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="featured_image" class="form-label">精選圖片</label>
                        <div class="media-picker-preview-container mb-2" style="{{ $article->featured_image ? '' : 'display: none;' }}">
                            <img id="media-picker-preview-featured_image"
                                 src="{{ $article->featured_image ?? '' }}"
                                 class="img-thumbnail"
                                 style="max-width: 300px; max-height: 200px; {{ $article->featured_image ? '' : 'display: none;' }}"
                                 alt="Current featured image">
                            @if($article->featured_image)
                            <div class="form-check mt-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="remove_featured_image"
                                       name="remove_featured_image"
                                       value="1">
                                <label class="form-check-label" for="remove_featured_image">
                                    清除圖片
                                </label>
                            </div>
                            @endif
                        </div>
                        <div class="input-group">
                            <input type="text"
                                   class="form-control @error('featured_image') is-invalid @enderror"
                                   id="featured_image"
                                   name="featured_image"
                                   value="{{ old('featured_image', $article->featured_image) }}"
                                   placeholder="圖片 URL（可從媒體庫選擇或直接輸入）">
                            <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('featured_image')">
                                <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-image"></use></svg>
                                媒體庫
                            </button>
                        </div>
                        <div class="form-text">從媒體庫選擇圖片或直接輸入 URL。建議尺寸 1200x630 像素</div>
                        @error('featured_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>SEO 設定</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="meta_title" class="form-label">SEO 標題</label>
                        <input type="text"
                               class="form-control @error('meta_title') is-invalid @enderror"
                               id="meta_title"
                               name="meta_title"
                               value="{{ old('meta_title', $article->seoMeta->meta_title ?? '') }}"
                               maxlength="60">
                        <div class="form-text">留空將使用文章標題，建議 60 字元以內</div>
                        @error('meta_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="meta_description" class="form-label">SEO 描述</label>
                        <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                  id="meta_description"
                                  name="meta_description"
                                  rows="3"
                                  maxlength="160">{{ old('meta_description', $article->seoMeta->meta_description ?? '') }}</textarea>
                        <div class="form-text">留空將使用文章摘要，建議 160 字元以內</div>
                        @error('meta_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="meta_keywords" class="form-label">SEO 關鍵字</label>
                        <input type="text"
                               class="form-control @error('meta_keywords') is-invalid @enderror"
                               id="meta_keywords"
                               name="meta_keywords"
                               value="{{ old('meta_keywords', $article->seoMeta->meta_keywords ?? '') }}">
                        <div class="form-text">多個關鍵字請用逗號分隔</div>
                        @error('meta_keywords')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>統計資訊</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">瀏覽次數</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <svg class="icon">
                                            <use xlink:href="/assets/icons/free.svg#cil-info"></use>
                                        </svg>
                                    </span>
                                    <input type="text" class="form-control" value="{{ number_format($article->views_count) }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">建立時間</label>
                                <input type="text" class="form-control" value="{{ $article->created_at->format('Y-m-d H:i') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">最後更新</label>
                                <input type="text" class="form-control" value="{{ $article->updated_at->format('Y-m-d H:i') }}" readonly>
                            </div>
                        </div>
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
                            <option value="draft" {{ old('status', $article->status) == 'draft' ? 'selected' : '' }}>草稿</option>
                            <option value="published" {{ old('status', $article->status) == 'published' ? 'selected' : '' }}>已發布</option>
                            <option value="scheduled" {{ old('status', $article->status) == 'scheduled' ? 'selected' : '' }}>排程發布</option>
                            <option value="archived" {{ old('status', $article->status) == 'archived' ? 'selected' : '' }}>封存</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="published_at" class="form-label">發布時間</label>
                        <input type="datetime-local"
                               class="form-control @error('published_at') is-invalid @enderror"
                               id="published_at"
                               name="published_at"
                               value="{{ old('published_at', $article->published_at ? $article->published_at->format('Y-m-d\TH:i') : '') }}">
                        <div class="form-text">排程發布時必填</div>
                        @error('published_at')
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
                                   {{ old('is_featured', $article->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                設為精選文章
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="allow_comments"
                                   name="allow_comments"
                                   value="1"
                                   {{ old('allow_comments', $article->allow_comments) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_comments">
                                允許評論
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">作者</label>
                        <input type="text" class="form-control" value="{{ $article->author->name ?? 'Unknown' }}" readonly>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>分類與標籤</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">分類</label>
                        <select class="form-select @error('category_id') is-invalid @enderror"
                                id="category_id"
                                name="category_id">
                            <option value="">未分類</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $article->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="tags" class="form-label">標籤</label>
                        <select class="form-select @error('tags') is-invalid @enderror"
                                id="tags"
                                name="tags[]"
                                multiple
                                size="8">
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}"
                                    {{ in_array($tag->id, old('tags', $article->tags->pluck('id')->toArray())) ? 'selected' : '' }}>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">按住 Ctrl/Cmd 可選擇多個標籤</div>
                        @error('tags')
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
                            更新
                        </button>
                        <a href="{{ route('admin.articles.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@include('admin.media.partials.picker-modal')

@push('scripts')
@include('admin.partials.tinymce', ['selector' => 'content'])
<script>
    // 自動生成 Slug（僅在 slug 為空時）
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

    // 字數統計
    const titleInput = document.getElementById('title');
    const excerptTextarea = document.getElementById('excerpt');

    function updateCharCount(element, maxLength) {
        const current = element.value.length;
        const helpText = element.nextElementSibling;
        if (helpText && helpText.classList.contains('form-text')) {
            const originalText = helpText.textContent;
            helpText.textContent = `${originalText.split('(')[0]} (${current}/${maxLength})`;
            if (current > maxLength * 0.9) {
                helpText.classList.add('text-warning');
            } else {
                helpText.classList.remove('text-warning');
            }
        }
    }

    titleInput.addEventListener('input', () => updateCharCount(titleInput, 60));
    excerptTextarea.addEventListener('input', () => updateCharCount(excerptTextarea, 160));

    // 圖片 URL 預覽
    document.getElementById('featured_image').addEventListener('input', function() {
        const url = this.value.trim();
        const preview = document.getElementById('media-picker-preview-featured_image');
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

    // 當勾選「清除圖片」時，清除 URL
    const removeCheckbox = document.getElementById('remove_featured_image');
    if (removeCheckbox) {
        removeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('featured_image').value = '';
                const preview = document.getElementById('media-picker-preview-featured_image');
                if (preview) preview.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection
