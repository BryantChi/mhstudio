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

                    <div class="alert alert-info small mb-0">
                        <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-info"></use></svg>
                        建立作品後，可在編輯頁面管理多張圖片庫（支援拖曳排序、圖片說明等功能）。
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
                        <label for="visibility" class="form-label">可見性</label>
                        <select class="form-select @error('visibility') is-invalid @enderror"
                                id="visibility"
                                name="visibility">
                            <option value="public" {{ old('visibility', 'public') == 'public' ? 'selected' : '' }}>公開 — 列表可見+可點擊+搜尋引擎收錄</option>
                            <option value="showcase" {{ old('visibility') == 'showcase' ? 'selected' : '' }}>僅展示 — 列表可見但不可點擊，不被收錄</option>
                            <option value="unlisted" {{ old('visibility') == 'unlisted' ? 'selected' : '' }}>僅限連結 — 不出現在列表，有網址才能看</option>
                            <option value="hidden" {{ old('visibility') == 'hidden' ? 'selected' : '' }}>隱藏 — 完全不公開</option>
                        </select>
                        <div class="form-text">合作案件建議選「僅限連結」或「隱藏」</div>
                        @error('visibility')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="display_mode" class="form-label">顯示模式</label>
                        <select class="form-select @error('display_mode') is-invalid @enderror"
                                id="display_mode"
                                name="display_mode">
                            <option value="normal" {{ old('display_mode', 'normal') == 'normal' ? 'selected' : '' }}>正常展示</option>
                            <option value="blurred" {{ old('display_mode') == 'blurred' ? 'selected' : '' }}>模糊保密 — 截圖模糊+保密徽章</option>
                            <option value="abstract" {{ old('display_mode') == 'abstract' ? 'selected' : '' }}>抽象封面 — 不顯示實際截圖</option>
                        </select>
                        <div class="form-text">控制作品圖片的呈現方式（不影響可見性設定）</div>
                        @error('display_mode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="confidentialOptions" style="{{ in_array(old('display_mode'), ['blurred', 'abstract']) ? '' : 'display:none;' }}">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="hide_client"
                                       name="hide_client"
                                       value="1"
                                       {{ old('hide_client') ? 'checked' : '' }}>
                                <label class="form-check-label" for="hide_client">
                                    隱藏客戶名稱
                                </label>
                            </div>
                            <div class="form-text">前台顯示為「機密客戶」</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="hide_results"
                                       name="hide_results"
                                       value="1"
                                       {{ old('hide_results') ? 'checked' : '' }}>
                                <label class="form-check-label" for="hide_results">
                                    隱藏成果數據
                                </label>
                            </div>
                            <div class="form-text">前台不顯示專案成果區塊</div>
                        </div>

                        <div class="mb-3">
                            <label for="confidential_label" class="form-label">保密標籤文字</label>
                            <input type="text"
                                   class="form-control @error('confidential_label') is-invalid @enderror"
                                   id="confidential_label"
                                   name="confidential_label"
                                   value="{{ old('confidential_label') }}"
                                   placeholder="留空使用預設「Confidential Project」"
                                   maxlength="50">
                            @error('confidential_label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="abstractColorGroup" style="{{ old('display_mode') == 'abstract' ? '' : 'display:none;' }}">
                            <label for="abstract_color" class="form-label">抽象封面主色</label>
                            <div class="input-group">
                                <input type="color"
                                       class="form-control form-control-color"
                                       id="abstract_color_picker"
                                       value="{{ old('abstract_color', '#00d4ff') }}">
                                <input type="text"
                                       class="form-control @error('abstract_color') is-invalid @enderror"
                                       id="abstract_color"
                                       name="abstract_color"
                                       value="{{ old('abstract_color') }}"
                                       placeholder="#00d4ff"
                                       maxlength="7">
                            </div>
                            <div class="form-text">用於生成漸層封面的主色調</div>
                            @error('abstract_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="exclude_from_search"
                                   name="exclude_from_search"
                                   value="1"
                                   {{ old('exclude_from_search') ? 'checked' : '' }}>
                            <label class="form-check-label" for="exclude_from_search">
                                搜尋引擎不收錄 (noindex)
                            </label>
                        </div>
                        <div class="form-text">勾選後 Google 不會收錄此作品頁面</div>
                    </div>

                    <div class="mb-3">
                        <label for="categorySelect" class="form-label">分類</label>
                        <select class="form-select @error('category') is-invalid @enderror"
                                id="categorySelect">
                            <option value="">— 請選擇分類 —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                            <option value="__custom__" {{ old('category') && !$categories->contains(old('category')) ? 'selected' : '' }}>＋ 自訂新分類...</option>
                        </select>
                        <input type="text"
                               class="form-control mt-2 @error('category') is-invalid @enderror"
                               id="categoryCustom"
                               name="category"
                               value="{{ old('category') }}"
                               placeholder="輸入新分類名稱"
                               style="{{ old('category') && !$categories->contains(old('category')) ? '' : 'display:none;' }}">
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
                        <input type="number" class="form-control" id="order" name="order"
                               value="{{ old('order') }}" min="1">
                        <div class="form-text">排序位置（1 = 第一個），也可從列表頁使用拖曳排序</div>
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
@include('admin.partials.tinymce', ['selector' => 'content'])
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

    // 顯示模式切換
    (function() {
        const displayMode = document.getElementById('display_mode');
        const confidentialOptions = document.getElementById('confidentialOptions');
        const abstractColorGroup = document.getElementById('abstractColorGroup');
        const colorPicker = document.getElementById('abstract_color_picker');
        const colorInput = document.getElementById('abstract_color');

        if (displayMode) {
            displayMode.addEventListener('change', function() {
                confidentialOptions.style.display = this.value !== 'normal' ? '' : 'none';
                abstractColorGroup.style.display = this.value === 'abstract' ? '' : 'none';
            });
        }

        if (colorPicker && colorInput) {
            colorPicker.addEventListener('input', function() { colorInput.value = this.value; });
            colorInput.addEventListener('input', function() {
                if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) colorPicker.value = this.value;
            });
        }
    })();

    // 分類下拉選單 ↔ 自訂輸入切換
    (function() {
        const sel = document.getElementById('categorySelect');
        const inp = document.getElementById('categoryCustom');
        if (!sel || !inp) return;

        sel.addEventListener('change', function() {
            if (this.value === '__custom__') {
                inp.style.display = '';
                inp.value = '';
                inp.focus();
            } else {
                inp.style.display = 'none';
                inp.value = this.value;
            }
        });

        // 初始化：如果已選既有分類，同步 hidden input
        if (sel.value && sel.value !== '__custom__') {
            inp.value = sel.value;
        }
    })();
</script>
@endpush
@endsection
