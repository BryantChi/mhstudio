@extends('layouts.admin')

@section('title', 'SEO 設定')

@php
    $breadcrumbs = [
        ['title' => '系統設定', 'url' => '#'],
        ['title' => 'SEO 設定', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">SEO 設定</h2>
        <p class="text-muted">配置搜尋引擎優化相關設定</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.seo.update') }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong>預設 Meta Tags</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="default_meta_title" class="form-label">預設 Meta 標題</label>
                        <input type="text"
                               class="form-control @error('default_meta_title') is-invalid @enderror"
                               id="default_meta_title"
                               name="default_meta_title"
                               value="{{ old('default_meta_title', setting('default_meta_title', '')) }}"
                               maxlength="60">
                        <div class="form-text">留空將使用網站名稱，建議 50-60 字元</div>
                        @error('default_meta_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="default_meta_description" class="form-label">預設 Meta 描述</label>
                        <textarea class="form-control @error('default_meta_description') is-invalid @enderror"
                                  id="default_meta_description"
                                  name="default_meta_description"
                                  rows="3"
                                  maxlength="160">{{ old('default_meta_description', setting('default_meta_description', '')) }}</textarea>
                        <div class="form-text">留空將使用網站描述，建議 150-160 字元</div>
                        @error('default_meta_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="default_meta_keywords" class="form-label">預設 Meta 關鍵字</label>
                        <input type="text"
                               class="form-control @error('default_meta_keywords') is-invalid @enderror"
                               id="default_meta_keywords"
                               name="default_meta_keywords"
                               value="{{ old('default_meta_keywords', setting('default_meta_keywords', '')) }}">
                        <div class="form-text">多個關鍵字用逗號分隔</div>
                        @error('default_meta_keywords')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="default_og_image" class="form-label">預設 OG 圖片網址</label>
                        <input type="url"
                               class="form-control @error('default_og_image') is-invalid @enderror"
                               id="default_og_image"
                               name="default_og_image"
                               value="{{ old('default_og_image', setting('default_og_image', '')) }}">
                        <div class="form-text">用於社群分享，建議尺寸 1200x630 像素</div>
                        @error('default_og_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>社群媒體</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="facebook_app_id" class="form-label">Facebook App ID</label>
                        <input type="text"
                               class="form-control @error('facebook_app_id') is-invalid @enderror"
                               id="facebook_app_id"
                               name="facebook_app_id"
                               value="{{ old('facebook_app_id', setting('facebook_app_id', '')) }}">
                        @error('facebook_app_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="twitter_username" class="form-label">Twitter 用戶名</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text"
                                   class="form-control @error('twitter_username') is-invalid @enderror"
                                   id="twitter_username"
                                   name="twitter_username"
                                   value="{{ old('twitter_username', setting('twitter_username', '')) }}">
                        </div>
                        @error('twitter_username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="twitter_card_type" class="form-label">Twitter Card 類型</label>
                        <select class="form-select @error('twitter_card_type') is-invalid @enderror"
                                id="twitter_card_type"
                                name="twitter_card_type">
                            <option value="summary" {{ old('twitter_card_type', setting('twitter_card_type', 'summary')) == 'summary' ? 'selected' : '' }}>Summary</option>
                            <option value="summary_large_image" {{ old('twitter_card_type', setting('twitter_card_type')) == 'summary_large_image' ? 'selected' : '' }}>Summary Large Image</option>
                        </select>
                        @error('twitter_card_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>搜尋引擎驗證</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="google_verification" class="form-label">Google Search Console 驗證碼</label>
                        <input type="text"
                               class="form-control @error('google_verification') is-invalid @enderror"
                               id="google_verification"
                               name="google_verification"
                               value="{{ old('google_verification', setting('google_verification', '')) }}"
                               placeholder="例如: 1234567890abcdef">
                        <div class="form-text">
                            <meta> 標籤中的 content 值
                        </div>
                        @error('google_verification')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="bing_verification" class="form-label">Bing Webmaster Tools 驗證碼</label>
                        <input type="text"
                               class="form-control @error('bing_verification') is-invalid @enderror"
                               id="bing_verification"
                               name="bing_verification"
                               value="{{ old('bing_verification', setting('bing_verification', '')) }}">
                        @error('bing_verification')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="yandex_verification" class="form-label">Yandex Webmaster 驗證碼</label>
                        <input type="text"
                               class="form-control @error('yandex_verification') is-invalid @enderror"
                               id="yandex_verification"
                               name="yandex_verification"
                               value="{{ old('yandex_verification', setting('yandex_verification', '')) }}">
                        @error('yandex_verification')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>結構化資料 (Schema.org)</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="enable_schema" value="0">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="enable_schema"
                                   name="enable_schema"
                                   value="1"
                                   {{ old('enable_schema', setting('enable_schema', '1')) == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="enable_schema">
                                啟用 Schema.org 標記
                            </label>
                        </div>
                        <small class="text-muted">自動為文章添加結構化資料標記</small>
                    </div>

                    <div class="mb-3">
                        <label for="schema_type" class="form-label">預設 Schema 類型</label>
                        <select class="form-select @error('schema_type') is-invalid @enderror"
                                id="schema_type"
                                name="schema_type">
                            <option value="Article" {{ old('schema_type', setting('schema_type', 'Article')) == 'Article' ? 'selected' : '' }}>Article</option>
                            <option value="BlogPosting" {{ old('schema_type', setting('schema_type')) == 'BlogPosting' ? 'selected' : '' }}>BlogPosting</option>
                            <option value="NewsArticle" {{ old('schema_type', setting('schema_type')) == 'NewsArticle' ? 'selected' : '' }}>NewsArticle</option>
                        </select>
                        @error('schema_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="organization_name" class="form-label">組織名稱</label>
                        <input type="text"
                               class="form-control @error('organization_name') is-invalid @enderror"
                               id="organization_name"
                               name="organization_name"
                               value="{{ old('organization_name', setting('organization_name', '')) }}">
                        @error('organization_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="organization_logo" class="form-label">組織 Logo 網址</label>
                        <input type="url"
                               class="form-control @error('organization_logo') is-invalid @enderror"
                               id="organization_logo"
                               name="organization_logo"
                               value="{{ old('organization_logo', setting('organization_logo', '')) }}">
                        @error('organization_logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong>索引設定</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="allow_indexing" value="0">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="allow_indexing"
                                   name="allow_indexing"
                                   value="1"
                                   {{ old('allow_indexing', setting('allow_indexing', '1')) == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_indexing">
                                <strong>允許搜尋引擎索引</strong>
                            </label>
                        </div>
                        <small class="text-muted">關閉後網站將不會被搜尋引擎收錄</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="auto_generate_meta" value="0">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="auto_generate_meta"
                                   name="auto_generate_meta"
                                   value="1"
                                   {{ old('auto_generate_meta', setting('auto_generate_meta', '1')) == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_generate_meta">
                                自動生成 Meta Tags
                            </label>
                        </div>
                        <small class="text-muted">為缺少 Meta 的頁面自動生成</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="generate_canonical" value="0">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="generate_canonical"
                                   name="generate_canonical"
                                   value="1"
                                   {{ old('generate_canonical', setting('generate_canonical', '1')) == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="generate_canonical">
                                自動生成 Canonical URL
                            </label>
                        </div>
                        <small class="text-muted">避免重複內容問題</small>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>Sitemap 設定</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="sitemap_priority" class="form-label">預設優先級</label>
                        <input type="range"
                               class="form-range"
                               id="sitemap_priority"
                               name="sitemap_priority"
                               min="0"
                               max="1"
                               step="0.1"
                               value="{{ old('sitemap_priority', setting('sitemap_priority', 0.5)) }}"
                               oninput="this.nextElementSibling.value = this.value">
                        <output>{{ old('sitemap_priority', setting('sitemap_priority', 0.5)) }}</output>
                    </div>

                    <div class="mb-3">
                        <label for="sitemap_changefreq" class="form-label">預設更新頻率</label>
                        <select class="form-select"
                                id="sitemap_changefreq"
                                name="sitemap_changefreq">
                            <option value="always" {{ old('sitemap_changefreq', setting('sitemap_changefreq', 'weekly')) == 'always' ? 'selected' : '' }}>隨時</option>
                            <option value="hourly" {{ old('sitemap_changefreq', setting('sitemap_changefreq')) == 'hourly' ? 'selected' : '' }}>每小時</option>
                            <option value="daily" {{ old('sitemap_changefreq', setting('sitemap_changefreq')) == 'daily' ? 'selected' : '' }}>每天</option>
                            <option value="weekly" {{ old('sitemap_changefreq', setting('sitemap_changefreq')) == 'weekly' ? 'selected' : '' }}>每週</option>
                            <option value="monthly" {{ old('sitemap_changefreq', setting('sitemap_changefreq')) == 'monthly' ? 'selected' : '' }}>每月</option>
                            <option value="yearly" {{ old('sitemap_changefreq', setting('sitemap_changefreq')) == 'yearly' ? 'selected' : '' }}>每年</option>
                            <option value="never" {{ old('sitemap_changefreq', setting('sitemap_changefreq')) == 'never' ? 'selected' : '' }}>從不</option>
                        </select>
                    </div>

                    <div class="d-grid">
                        <a href="{{ route('admin.seo.sitemap-settings') }}" class="btn btn-outline-primary">
                            進階 Sitemap 設定
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>快捷連結</strong>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.seo.meta') }}" class="btn btn-outline-secondary btn-sm">
                            Meta Tags 管理
                        </a>
                        <a href="{{ route('admin.seo.robots-txt') }}" class="btn btn-outline-secondary btn-sm">
                            編輯 Robots.txt
                        </a>
                        <a href="{{ route('admin.seo.analyze') }}" class="btn btn-outline-secondary btn-sm">
                            SEO 分析
                        </a>
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
@endsection
