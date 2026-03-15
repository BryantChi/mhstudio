@extends('layouts.admin')

@section('title', '編輯 SEO Meta')

@php
    $modelName = $seoMeta->model ? ($seoMeta->model->title ?? $seoMeta->model->name ?? 'Unknown') : '(已刪除)';
    $modelType = class_basename($seoMeta->model_type);
    $breadcrumbs = [
        ['title' => 'SEO 管理', 'url' => route('admin.seo.index')],
        ['title' => 'Meta Tags', 'url' => route('admin.seo.meta')],
        ['title' => '編輯: ' . Str::limit($modelName, 20), 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">編輯 SEO Meta</h2>
        <p class="text-muted">
            <span class="badge bg-secondary">{{ $modelType }}</span>
            {{ $modelName }}
        </p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.seo.meta') }}" class="btn btn-light">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-arrow-left"></use>
            </svg>
            返回列表
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.seo.meta.update', $seoMeta) }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            {{-- 基本 Meta Tags --}}
            <div class="card">
                <div class="card-header">
                    <strong>基本 Meta Tags</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="meta_title" class="form-label">Meta 標題 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('meta_title') is-invalid @enderror"
                               id="meta_title"
                               name="meta_title"
                               value="{{ old('meta_title', $seoMeta->meta_title) }}"
                               maxlength="70"
                               required>
                        <div class="form-text">
                            建議 50-60 字元 |
                            <span id="titleCount">{{ mb_strlen($seoMeta->meta_title ?? '') }}</span> / 60
                        </div>
                        @error('meta_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta 描述 <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                  id="meta_description"
                                  name="meta_description"
                                  rows="3"
                                  maxlength="200"
                                  required>{{ old('meta_description', $seoMeta->meta_description) }}</textarea>
                        <div class="form-text">
                            建議 150-160 字元 |
                            <span id="descCount">{{ mb_strlen($seoMeta->meta_description ?? '') }}</span> / 160
                        </div>
                        @error('meta_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="meta_keywords" class="form-label">Meta 關鍵字</label>
                        <input type="text"
                               class="form-control @error('meta_keywords') is-invalid @enderror"
                               id="meta_keywords"
                               name="meta_keywords"
                               value="{{ old('meta_keywords', $seoMeta->meta_keywords) }}">
                        <div class="form-text">多個關鍵字用逗號分隔</div>
                        @error('meta_keywords')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="meta_robots" class="form-label">Robots 指令</label>
                            <select class="form-select @error('meta_robots') is-invalid @enderror"
                                    id="meta_robots"
                                    name="meta_robots">
                                <option value="index, follow" {{ old('meta_robots', $seoMeta->meta_robots) == 'index, follow' ? 'selected' : '' }}>index, follow（預設）</option>
                                <option value="noindex, follow" {{ old('meta_robots', $seoMeta->meta_robots) == 'noindex, follow' ? 'selected' : '' }}>noindex, follow</option>
                                <option value="index, nofollow" {{ old('meta_robots', $seoMeta->meta_robots) == 'index, nofollow' ? 'selected' : '' }}>index, nofollow</option>
                                <option value="noindex, nofollow" {{ old('meta_robots', $seoMeta->meta_robots) == 'noindex, nofollow' ? 'selected' : '' }}>noindex, nofollow</option>
                            </select>
                            @error('meta_robots')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="canonical_url" class="form-label">Canonical URL</label>
                            <input type="url"
                                   class="form-control @error('canonical_url') is-invalid @enderror"
                                   id="canonical_url"
                                   name="canonical_url"
                                   value="{{ old('canonical_url', $seoMeta->canonical_url) }}"
                                   placeholder="https://...">
                            @error('canonical_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Open Graph --}}
            <div class="card mt-3">
                <div class="card-header">
                    <strong>Open Graph（社群分享）</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="og_title" class="form-label">OG 標題</label>
                        <input type="text"
                               class="form-control @error('og_title') is-invalid @enderror"
                               id="og_title"
                               name="og_title"
                               value="{{ old('og_title', $seoMeta->og_title) }}"
                               placeholder="留空使用 Meta 標題">
                        @error('og_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="og_description" class="form-label">OG 描述</label>
                        <textarea class="form-control @error('og_description') is-invalid @enderror"
                                  id="og_description"
                                  name="og_description"
                                  rows="2"
                                  placeholder="留空使用 Meta 描述">{{ old('og_description', $seoMeta->og_description) }}</textarea>
                        @error('og_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="og_image" class="form-label">OG 圖片 URL</label>
                            <input type="text"
                                   class="form-control @error('og_image') is-invalid @enderror"
                                   id="og_image"
                                   name="og_image"
                                   value="{{ old('og_image', $seoMeta->og_image) }}"
                                   placeholder="建議尺寸 1200x630 像素">
                            @error('og_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="og_type" class="form-label">OG 類型</label>
                            <select class="form-select @error('og_type') is-invalid @enderror"
                                    id="og_type"
                                    name="og_type">
                                <option value="website" {{ old('og_type', $seoMeta->og_type ?? 'website') == 'website' ? 'selected' : '' }}>website</option>
                                <option value="article" {{ old('og_type', $seoMeta->og_type) == 'article' ? 'selected' : '' }}>article</option>
                                <option value="product" {{ old('og_type', $seoMeta->og_type) == 'product' ? 'selected' : '' }}>product</option>
                            </select>
                            @error('og_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Twitter Card --}}
            <div class="card mt-3">
                <div class="card-header">
                    <strong>Twitter Card</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="twitter_card" class="form-label">Card 類型</label>
                            <select class="form-select @error('twitter_card') is-invalid @enderror"
                                    id="twitter_card"
                                    name="twitter_card">
                                <option value="summary_large_image" {{ old('twitter_card', $seoMeta->twitter_card ?? 'summary_large_image') == 'summary_large_image' ? 'selected' : '' }}>Summary Large Image</option>
                                <option value="summary" {{ old('twitter_card', $seoMeta->twitter_card) == 'summary' ? 'selected' : '' }}>Summary</option>
                            </select>
                            @error('twitter_card')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="twitter_title" class="form-label">Twitter 標題</label>
                        <input type="text"
                               class="form-control @error('twitter_title') is-invalid @enderror"
                               id="twitter_title"
                               name="twitter_title"
                               value="{{ old('twitter_title', $seoMeta->twitter_title) }}"
                               placeholder="留空使用 Meta 標題">
                        @error('twitter_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="twitter_description" class="form-label">Twitter 描述</label>
                        <textarea class="form-control @error('twitter_description') is-invalid @enderror"
                                  id="twitter_description"
                                  name="twitter_description"
                                  rows="2"
                                  placeholder="留空使用 Meta 描述">{{ old('twitter_description', $seoMeta->twitter_description) }}</textarea>
                        @error('twitter_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="twitter_image" class="form-label">Twitter 圖片 URL</label>
                        <input type="text"
                               class="form-control @error('twitter_image') is-invalid @enderror"
                               id="twitter_image"
                               name="twitter_image"
                               value="{{ old('twitter_image', $seoMeta->twitter_image) }}"
                               placeholder="留空使用 OG 圖片">
                        @error('twitter_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- 搜尋結果預覽 --}}
            <div class="card">
                <div class="card-header">
                    <strong>Google 搜尋預覽</strong>
                </div>
                <div class="card-body">
                    <div style="font-family: Arial, sans-serif; max-width: 600px;">
                        <div id="previewTitle" style="font-size: 18px; color: #1a0dab; line-height: 1.3; margin-bottom: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $seoMeta->meta_title ?: '頁面標題' }}
                        </div>
                        <div style="font-size: 12px; color: #006621; margin-bottom: 4px;">
                            {{ config('app.url') }}
                        </div>
                        <div id="previewDesc" style="font-size: 13px; color: #545454; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $seoMeta->meta_description ?: '頁面描述文字...' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- 內容資訊 --}}
            <div class="card mt-3">
                <div class="card-header">
                    <strong>關聯內容</strong>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">類型</td>
                            <td><span class="badge bg-secondary">{{ $modelType }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">名稱</td>
                            <td>{{ $modelName }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">ID</td>
                            <td>{{ $seoMeta->model_id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">建立時間</td>
                            <td>{{ $seoMeta->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">更新時間</td>
                            <td>{{ $seoMeta->updated_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- 儲存按鈕 --}}
            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="/assets/icons/free.svg#cil-save"></use>
                            </svg>
                            儲存 Meta
                        </button>
                        <a href="{{ route('admin.seo.meta') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    // 即時字元計數 + 預覽
    const titleInput = document.getElementById('meta_title');
    const descInput = document.getElementById('meta_description');
    const titleCount = document.getElementById('titleCount');
    const descCount = document.getElementById('descCount');
    const previewTitle = document.getElementById('previewTitle');
    const previewDesc = document.getElementById('previewDesc');

    titleInput.addEventListener('input', function() {
        titleCount.textContent = this.value.length;
        previewTitle.textContent = this.value || '頁面標題';
        titleCount.style.color = this.value.length > 60 ? '#dc3545' : '';
    });

    descInput.addEventListener('input', function() {
        descCount.textContent = this.value.length;
        previewDesc.textContent = this.value || '頁面描述文字...';
        descCount.style.color = this.value.length > 160 ? '#dc3545' : '';
    });
</script>
@endpush
@endsection
