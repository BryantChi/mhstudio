@extends('layouts.admin')

@section('title', '一般設定')

@php
    $breadcrumbs = [
        ['title' => '系統設定', 'url' => '#'],
        ['title' => '一般設定', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">一般設定</h2>
        <p class="text-muted">配置網站基本資訊與系統設定</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.general.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong>網站資訊</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="site_name" class="form-label">網站名稱 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('site_name') is-invalid @enderror"
                               id="site_name"
                               name="site_name"
                               value="{{ old('site_name', setting('site_name', '')) }}"
                               required>
                        @error('site_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="site_description" class="form-label">網站描述</label>
                        <textarea class="form-control @error('site_description') is-invalid @enderror"
                                  id="site_description"
                                  name="site_description"
                                  rows="3">{{ old('site_description', setting('site_description', '')) }}</textarea>
                        <div class="form-text">用於首頁 Meta 描述，建議 160 字元以內</div>
                        @error('site_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="site_keywords" class="form-label">網站關鍵字</label>
                        <input type="text"
                               class="form-control @error('site_keywords') is-invalid @enderror"
                               id="site_keywords"
                               name="site_keywords"
                               value="{{ old('site_keywords', setting('site_keywords', '')) }}">
                        <div class="form-text">多個關鍵字用逗號分隔</div>
                        @error('site_keywords')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="site_url" class="form-label">網站網址</label>
                            <input type="url"
                                   class="form-control @error('site_url') is-invalid @enderror"
                                   id="site_url"
                                   name="site_url"
                                   value="{{ old('site_url', setting('site_url', url('/'))) }}">
                            @error('site_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="admin_email" class="form-label">管理員 Email</label>
                            <input type="email"
                                   class="form-control @error('admin_email') is-invalid @enderror"
                                   id="admin_email"
                                   name="admin_email"
                                   value="{{ old('admin_email', setting('admin_email', '')) }}">
                            @error('admin_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="site_logo" class="form-label">網站 Logo</label>
                        <div class="media-picker-preview-container mb-2" style="{{ setting('site_logo') ? '' : 'display: none;' }}">
                            <img id="media-picker-preview-site_logo"
                                 src="{{ setting('site_logo', '') }}"
                                 alt="Logo"
                                 style="max-height: 60px; {{ setting('site_logo') ? '' : 'display: none;' }}">
                        </div>
                        <div class="input-group">
                            <input type="text"
                                   class="form-control @error('site_logo') is-invalid @enderror"
                                   id="site_logo"
                                   name="site_logo"
                                   value="{{ old('site_logo', setting('site_logo', '')) }}"
                                   placeholder="Logo 圖片 URL">
                            <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('site_logo')">
                                <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-image"></use></svg>
                                媒體庫
                            </button>
                        </div>
                        <div class="form-text">建議尺寸 200x60 像素，格式 PNG</div>
                        @error('site_logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="site_favicon" class="form-label">網站 Favicon</label>
                        <div class="media-picker-preview-container mb-2" style="{{ setting('site_favicon') ? '' : 'display: none;' }}">
                            <img id="media-picker-preview-site_favicon"
                                 src="{{ setting('site_favicon', '') }}"
                                 alt="Favicon"
                                 style="max-height: 32px; {{ setting('site_favicon') ? '' : 'display: none;' }}">
                        </div>
                        <div class="input-group">
                            <input type="text"
                                   class="form-control @error('site_favicon') is-invalid @enderror"
                                   id="site_favicon"
                                   name="site_favicon"
                                   value="{{ old('site_favicon', setting('site_favicon', '')) }}"
                                   placeholder="Favicon 圖片 URL">
                            <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('site_favicon')">
                                <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-image"></use></svg>
                                媒體庫
                            </button>
                        </div>
                        <div class="form-text">建議尺寸 32x32 或 64x64 像素，格式 ICO 或 PNG</div>
                        @error('site_favicon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>區域設定</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="timezone" class="form-label">時區</label>
                            <select class="form-select @error('timezone') is-invalid @enderror"
                                    id="timezone"
                                    name="timezone">
                                <option value="Asia/Taipei" {{ old('timezone', setting('timezone', 'Asia/Taipei')) == 'Asia/Taipei' ? 'selected' : '' }}>台北 (UTC+8)</option>
                                <option value="Asia/Shanghai" {{ old('timezone', setting('timezone')) == 'Asia/Shanghai' ? 'selected' : '' }}>上海 (UTC+8)</option>
                                <option value="Asia/Hong_Kong" {{ old('timezone', setting('timezone')) == 'Asia/Hong_Kong' ? 'selected' : '' }}>香港 (UTC+8)</option>
                                <option value="Asia/Tokyo" {{ old('timezone', setting('timezone')) == 'Asia/Tokyo' ? 'selected' : '' }}>東京 (UTC+9)</option>
                                <option value="UTC" {{ old('timezone', setting('timezone')) == 'UTC' ? 'selected' : '' }}>UTC (UTC+0)</option>
                            </select>
                            @error('timezone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="language" class="form-label">語言</label>
                            <select class="form-select @error('language') is-invalid @enderror"
                                    id="language"
                                    name="language">
                                <option value="zh_TW" {{ old('language', setting('language', 'zh_TW')) == 'zh_TW' ? 'selected' : '' }}>繁體中文</option>
                                <option value="zh_CN" {{ old('language', setting('language')) == 'zh_CN' ? 'selected' : '' }}>简体中文</option>
                                <option value="en" {{ old('language', setting('language')) == 'en' ? 'selected' : '' }}>English</option>
                                <option value="ja" {{ old('language', setting('language')) == 'ja' ? 'selected' : '' }}>日本語</option>
                            </select>
                            @error('language')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_format" class="form-label">日期格式</label>
                            <select class="form-select @error('date_format') is-invalid @enderror"
                                    id="date_format"
                                    name="date_format">
                                <option value="Y-m-d" {{ old('date_format', setting('date_format', 'Y-m-d')) == 'Y-m-d' ? 'selected' : '' }}>2024-01-15</option>
                                <option value="Y/m/d" {{ old('date_format', setting('date_format')) == 'Y/m/d' ? 'selected' : '' }}>2024/01/15</option>
                                <option value="d/m/Y" {{ old('date_format', setting('date_format')) == 'd/m/Y' ? 'selected' : '' }}>15/01/2024</option>
                                <option value="m/d/Y" {{ old('date_format', setting('date_format')) == 'm/d/Y' ? 'selected' : '' }}>01/15/2024</option>
                            </select>
                            @error('date_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="time_format" class="form-label">時間格式</label>
                            <select class="form-select @error('time_format') is-invalid @enderror"
                                    id="time_format"
                                    name="time_format">
                                <option value="H:i:s" {{ old('time_format', setting('time_format', 'H:i:s')) == 'H:i:s' ? 'selected' : '' }}>24 小時制 (23:59:59)</option>
                                <option value="g:i A" {{ old('time_format', setting('time_format')) == 'g:i A' ? 'selected' : '' }}>12 小時制 (11:59 PM)</option>
                            </select>
                            @error('time_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>內容設定</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="posts_per_page" class="form-label">每頁文章數</label>
                            <input type="number"
                                   class="form-control @error('posts_per_page') is-invalid @enderror"
                                   id="posts_per_page"
                                   name="posts_per_page"
                                   value="{{ old('posts_per_page', setting('posts_per_page', 10)) }}"
                                   min="1"
                                   max="100">
                            @error('posts_per_page')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="excerpt_length" class="form-label">摘要長度（字元）</label>
                            <input type="number"
                                   class="form-control @error('excerpt_length') is-invalid @enderror"
                                   id="excerpt_length"
                                   name="excerpt_length"
                                   value="{{ old('excerpt_length', setting('excerpt_length', 200)) }}"
                                   min="50"
                                   max="500">
                            @error('excerpt_length')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="allow_comments"
                                   name="allow_comments"
                                   value="1"
                                   {{ old('allow_comments', setting('allow_comments', true)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_comments">
                                預設允許評論
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="auto_excerpt"
                                   name="auto_excerpt"
                                   value="1"
                                   {{ old('auto_excerpt', setting('auto_excerpt', true)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_excerpt">
                                自動生成摘要
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong>系統狀態</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="site_active"
                                   name="site_active"
                                   value="1"
                                   {{ old('site_active', setting('site_active', true)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="site_active">
                                <strong>網站啟用</strong>
                            </label>
                        </div>
                        <small class="text-muted">關閉後將顯示維護頁面</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="registration_enabled"
                                   name="registration_enabled"
                                   value="1"
                                   {{ old('registration_enabled', setting('registration_enabled', false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="registration_enabled">
                                <strong>允許註冊</strong>
                            </label>
                        </div>
                        <small class="text-muted">是否開放用戶註冊</small>
                    </div>

                    <div class="mb-3">
                        <label for="maintenance_message" class="form-label">維護訊息</label>
                        <textarea class="form-control"
                                  id="maintenance_message"
                                  name="maintenance_message"
                                  rows="3">{{ old('maintenance_message', setting('maintenance_message', '網站維護中，請稍後再訪問。')) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>快取設定</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="cache_enabled"
                                   name="cache_enabled"
                                   value="1"
                                   {{ old('cache_enabled', setting('cache_enabled', true)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cache_enabled">
                                啟用快取
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="cache_lifetime" class="form-label">快取時間（分鐘）</label>
                        <input type="number"
                               class="form-control"
                               id="cache_lifetime"
                               name="cache_lifetime"
                               value="{{ old('cache_lifetime', setting('cache_lifetime', 60)) }}"
                               min="1">
                    </div>

                    <div class="d-grid">
                        <button type="button" class="btn btn-outline-danger" onclick="clearCache()">
                            <svg class="icon me-2">
                                <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                            </svg>
                            清除快取
                        </button>
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
                            儲存設定
                        </button>
                        <button type="reset" class="btn btn-light">重置</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@include('admin.media.partials.picker-modal')

@push('scripts')
<script>
    function clearCache() {
        if (confirm('確定要清除所有快取嗎？')) {
            // 實作清除快取邏輯
            alert('快取清除功能待實作');
        }
    }

    // Logo/Favicon URL 預覽
    ['site_logo', 'site_favicon'].forEach(function(fieldId) {
        const input = document.getElementById(fieldId);
        if (input) {
            input.addEventListener('input', function() {
                const url = this.value.trim();
                const preview = document.getElementById('media-picker-preview-' + fieldId);
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
        }
    });
</script>
@endpush
@endsection
