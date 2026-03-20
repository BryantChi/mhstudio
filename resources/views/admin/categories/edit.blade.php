@extends('layouts.admin')

@section('title', '編輯分類')

@php
    $breadcrumbs = [
        ['title' => '分類管理', 'url' => route('admin.categories.index')],
        ['title' => '編輯分類', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">編輯分類</h2>
        <p class="text-muted">修改分類資訊</p>
    </div>
    <div class="col-md-6 text-md-end">
        @can('view categories')
        <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-light">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-info"></use>
            </svg>
            查看
        </a>
        @endcan
    </div>
</div>

<form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
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
                        <label for="name" class="form-label">分類名稱 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name', $category->name) }}"
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
                               value="{{ old('slug', $category->slug) }}"
                               maxlength="100">
                        <div class="form-text">
                            當前網址：{{ url('categories/' . $category->slug) }}
                        </div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">分類描述</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4">{{ old('description', $category->description) }}</textarea>
                        <div class="form-text">簡短描述此分類的用途</div>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">分類圖片</label>
                        @if($category->image)
                        <div class="mb-2">
                            <img src="{{ $category->image }}"
                                 class="img-thumbnail"
                                 style="max-width: 300px; max-height: 200px;"
                                 alt="Current category image">
                            <div class="form-check mt-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="remove_image"
                                       name="remove_image"
                                       value="1">
                                <label class="form-check-label" for="remove_image">
                                    刪除現有圖片
                                </label>
                            </div>
                        </div>
                        @endif
                        <input type="file"
                               class="form-control @error('image') is-invalid @enderror"
                               id="image"
                               name="image"
                               accept="image/*">
                        <div class="form-text">上傳新圖片將替換現有圖片。建議尺寸 600x400 像素</div>
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
                        @php
                            $metaData = old('meta_keys') ? array_combine(old('meta_keys'), old('meta_values')) : ($category->meta ?? []);
                        @endphp
                        @foreach($metaData as $key => $value)
                        <div class="row mb-2 meta-item">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="meta_keys[]" value="{{ $key }}" placeholder="欄位名稱（例如：banner_image）">
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" name="meta_values[]" value="{{ $value }}" placeholder="欄位值">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-danger remove-meta">
                                    <svg class="icon">
                                        <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
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

            <div class="card mt-3">
                <div class="card-header">
                    <strong>統計資訊</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">文章數量</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <svg class="icon">
                                            <use xlink:href="/assets/icons/free.svg#cil-file"></use>
                                        </svg>
                                    </span>
                                    <input type="text" class="form-control" value="{{ $category->articles_count ?? 0 }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">子分類數量</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <svg class="icon">
                                            <use xlink:href="/assets/icons/free.svg#cil-folder"></use>
                                        </svg>
                                    </span>
                                    <input type="text" class="form-control" value="{{ $category->children_count ?? 0 }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">建立時間</label>
                                <input type="text" class="form-control" value="{{ $category->created_at->format('Y-m-d H:i') }}" readonly>
                            </div>
                        </div>
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
                            @foreach($parentCategories as $cat)
                                @if($cat->id !== $category->id)
                                <option value="{{ $cat->id }}" {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endif
                            @endforeach
                        </select>
                        <div class="form-text">選擇此分類的父分類（不能選擇自己）</div>
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
                            <option value="active" {{ old('status', $category->status) == 'active' ? 'selected' : '' }}>啟用</option>
                            <option value="inactive" {{ old('status', $category->status) == 'inactive' ? 'selected' : '' }}>停用</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="order" class="form-label">排序</label>
                        <input type="number" class="form-control" id="order" name="order"
                               value="{{ old('order', $category->order) }}" readonly>
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
                                   value="{{ old('color', $category->color ?? '#6c757d') }}"
                                   style="max-width: 60px;">
                            <input type="text"
                                   class="form-control"
                                   id="color-text"
                                   value="{{ old('color', $category->color ?? '#6c757d') }}"
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
                            'fieldValue' => old('icon', $category->icon),
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
                            更新
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
    // 自動生成 Slug（僅在 slug 為空時）
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

    // 當勾選「刪除現有圖片」時，清除文件輸入
    const removeCheckbox = document.getElementById('remove_image');
    if (removeCheckbox) {
        removeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('image').value = '';
            }
        });
    }

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
