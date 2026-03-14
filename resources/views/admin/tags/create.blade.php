@extends('layouts.admin')

@section('title', '新增標籤')

@php
    $breadcrumbs = [
        ['title' => '標籤管理', 'url' => route('admin.tags.index')],
        ['title' => '新增標籤', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">新增標籤</h2>
        <p class="text-muted">建立新的文章標籤</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.tags.store') }}">
    @csrf

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
                               value="{{ old('name') }}"
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
                               value="{{ old('slug') }}"
                               maxlength="50">
                        <div class="form-text">留空將自動根據名稱生成</div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">標籤描述</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4">{{ old('description') }}</textarea>
                        <div class="form-text">簡短描述此標籤的用途</div>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>使用說明</strong>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <h6 class="alert-heading">標籤命名建議</h6>
                        <ul class="mb-0">
                            <li>使用簡短、描述性的詞彙（2-10 個字元）</li>
                            <li>避免使用過於寬泛的標籤（如「文章」、「內容」）</li>
                            <li>使用一致的命名規則（全小寫或駝峰式）</li>
                            <li>標籤數量建議控制在 20-50 個之間</li>
                            <li>定期清理未使用的標籤</li>
                        </ul>
                    </div>
                </div>
            </div>
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
                                   value="{{ old('color', '#6c757d') }}"
                                   style="max-width: 60px;"
                                   required>
                            <input type="text"
                                   class="form-control"
                                   id="color-text"
                                   value="{{ old('color', '#6c757d') }}"
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
                            <span id="tag-preview" class="badge" style="background-color: {{ old('color', '#6c757d') }}; font-size: 1.2rem;">
                                <span id="tag-name-display">標籤名稱</span>
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
                            儲存
                        </button>
                        <a href="{{ route('admin.tags.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    // 自動生成 Slug
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
