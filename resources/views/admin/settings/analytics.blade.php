@extends('layouts.admin')

@section('title', '分析設定')

@php
    $breadcrumbs = [
        ['title' => '系統設定', 'url' => '#'],
        ['title' => '分析設定', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">分析設定</h2>
        <p class="text-muted">配置網站分析與追蹤工具</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.analytics.update') }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong>Google Analytics</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="google_analytics_enabled"
                                   name="google_analytics_enabled"
                                   value="1"
                                   {{ old('google_analytics_enabled', setting('google_analytics_enabled', false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="google_analytics_enabled">
                                <strong>啟用 Google Analytics</strong>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="google_analytics_id" class="form-label">Tracking ID / Measurement ID</label>
                        <input type="text"
                               class="form-control @error('google_analytics_id') is-invalid @enderror"
                               id="google_analytics_id"
                               name="google_analytics_id"
                               value="{{ old('google_analytics_id', setting('google_analytics_id', '')) }}"
                               placeholder="例如: G-XXXXXXXXXX 或 UA-XXXXXXXXX-X">
                        <div class="form-text">
                            GA4 使用 G-XXXXXXXXXX 格式，Universal Analytics 使用 UA-XXXXXXXXX-X 格式
                        </div>
                        @error('google_analytics_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="google_analytics_version" class="form-label">Analytics 版本</label>
                        <select class="form-select @error('google_analytics_version') is-invalid @enderror"
                                id="google_analytics_version"
                                name="google_analytics_version">
                            <option value="ga4" {{ old('google_analytics_version', setting('google_analytics_version', 'ga4')) == 'ga4' ? 'selected' : '' }}>Google Analytics 4 (推薦)</option>
                            <option value="universal" {{ old('google_analytics_version', setting('google_analytics_version')) == 'universal' ? 'selected' : '' }}>Universal Analytics (舊版)</option>
                        </select>
                        @error('google_analytics_version')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="google_analytics_anonymize"
                                   name="google_analytics_anonymize"
                                   value="1"
                                   {{ old('google_analytics_anonymize', setting('google_analytics_anonymize', true)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="google_analytics_anonymize">
                                匿名化 IP 地址
                            </label>
                        </div>
                        <small class="text-muted">符合 GDPR 隱私規範</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="google_analytics_demographics"
                                   name="google_analytics_demographics"
                                   value="1"
                                   {{ old('google_analytics_demographics', setting('google_analytics_demographics', false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="google_analytics_demographics">
                                啟用人口統計和興趣報告
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>Google Tag Manager</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="google_tag_manager_enabled"
                                   name="google_tag_manager_enabled"
                                   value="1"
                                   {{ old('google_tag_manager_enabled', setting('google_tag_manager_enabled', false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="google_tag_manager_enabled">
                                <strong>啟用 Google Tag Manager</strong>
                            </label>
                        </div>
                        <small class="text-muted">啟用後將取代 Google Analytics 的直接整合</small>
                    </div>

                    <div class="mb-3">
                        <label for="google_tag_manager_id" class="form-label">Container ID</label>
                        <input type="text"
                               class="form-control @error('google_tag_manager_id') is-invalid @enderror"
                               id="google_tag_manager_id"
                               name="google_tag_manager_id"
                               value="{{ old('google_tag_manager_id', setting('google_tag_manager_id', '')) }}"
                               placeholder="例如: GTM-XXXXXXX">
                        @error('google_tag_manager_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>Facebook Pixel</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="facebook_pixel_enabled"
                                   name="facebook_pixel_enabled"
                                   value="1"
                                   {{ old('facebook_pixel_enabled', setting('facebook_pixel_enabled', false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="facebook_pixel_enabled">
                                <strong>啟用 Facebook Pixel</strong>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="facebook_pixel_id" class="form-label">Pixel ID</label>
                        <input type="text"
                               class="form-control @error('facebook_pixel_id') is-invalid @enderror"
                               id="facebook_pixel_id"
                               name="facebook_pixel_id"
                               value="{{ old('facebook_pixel_id', setting('facebook_pixel_id', '')) }}"
                               placeholder="例如: 1234567890123456">
                        @error('facebook_pixel_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>其他追蹤工具</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="hotjar_id" class="form-label">Hotjar Site ID</label>
                        <input type="text"
                               class="form-control @error('hotjar_id') is-invalid @enderror"
                               id="hotjar_id"
                               name="hotjar_id"
                               value="{{ old('hotjar_id', setting('hotjar_id', '')) }}"
                               placeholder="例如: 1234567">
                        @error('hotjar_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="matomo_site_id" class="form-label">Matomo Site ID</label>
                        <input type="text"
                               class="form-control @error('matomo_site_id') is-invalid @enderror"
                               id="matomo_site_id"
                               name="matomo_site_id"
                               value="{{ old('matomo_site_id', setting('matomo_site_id', '')) }}">
                        @error('matomo_site_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="matomo_url" class="form-label">Matomo URL</label>
                        <input type="url"
                               class="form-control @error('matomo_url') is-invalid @enderror"
                               id="matomo_url"
                               name="matomo_url"
                               value="{{ old('matomo_url', setting('matomo_url', '')) }}"
                               placeholder="例如: https://matomo.yourdomain.com/">
                        @error('matomo_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>自訂追蹤碼</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="custom_head_scripts" class="form-label">Head 區塊腳本</label>
                        <textarea class="form-control font-monospace @error('custom_head_scripts') is-invalid @enderror"
                                  id="custom_head_scripts"
                                  name="custom_head_scripts"
                                  rows="5"
                                  placeholder="<script>&#10;// 你的自訂腳本&#10;</script>">{{ old('custom_head_scripts', setting('custom_head_scripts', '')) }}</textarea>
                        <div class="form-text">將在 &lt;/head&gt; 之前插入</div>
                        @error('custom_head_scripts')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="custom_body_scripts" class="form-label">Body 區塊腳本</label>
                        <textarea class="form-control font-monospace @error('custom_body_scripts') is-invalid @enderror"
                                  id="custom_body_scripts"
                                  name="custom_body_scripts"
                                  rows="5"
                                  placeholder="<script>&#10;// 你的自訂腳本&#10;</script>">{{ old('custom_body_scripts', setting('custom_body_scripts', '')) }}</textarea>
                        <div class="form-text">將在 &lt;/body&gt; 之前插入</div>
                        @error('custom_body_scripts')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong>追蹤設定</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="track_admin"
                                   name="track_admin"
                                   value="1"
                                   {{ old('track_admin', setting('track_admin', false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="track_admin">
                                追蹤管理員訪問
                            </label>
                        </div>
                        <small class="text-muted">是否記錄已登入管理員的訪問</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="track_outbound_links"
                                   name="track_outbound_links"
                                   value="1"
                                   {{ old('track_outbound_links', setting('track_outbound_links', true)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="track_outbound_links">
                                追蹤外部連結點擊
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="track_downloads"
                                   name="track_downloads"
                                   value="1"
                                   {{ old('track_downloads', setting('track_downloads', true)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="track_downloads">
                                追蹤文件下載
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="track_404"
                                   name="track_404"
                                   value="1"
                                   {{ old('track_404', setting('track_404', true)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="track_404">
                                追蹤 404 錯誤頁面
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>Cookie 同意</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="cookie_consent_enabled"
                                   name="cookie_consent_enabled"
                                   value="1"
                                   {{ old('cookie_consent_enabled', setting('cookie_consent_enabled', false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cookie_consent_enabled">
                                啟用 Cookie 同意通知
                            </label>
                        </div>
                        <small class="text-muted">符合 GDPR 和 CCPA 規範</small>
                    </div>

                    <div class="mb-3">
                        <label for="cookie_consent_message" class="form-label">同意訊息</label>
                        <textarea class="form-control"
                                  id="cookie_consent_message"
                                  name="cookie_consent_message"
                                  rows="3">{{ old('cookie_consent_message', setting('cookie_consent_message', '本網站使用 Cookie 來提升您的使用體驗。')) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>說明</strong>
                </div>
                <div class="card-body">
                    <h6 class="small">Google Analytics 4</h6>
                    <p class="small text-muted">
                        GA4 是 Google 最新的分析平台，提供更完整的跨平台追蹤和隱私保護功能。
                    </p>

                    <h6 class="small mt-3">Google Tag Manager</h6>
                    <p class="small text-muted">
                        GTM 可以集中管理所有追蹤碼，無需修改程式碼即可添加或更新標籤。
                    </p>

                    <h6 class="small mt-3">隱私政策</h6>
                    <p class="small text-muted mb-0">
                        使用任何追蹤工具前，請確保網站有完整的隱私政策說明。
                    </p>
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
