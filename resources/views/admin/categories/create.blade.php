@extends('layouts.admin')

@section('title', '新增分類')

@php
    $breadcrumbs = [
        ['title' => '分類管理', 'url' => route('admin.categories.index')],
        ['title' => '新增分類', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">新增分類</h2>
        <p class="text-muted">建立新的文章分類</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong>基本資訊</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">分類名稱 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               required
                               maxlength="100">
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
                               maxlength="100">
                        <div class="form-text">留空將自動根據名稱生成</div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">分類描述</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4">{{ old('description') }}</textarea>
                        <div class="form-text">簡短描述此分類的用途</div>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">分類圖片</label>
                        <input type="file"
                               class="form-control @error('image') is-invalid @enderror"
                               id="image"
                               name="image"
                               accept="image/*">
                        <div class="form-text">建議尺寸 600x400 像素</div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>Meta 資訊</strong>
                    <span class="text-muted small ms-2">選填，用於儲存額外資訊</span>
                </div>
                <div class="card-body">
                    <div id="meta-fields">
                        <!-- 初始為空，可點擊下方按鈕新增 -->
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-meta">
                        <svg class="icon me-1">
                            <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
                        </svg>
                        新增 Meta 欄位
                    </button>
                    <div class="form-text mt-2">
                        常用範例：banner_image (橫幅圖片)、external_link (外部連結)、description_long (長描述)
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong>分類設定</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">父分類</label>
                        <select class="form-select @error('parent_id') is-invalid @enderror"
                                id="parent_id"
                                name="parent_id">
                            <option value="">無（頂層分類）</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('parent_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">選擇此分類的父分類</div>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">狀態 <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror"
                                id="status"
                                name="status"
                                required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>啟用</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>停用</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="order" class="form-label">排序</label>
                        <input type="number" class="form-control" id="order" name="order"
                               value="{{ old('order', 0) }}" readonly>
                        <div class="form-text">排序由列表頁的「拖曳排序」功能管理</div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>外觀設定</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="color" class="form-label">分類顏色</label>
                        <div class="input-group">
                            <input type="color"
                                   class="form-control form-control-color @error('color') is-invalid @enderror"
                                   id="color"
                                   name="color"
                                   value="{{ old('color', '#6c757d') }}"
                                   style="max-width: 60px;">
                            <input type="text"
                                   class="form-control"
                                   id="color-text"
                                   value="{{ old('color', '#6c757d') }}"
                                   readonly>
                        </div>
                        <div class="form-text">用於標籤和視覺識別</div>
                        @error('color')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="icon" class="form-label">圖標</label>
                        @include('admin.media.partials.icon-picker-field', [
                            'fieldName'  => 'icon',
                            'fieldId'    => 'icon',
                            'fieldValue' => old('icon'),
                            'placeholder' => '輸入圖示名稱（如 cil-folder、fas fa-folder）或從媒體庫選圖',
                        ])
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
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    // 自動生成 Slug
    document.getElementById('name').addEventListener('blur', function() {
        const slugInput = document.getElementById('slug');
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

    // 顏色選擇器同步
    const colorInput = document.getElementById('color');
    const colorText = document.getElementById('color-text');

    colorInput.addEventListener('input', function() {
        colorText.value = this.value;
    });

    // 圖片預覽
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.getElementById('image-preview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.id = 'image-preview';
                    preview.className = 'img-thumbnail mt-2';
                    preview.style.maxWidth = '100%';
                    preview.style.maxHeight = '200px';
                    document.getElementById('image').parentNode.appendChild(preview);
                }
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Meta 欄位動態管理
    document.getElementById('add-meta').addEventListener('click', function() {
        const container = document.getElementById('meta-fields');
        const newRow = document.createElement('div');
        newRow.className = 'row mb-2 meta-item';
        newRow.innerHTML = `
            <div class="col-md-4">
                <input type="text" class="form-control" name="meta_keys[]" placeholder="欄位名稱（例如：banner_image）">
            </div>
            <div class="col-md-7">
                <input type="text" class="form-control" name="meta_values[]" placeholder="欄位值">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-danger remove-meta">
                    <svg class="icon">
                        <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                    </svg>
                </button>
            </div>
        `;
        container.appendChild(newRow);
    });

    // 移除 Meta 欄位
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-meta')) {
            e.target.closest('.meta-item').remove();
        }
    });

    // 表單提交前將鍵值對轉換為 JSON
    document.querySelector('form').addEventListener('submit', function(e) {
        const keys = document.querySelectorAll('input[name="meta_keys[]"]');
        const values = document.querySelectorAll('input[name="meta_values[]"]');
        const metaObj = {};

        keys.forEach((keyInput, index) => {
            const key = keyInput.value.trim();
            const value = values[index].value.trim();
            if (key && value) {
                metaObj[key] = value;
            }
        });

        // 創建隱藏的 meta 欄位
        let metaInput = document.querySelector('input[name="meta"]');
        if (!metaInput) {
            metaInput = document.createElement('input');
            metaInput.type = 'hidden';
            metaInput.name = 'meta';
            this.appendChild(metaInput);
        }
        metaInput.value = JSON.stringify(metaObj);
    });
</script>
@endpush

@include('admin.media.partials.picker-modal')
@endsection
