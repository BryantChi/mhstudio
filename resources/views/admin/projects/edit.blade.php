@extends('layouts.admin')

@section('title', '編輯作品')

@php
    $breadcrumbs = [
        ['title' => '作品集管理', 'url' => route('admin.projects.index')],
        ['title' => '編輯作品', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">編輯作品</h2>
        <p class="text-muted">修改作品內容</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-light">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-info"></use>
            </svg>
            查看
        </a>

        <form method="POST"
              action="{{ route('admin.projects.destroy', $project) }}"
              class="d-inline"
              onsubmit="return confirm('確定要刪除此作品嗎？');">
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

<form method="POST" action="{{ route('admin.projects.update', $project) }}">
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
                        <label for="title" class="form-label">作品標題 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('title') is-invalid @enderror"
                               id="title"
                               name="title"
                               value="{{ old('title', $project->title) }}"
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
                               value="{{ old('slug', $project->slug) }}"
                               maxlength="200">
                        <div class="form-text">
                            當前網址：<a href="{{ route('portfolio.show', $project->slug) }}" target="_blank">{{ route('portfolio.show', $project->slug) }}</a>
                        </div>
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
                                  maxlength="500">{{ old('excerpt', $project->excerpt) }}</textarea>
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
                                  rows="15">{{ old('content', $project->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="results" class="form-label">專案成果</label>
                        <textarea class="form-control" id="results" name="results" rows="3" placeholder="例如：下載量提升 150%、營收成長 40%">{{ old('results', $project->results) }}</textarea>
                        <small class="text-muted">填寫具體成效，將顯示在前台作品集頁面</small>
                    </div>

                    <div class="mb-3">
                        <label for="cover_image" class="form-label">封面圖片</label>
                        <div class="media-picker-preview-container mb-2" style="{{ $project->cover_image ? '' : 'display: none;' }}">
                            <img id="media-picker-preview-cover_image"
                                 src="{{ $project->cover_image ?? '' }}"
                                 class="img-thumbnail"
                                 style="max-width: 300px; max-height: 200px; {{ $project->cover_image ? '' : 'display: none;' }}"
                                 alt="Current cover image">
                        </div>
                        <div class="input-group">
                            <input type="text"
                                   class="form-control @error('cover_image') is-invalid @enderror"
                                   id="cover_image"
                                   name="cover_image"
                                   value="{{ old('cover_image', $project->cover_image) }}"
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

            {{-- ===== 作品圖片庫 ===== --}}
            <div class="card mt-3" id="galleryCard">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>
                        <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-image"></use></svg>
                        作品圖片庫
                    </strong>
                    <span class="badge bg-secondary" id="galleryCount">{{ $project->images->count() }} 張</span>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">拖曳圖片可調整排序，第一張將自動作為封面圖片。</p>

                    {{-- 圖片 Grid --}}
                    <div class="row g-3 mb-3" id="galleryGrid">
                        @foreach($project->images as $image)
                        <div class="col-6 col-sm-4 col-md-3 gallery-item" data-id="{{ $image->id }}">
                            <div class="position-relative border rounded overflow-hidden" style="aspect-ratio: 4/3;">
                                <img src="{{ $image->image_url }}"
                                     alt="{{ $image->alt_text ?? $project->title }}"
                                     class="w-100 h-100"
                                     style="object-fit: cover;">
                                @if($loop->first)
                                <span class="badge bg-primary position-absolute top-0 start-0 m-1" style="font-size: 0.7rem;">封面</span>
                                @endif
                                <button type="button"
                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 gallery-delete-btn"
                                        data-id="{{ $image->id }}"
                                        style="padding: 2px 6px; font-size: 0.7rem; line-height: 1;"
                                        title="刪除圖片">
                                    &times;
                                </button>
                            </div>
                            <div class="mt-1">
                                <input type="text"
                                       class="form-control form-control-sm gallery-caption-input"
                                       data-id="{{ $image->id }}"
                                       value="{{ $image->caption ?? '' }}"
                                       placeholder="圖片說明 (選填)">
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- 新增圖片按鈕（多選模式） --}}
                    <button type="button" class="btn btn-outline-primary btn-sm" id="galleryAddBtn">
                        <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-library-add"></use></svg>
                        從媒體庫新增圖片
                    </button>
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
                                <input type="text" class="form-control" value="{{ $project->created_at->format('Y-m-d H:i') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">最後更新</label>
                                <input type="text" class="form-control" value="{{ $project->updated_at->format('Y-m-d H:i') }}" readonly>
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
                            <option value="draft" {{ old('status', $project->status) == 'draft' ? 'selected' : '' }}>草稿</option>
                            <option value="published" {{ old('status', $project->status) == 'published' ? 'selected' : '' }}>已發布</option>
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
                            <option value="public" {{ old('visibility', $project->visibility) == 'public' ? 'selected' : '' }}>公開 — 列表可見+可點擊+搜尋引擎收錄</option>
                            <option value="showcase" {{ old('visibility', $project->visibility) == 'showcase' ? 'selected' : '' }}>僅展示 — 列表可見但不可點擊，不被收錄</option>
                            <option value="unlisted" {{ old('visibility', $project->visibility) == 'unlisted' ? 'selected' : '' }}>僅限連結 — 不出現在列表，有網址才能看</option>
                            <option value="hidden" {{ old('visibility', $project->visibility) == 'hidden' ? 'selected' : '' }}>隱藏 — 完全不公開</option>
                        </select>
                        <div class="form-text">合作案件建議選「僅限連結」或「隱藏」</div>
                        @error('visibility')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @php
                        $currentDisplayMode = old('display_mode', $project->display_mode ?? 'normal');
                    @endphp
                    <div class="mb-3">
                        <label for="display_mode" class="form-label">顯示模式</label>
                        <select class="form-select @error('display_mode') is-invalid @enderror"
                                id="display_mode"
                                name="display_mode">
                            <option value="normal" {{ $currentDisplayMode == 'normal' ? 'selected' : '' }}>正常展示</option>
                            <option value="blurred" {{ $currentDisplayMode == 'blurred' ? 'selected' : '' }}>模糊保密 — 截圖模糊+保密徽章</option>
                            <option value="abstract" {{ $currentDisplayMode == 'abstract' ? 'selected' : '' }}>抽象封面 — 不顯示實際截圖</option>
                        </select>
                        <div class="form-text">控制作品圖片的呈現方式（不影響可見性設定）</div>
                        @error('display_mode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="confidentialOptions" style="{{ in_array($currentDisplayMode, ['blurred', 'abstract']) ? '' : 'display:none;' }}">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="hide_client"
                                       name="hide_client"
                                       value="1"
                                       {{ old('hide_client', $project->hide_client) ? 'checked' : '' }}>
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
                                       {{ old('hide_results', $project->hide_results) ? 'checked' : '' }}>
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
                                   value="{{ old('confidential_label', $project->confidential_label) }}"
                                   placeholder="留空使用預設「Confidential Project」"
                                   maxlength="50">
                            @error('confidential_label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="abstractColorGroup" style="{{ $currentDisplayMode == 'abstract' ? '' : 'display:none;' }}">
                            <label for="abstract_color" class="form-label">抽象封面主色</label>
                            <div class="input-group">
                                <input type="color"
                                       class="form-control form-control-color"
                                       id="abstract_color_picker"
                                       value="{{ old('abstract_color', $project->abstract_color ?? '#00d4ff') }}">
                                <input type="text"
                                       class="form-control @error('abstract_color') is-invalid @enderror"
                                       id="abstract_color"
                                       name="abstract_color"
                                       value="{{ old('abstract_color', $project->abstract_color) }}"
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
                                   {{ old('exclude_from_search', $project->exclude_from_search) ? 'checked' : '' }}>
                            <label class="form-check-label" for="exclude_from_search">
                                搜尋引擎不收錄 (noindex)
                            </label>
                        </div>
                        <div class="form-text">勾選後 Google 不會收錄此作品頁面</div>
                    </div>

                    {{-- 私密分享連結 --}}
                    @if($project->share_token)
                    <div class="mb-3">
                        <label class="form-label">私密分享連結</label>
                        <div class="input-group">
                            <input type="text"
                                   class="form-control form-control-sm"
                                   id="shareUrl"
                                   value="{{ route('portfolio.share', $project->share_token) }}"
                                   readonly>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('shareUrl').value);this.textContent='已複製';setTimeout(()=>this.textContent='複製',2000);">
                                複製
                            </button>
                        </div>
                        <div class="form-text">此連結不受可見性限制，任何人拿到連結都能看到作品（noindex）</div>
                    </div>
                    @endif

                    @php
                        $currentCat = old('category', $project->category);
                        $isCustomCat = $currentCat && !$categories->contains($currentCat);
                    @endphp
                    <div class="mb-3">
                        <label for="categorySelect" class="form-label">分類</label>
                        <select class="form-select @error('category') is-invalid @enderror"
                                id="categorySelect">
                            <option value="">— 請選擇分類 —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ $currentCat == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                            <option value="__custom__" {{ $isCustomCat ? 'selected' : '' }}>＋ 自訂新分類...</option>
                        </select>
                        <input type="text"
                               class="form-control mt-2 @error('category') is-invalid @enderror"
                               id="categoryCustom"
                               name="category"
                               value="{{ $currentCat }}"
                               placeholder="輸入新分類名稱"
                               style="{{ $isCustomCat ? '' : 'display:none;' }}">
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
                               value="{{ old('client', $project->client) }}">
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
                               value="{{ old('url', $project->url) }}"
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
                               value="{{ old('github_url', $project->github_url) }}"
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
                               value="{{ old('tech_stack', is_array($project->tech_stack) ? implode(', ', $project->tech_stack) : $project->tech_stack) }}"
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
                               value="{{ old('completed_at', $project->completed_at ? $project->completed_at->format('Y-m-d') : '') }}">
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
                                   {{ old('is_featured', $project->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                設為精選作品
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="order" class="form-label">排序</label>
                        <input type="number" class="form-control" id="order" name="order"
                               value="{{ old('order', $project->order + 1) }}" min="1">
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
                            更新
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
{{-- SortableJS CDN for gallery drag-and-drop --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
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

    /* ===== Gallery Management ===== */
    (function() {
        const projectId = {{ $project->id }};
        const csrfToken = '{{ csrf_token() }}';
        const galleryGrid = document.getElementById('galleryGrid');
        const galleryCount = document.getElementById('galleryCount');
        const galleryAddBtn = document.getElementById('galleryAddBtn');

        // Initialize SortableJS
        if (galleryGrid) {
            new Sortable(galleryGrid, {
                animation: 150,
                ghostClass: 'opacity-50',
                handle: '.gallery-item',
                onEnd: function() {
                    saveGalleryOrder();
                    updateCoverBadge();
                }
            });
        }

        // 「從媒體庫新增圖片」按鈕 → 開啟多選模式
        if (galleryAddBtn) {
            galleryAddBtn.addEventListener('click', function() {
                openMediaPickerMulti(function(urls) {
                    // 依序 AJAX 新增每張圖片
                    urls.forEach(function(url) {
                        addGalleryImage(url);
                    });
                });
            });
        }

        // 刪除圖片 — 事件委派
        galleryGrid?.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.gallery-delete-btn');
            if (!deleteBtn) return;
            if (!confirm('確定要刪除此圖片嗎？')) return;

            const imageId = deleteBtn.dataset.id;
            fetch(`{{ url(config('admin.prefix', 'admin')) }}/gallery/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    deleteBtn.closest('.gallery-item').remove();
                    updateGalleryCount();
                    updateCoverBadge();
                }
            })
            .catch(err => console.error('刪除圖片失敗:', err));
        });

        // Caption 更新 — 失焦時儲存
        galleryGrid?.addEventListener('focusout', function(e) {
            if (!e.target.classList.contains('gallery-caption-input')) return;

            const imageId = e.target.dataset.id;
            const caption = e.target.value.trim();

            fetch(`{{ url(config('admin.prefix', 'admin')) }}/gallery/${imageId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ caption: caption })
            })
            .catch(err => console.error('更新 caption 失敗:', err));
        });

        function addGalleryImage(url) {
            fetch(`{{ url(config('admin.prefix', 'admin')) }}/projects/${projectId}/gallery`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ image_url: url })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.image) {
                    appendGalleryItem(data.image);
                    updateGalleryCount();
                    updateCoverBadge();
                }
            })
            .catch(err => console.error('新增圖片失敗:', err));
        }

        function appendGalleryItem(image) {
            const col = document.createElement('div');
            col.className = 'col-6 col-sm-4 col-md-3 gallery-item';
            col.dataset.id = image.id;
            col.innerHTML = `
                <div class="position-relative border rounded overflow-hidden" style="aspect-ratio: 4/3;">
                    <img src="${image.image_url}" alt="${image.alt_text || ''}" class="w-100 h-100" style="object-fit: cover;">
                    <button type="button"
                            class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 gallery-delete-btn"
                            data-id="${image.id}"
                            style="padding: 2px 6px; font-size: 0.7rem; line-height: 1;"
                            title="刪除圖片">
                        &times;
                    </button>
                </div>
                <div class="mt-1">
                    <input type="text"
                           class="form-control form-control-sm gallery-caption-input"
                           data-id="${image.id}"
                           value=""
                           placeholder="圖片說明 (選填)">
                </div>
            `;
            galleryGrid.appendChild(col);
        }

        function saveGalleryOrder() {
            const items = galleryGrid.querySelectorAll('.gallery-item');
            const ids = Array.from(items).map(el => parseInt(el.dataset.id));

            fetch(`{{ url(config('admin.prefix', 'admin')) }}/projects/${projectId}/gallery/reorder`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ids: ids })
            })
            .catch(err => console.error('排序失敗:', err));
        }

        function updateGalleryCount() {
            const count = galleryGrid.querySelectorAll('.gallery-item').length;
            if (galleryCount) galleryCount.textContent = count + ' 張';
        }

        function updateCoverBadge() {
            // 移除所有「封面」badge
            galleryGrid.querySelectorAll('.badge.bg-primary').forEach(b => b.remove());
            // 在第一張加上「封面」badge
            const firstItem = galleryGrid.querySelector('.gallery-item');
            if (firstItem) {
                const imgContainer = firstItem.querySelector('.position-relative');
                if (imgContainer && !imgContainer.querySelector('.badge.bg-primary')) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-primary position-absolute top-0 start-0 m-1';
                    badge.style.fontSize = '0.7rem';
                    badge.textContent = '封面';
                    imgContainer.appendChild(badge);
                }
            }
        }
    })();
</script>
@endpush
@endsection
