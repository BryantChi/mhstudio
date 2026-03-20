@extends('layouts.admin')

@section('title', '編輯評價')

@php
    $breadcrumbs = [
        ['title' => '客戶評價', 'url' => route('admin.testimonials.index')],
        ['title' => '編輯評價', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">編輯評價</h2>
        <p class="text-muted">修改客戶評價內容</p>
    </div>
    <div class="col-md-6 text-md-end">
        <form method="POST"
              action="{{ route('admin.testimonials.destroy', $testimonial) }}"
              class="d-inline"
              onsubmit="return confirm('確定要刪除此評價嗎？');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <svg class="icon me-2">
                    <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                </svg>
                刪除
            </button>
        </form>
    </div>
</div>

<form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong>客戶資訊</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="client_name" class="form-label">客戶姓名 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('client_name') is-invalid @enderror"
                               id="client_name"
                               name="client_name"
                               value="{{ old('client_name', $testimonial->client_name) }}"
                               required
                               maxlength="100">
                        @error('client_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="company" class="form-label">公司名稱</label>
                                <input type="text"
                                       class="form-control @error('company') is-invalid @enderror"
                                       id="company"
                                       name="company"
                                       value="{{ old('company', $testimonial->company) }}"
                                       maxlength="100">
                                @error('company')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="position" class="form-label">職稱</label>
                                <input type="text"
                                       class="form-control @error('position') is-invalid @enderror"
                                       id="position"
                                       name="position"
                                       value="{{ old('position', $testimonial->position) }}"
                                       maxlength="100">
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">評價內容 <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content"
                                  name="content"
                                  rows="6"
                                  required>{{ old('content', $testimonial->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="avatar" class="form-label">頭像</label>
                        <div class="media-picker-preview-container mb-2" style="{{ $testimonial->avatar ? '' : 'display: none;' }}">
                            <img id="media-picker-preview-avatar"
                                 src="{{ $testimonial->avatar ?? '' }}"
                                 class="img-thumbnail rounded-circle"
                                 style="max-width: 80px; max-height: 80px; {{ $testimonial->avatar ? '' : 'display: none;' }}"
                                 alt="Current avatar">
                        </div>
                        <div class="input-group">
                            <input type="text"
                                   class="form-control @error('avatar') is-invalid @enderror"
                                   id="avatar"
                                   name="avatar"
                                   value="{{ old('avatar', $testimonial->avatar) }}"
                                   placeholder="圖片 URL（可從媒體庫選擇或直接輸入）">
                            <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('avatar')">
                                <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-image"></use></svg>
                                媒體庫
                            </button>
                        </div>
                        <div class="form-text">從媒體庫選擇圖片或直接輸入頭像網址</div>
                        @error('avatar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="project_type" class="form-label">專案類型</label>
                        <input type="text"
                               class="form-control @error('project_type') is-invalid @enderror"
                               id="project_type"
                               name="project_type"
                               value="{{ old('project_type', $testimonial->project_type) }}"
                               placeholder="例如：網站設計、App 開發">
                        @error('project_type')
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">建立時間</label>
                                <input type="text" class="form-control" value="{{ $testimonial->created_at->format('Y-m-d H:i') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">最後更新</label>
                                <input type="text" class="form-control" value="{{ $testimonial->updated_at->format('Y-m-d H:i') }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong>評價設定</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="rating" class="form-label">評分 <span class="text-danger">*</span></label>
                        <select class="form-select @error('rating') is-invalid @enderror"
                                id="rating"
                                name="rating"
                                required>
                            <option value="">請選擇評分</option>
                            <option value="5" {{ old('rating', $testimonial->rating) == '5' ? 'selected' : '' }}>★★★★★ (5分)</option>
                            <option value="4" {{ old('rating', $testimonial->rating) == '4' ? 'selected' : '' }}>★★★★☆ (4分)</option>
                            <option value="3" {{ old('rating', $testimonial->rating) == '3' ? 'selected' : '' }}>★★★☆☆ (3分)</option>
                            <option value="2" {{ old('rating', $testimonial->rating) == '2' ? 'selected' : '' }}>★★☆☆☆ (2分)</option>
                            <option value="1" {{ old('rating', $testimonial->rating) == '1' ? 'selected' : '' }}>★☆☆☆☆ (1分)</option>
                        </select>
                        @error('rating')
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
                                   {{ old('is_featured', $testimonial->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                設為精選評價
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="is_active"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', $testimonial->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                啟用顯示
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="order" class="form-label">排序</label>
                        <input type="number" class="form-control" id="order" name="order"
                               value="{{ old('order', $testimonial->order) }}" min="0">
                        <div class="form-text">數字越小越前面，也可從列表頁使用拖曳排序</div>
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
                        <a href="{{ route('admin.testimonials.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@include('admin.media.partials.picker-modal')

@push('scripts')
<script>
    document.getElementById('avatar').addEventListener('input', function() {
        const url = this.value.trim();
        const preview = document.getElementById('media-picker-preview-avatar');
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
