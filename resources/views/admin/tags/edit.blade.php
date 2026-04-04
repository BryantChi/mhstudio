@extends('layouts.admin')

@section('title', '編輯標籤')

@php
    $breadcrumbs = [
        ['title' => '標籤管理', 'url' => route('admin.tags.index')],
        ['title' => '編輯標籤', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">編輯標籤</h2>
        <p class="text-muted">修改標籤資訊</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.tags.update', $tag) }}">
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
                        <label for="name" class="form-label">標籤名稱 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name', $tag->name) }}"
                               required
                               maxlength="50">
                        <div class="form-text">簡短、易記的標籤名稱</div>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">網址別名 (Slug)</label>
                        <input type="text"
                               class="form-control @error('slug') is-invalid @enderror"
                               id="slug"
                               name="slug"
                               value="{{ old('slug', $tag->slug) }}"
                               maxlength="50">
                        <div class="form-text">
                            當前網址：{{ url('tags/' . $tag->slug) }}
                        </div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">標籤描述</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4">{{ old('description', $tag->description) }}</textarea>
                        <div class="form-text">簡短描述此標籤的用途</div>
                        @error('description')
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
                                <label class="form-label">使用次數</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <svg class="icon">
                                            <use xlink:href="/assets/icons/free.svg#cil-tag"></use>
                                        </svg>
                                    </span>
                                    <input type="text" class="form-control" value="{{ $tag->count }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">建立時間</label>
                                <input type="text" class="form-control" value="{{ $tag->created_at->format('Y-m-d H:i') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">最後更新</label>
                                <input type="text" class="form-control" value="{{ $tag->updated_at->format('Y-m-d H:i') }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($tag->articles && $tag->articles->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <strong>使用此標籤的文章（{{ $tag->articles->count() }}）</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>標題</th>
                                    <th>作者</th>
                                    <th>狀態</th>
                                    <th>發布時間</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tag->articles->take(5) as $article)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.articles.show', $article) }}">
                                            {{ $article->title }}
                                        </a>
                                    </td>
                                    <td>{{ $article->display_author_name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $article->status_color }}">
                                            {{ $article->status_label }}
                                        </span>
                                    </td>
                                    <td>{{ $article->published_at ? $article->published_at->format('Y-m-d') : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($tag->articles->count() > 5)
                    <div class="card-footer">
                        <small class="text-muted">還有 {{ $tag->articles->count() - 5 }} 篇文章使用此標籤</small>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong>外觀設定</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="color" class="form-label">標籤顏色 <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="color"
                                   class="form-control form-control-color @error('color') is-invalid @enderror"
                                   id="color"
                                   name="color"
                                   value="{{ old('color', $tag->color) }}"
                                   style="max-width: 60px;"
                                   required>
                            <input type="text"
                                   class="form-control"
                                   id="color-text"
                                   value="{{ old('color', $tag->color) }}"
                                   readonly>
                        </div>
                        <div class="form-text">用於標籤顯示的顏色</div>
                        @error('color')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">預設顏色</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm color-preset" data-color="#dc3545" style="background-color: #dc3545; width: 40px; height: 40px; border: 2px solid #fff; box-shadow: 0 0 0 1px #dee2e6;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#fd7e14" style="background-color: #fd7e14; width: 40px; height: 40px; border: 2px solid #fff; box-shadow: 0 0 0 1px #dee2e6;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#ffc107" style="background-color: #ffc107; width: 40px; height: 40px; border: 2px solid #fff; box-shadow: 0 0 0 1px #dee2e6;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#28a745" style="background-color: #28a745; width: 40px; height: 40px; border: 2px solid #fff; box-shadow: 0 0 0 1px #dee2e6;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#20c997" style="background-color: #20c997; width: 40px; height: 40px; border: 2px solid #fff; box-shadow: 0 0 0 1px #dee2e6;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#17a2b8" style="background-color: #17a2b8; width: 40px; height: 40px; border: 2px solid #fff; box-shadow: 0 0 0 1px #dee2e6;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#007bff" style="background-color: #007bff; width: 40px; height: 40px; border: 2px solid #fff; box-shadow: 0 0 0 1px #dee2e6;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#6610f2" style="background-color: #6610f2; width: 40px; height: 40px; border: 2px solid #fff; box-shadow: 0 0 0 1px #dee2e6;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#e83e8c" style="background-color: #e83e8c; width: 40px; height: 40px; border: 2px solid #fff; box-shadow: 0 0 0 1px #dee2e6;"></button>
                            <button type="button" class="btn btn-sm color-preset" data-color="#6c757d" style="background-color: #6c757d; width: 40px; height: 40px; border: 2px solid #fff; box-shadow: 0 0 0 1px #dee2e6;"></button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">標籤預覽</label>
                        <div class="p-3 bg-light rounded text-center">
                            <span id="tag-preview" class="badge" style="background-color: {{ old('color', $tag->color) }}; font-size: 1.2rem;">
                                <span id="tag-name-display">{{ old('name', $tag->name) }}</span>
                            </span>
                        </div>
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
                        <a href="{{ route('admin.tags.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>

            @can('delete tags')
            <div class="card mt-3">
                <div class="card-body">
                    @if($tag->count > 0)
                    <div class="alert alert-warning mb-3">
                        <small>⚠️ 此標籤正被 {{ $tag->count }} 篇文章使用，無法刪除</small>
                    </div>
                    @endif
                    <form method="POST"
                          action="{{ route('admin.tags.destroy', $tag) }}"
                          onsubmit="return confirm('確定要刪除此標籤嗎？此操作無法復原。');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-danger w-100"
                                {{ $tag->count > 0 ? 'disabled' : '' }}>
                            <svg class="icon me-2">
                                <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                            </svg>
                            刪除標籤
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</form>

@push('scripts')
<script>
    // 自動生成 Slug（僅在 slug 為空時）
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const tagNameDisplay = document.getElementById('tag-name-display');

    nameInput.addEventListener('blur', function() {
        if (!slugInput.value) {
            const name = this.value;
            const slug = name
                .toLowerCase()
                .replace(/[\s_]+/g, '-')
                .replace(/[^\w\-\u4e00-\u9fa5]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '')
                .replace(/-+$/, '');
            slugInput.value = slug;
        }
    });

    // 標籤名稱即時預覽
    nameInput.addEventListener('input', function() {
        tagNameDisplay.textContent = this.value || '標籤名稱';
    });

    // 顏色選擇器同步
    const colorInput = document.getElementById('color');
    const colorText = document.getElementById('color-text');
    const tagPreview = document.getElementById('tag-preview');

    colorInput.addEventListener('input', function() {
        colorText.value = this.value;
        tagPreview.style.backgroundColor = this.value;
    });

    // 預設顏色按鈕
    document.querySelectorAll('.color-preset').forEach(button => {
        button.addEventListener('click', function() {
            const color = this.getAttribute('data-color');
            colorInput.value = color;
            colorText.value = color;
            tagPreview.style.backgroundColor = color;
        });
    });
</script>
@endpush
@endsection
