@extends('layouts.admin')

@section('title', '前台設定')

@php
    $breadcrumbs = [
        ['title' => '系統設定', 'url' => '#'],
        ['title' => '前台設定', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">前台設定</h2>
        <p class="text-muted">管理前台首頁內容、統計數據、聯繫資訊與社群連結</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.frontend.update') }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            {{-- Hero 設定 --}}
            <div class="card">
                <div class="card-header">
                    <strong>Hero 設定</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="hero_title" class="form-label">標題 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('hero_title') is-invalid @enderror"
                               id="hero_title"
                               name="hero_title"
                               value="{{ old('hero_title', $settings['hero_title'] ?? 'MH STUDIO') }}"
                               required>
                        @error('hero_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="hero_subtitle" class="form-label">副標題（中文）<span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('hero_subtitle') is-invalid @enderror"
                               id="hero_subtitle"
                               name="hero_subtitle"
                               value="{{ old('hero_subtitle', $settings['hero_subtitle'] ?? '孟 衡 工 作 室') }}"
                               required>
                        @error('hero_subtitle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="hero_tagline" class="form-label">標語 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('hero_tagline') is-invalid @enderror"
                               id="hero_tagline"
                               name="hero_tagline"
                               value="{{ old('hero_tagline', $settings['hero_tagline'] ?? 'Balance • Precision • Innovation') }}"
                               required>
                        <div class="form-text">顯示在標題下方的標語文字</div>
                        @error('hero_tagline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="hero_description" class="form-label">描述文字 <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('hero_description') is-invalid @enderror"
                                  id="hero_description"
                                  name="hero_description"
                                  rows="3"
                                  required>{{ old('hero_description', $settings['hero_description'] ?? '專注 App 開發與網頁設計，以精準技術與創新思維，為您打造超越期待的數位產品體驗。') }}</textarea>
                        @error('hero_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 數據統計 --}}
            <div class="card mt-3">
                <div class="card-header">
                    <strong>數據統計</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stats_years_experience" class="form-label">年開發經驗 <span class="text-danger">*</span></label>
                            <input type="number"
                                   class="form-control @error('stats_years_experience') is-invalid @enderror"
                                   id="stats_years_experience"
                                   name="stats_years_experience"
                                   value="{{ old('stats_years_experience', $settings['stats_years_experience'] ?? 7) }}"
                                   min="0"
                                   required>
                            @error('stats_years_experience')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="stats_projects_completed" class="form-label">完成專案數 <span class="text-danger">*</span></label>
                            <input type="number"
                                   class="form-control @error('stats_projects_completed') is-invalid @enderror"
                                   id="stats_projects_completed"
                                   name="stats_projects_completed"
                                   value="{{ old('stats_projects_completed', $settings['stats_projects_completed'] ?? 50) }}"
                                   min="0"
                                   required>
                            @error('stats_projects_completed')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stats_happy_clients" class="form-label">滿意客戶數 <span class="text-danger">*</span></label>
                            <input type="number"
                                   class="form-control @error('stats_happy_clients') is-invalid @enderror"
                                   id="stats_happy_clients"
                                   name="stats_happy_clients"
                                   value="{{ old('stats_happy_clients', $settings['stats_happy_clients'] ?? 30) }}"
                                   min="0"
                                   required>
                            @error('stats_happy_clients')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="stats_ontime_delivery" class="form-label">準時交付 (%) <span class="text-danger">*</span></label>
                            <input type="number"
                                   class="form-control @error('stats_ontime_delivery') is-invalid @enderror"
                                   id="stats_ontime_delivery"
                                   name="stats_ontime_delivery"
                                   value="{{ old('stats_ontime_delivery', $settings['stats_ontime_delivery'] ?? 99) }}"
                                   min="0"
                                   max="100"
                                   required>
                            @error('stats_ontime_delivery')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- 聯繫資訊 --}}
            <div class="card mt-3">
                <div class="card-header">
                    <strong>聯繫資訊</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email"
                                   class="form-control @error('contact_email') is-invalid @enderror"
                                   id="contact_email"
                                   name="contact_email"
                                   value="{{ old('contact_email', $settings['contact_email'] ?? 'bryantchi.work@gmail.com') }}"
                                   required>
                            @error('contact_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="contact_location" class="form-label">地點 <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('contact_location') is-invalid @enderror"
                                   id="contact_location"
                                   name="contact_location"
                                   value="{{ old('contact_location', $settings['contact_location'] ?? '台中市，台灣') }}"
                                   required>
                            @error('contact_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- 社群連結 --}}
            <div class="card mt-3">
                <div class="card-header">
                    <strong>社群連結</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3" style="font-size: 13px;">設定社群平台連結，開啟右方開關後連結才會顯示在前台 Footer。</p>

                    {{-- GitHub --}}
                    <div class="mb-3">
                        <label for="social_github" class="form-label">GitHub 連結</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text"
                                   class="form-control @error('social_github') is-invalid @enderror"
                                   id="social_github"
                                   name="social_github"
                                   value="{{ old('social_github', $settings['social_github'] ?? '#') }}"
                                   placeholder="https://github.com/username">
                            <div class="form-check form-switch flex-shrink-0" style="min-width: 50px;">
                                <input type="hidden" name="social_github_enabled" value="0">
                                <input class="form-check-input" type="checkbox" id="social_github_enabled" name="social_github_enabled" value="1"
                                       {{ old('social_github_enabled', $settings['social_github_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>
                        @error('social_github')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- LinkedIn --}}
                    <div class="mb-3">
                        <label for="social_linkedin" class="form-label">LinkedIn 連結</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text"
                                   class="form-control @error('social_linkedin') is-invalid @enderror"
                                   id="social_linkedin"
                                   name="social_linkedin"
                                   value="{{ old('social_linkedin', $settings['social_linkedin'] ?? '#') }}"
                                   placeholder="https://linkedin.com/in/username">
                            <div class="form-check form-switch flex-shrink-0" style="min-width: 50px;">
                                <input type="hidden" name="social_linkedin_enabled" value="0">
                                <input class="form-check-input" type="checkbox" id="social_linkedin_enabled" name="social_linkedin_enabled" value="1"
                                       {{ old('social_linkedin_enabled', $settings['social_linkedin_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>
                        @error('social_linkedin')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- LINE --}}
                    <div class="mb-3">
                        <label for="social_line" class="form-label">LINE 連結</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text"
                                   class="form-control @error('social_line') is-invalid @enderror"
                                   id="social_line"
                                   name="social_line"
                                   value="{{ old('social_line', $settings['social_line'] ?? '#') }}"
                                   placeholder="https://line.me/ti/p/@username">
                            <div class="form-check form-switch flex-shrink-0" style="min-width: 50px;">
                                <input type="hidden" name="social_line_enabled" value="0">
                                <input class="form-check-input" type="checkbox" id="social_line_enabled" name="social_line_enabled" value="1"
                                       {{ old('social_line_enabled', $settings['social_line_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>
                        @error('social_line')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="line_id" class="form-label">LINE 官方帳號 ID</label>
                        <input type="text"
                               class="form-control @error('line_id') is-invalid @enderror"
                               id="line_id"
                               name="line_id"
                               value="{{ old('line_id', $settings['line_id'] ?? '') }}"
                               placeholder="@mengheng.io">
                        <div class="form-text">顯示在前台聯繫區域的 LINE ID 文字</div>
                        @error('line_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="line_qrcode_url" class="form-label">LINE QR Code 圖片網址</label>
                        <input type="text"
                               class="form-control @error('line_qrcode_url') is-invalid @enderror"
                               id="line_qrcode_url"
                               name="line_qrcode_url"
                               value="{{ old('line_qrcode_url', $settings['line_qrcode_url'] ?? '') }}"
                               placeholder="https://qr-official.line.me/... 或 /images/line-qr.png">
                        <div class="form-text">LINE 官方帳號 QR Code 圖片（支援外部 URL 或本站路徑）。前台 Contact 區域與 Footer 會顯示。</div>
                        @error('line_qrcode_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(setting('line_qrcode_url'))
                        <div class="mt-2">
                            <img src="{{ setting('line_qrcode_url') }}" alt="LINE QR Code Preview" style="max-width: 120px; border: 1px solid #dee2e6; border-radius: 4px;">
                        </div>
                        @endif
                    </div>

                    {{-- Facebook --}}
                    <div class="mb-3">
                        <label for="social_facebook" class="form-label">Facebook 連結</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text"
                                   class="form-control @error('social_facebook') is-invalid @enderror"
                                   id="social_facebook"
                                   name="social_facebook"
                                   value="{{ old('social_facebook', $settings['social_facebook'] ?? '#') }}"
                                   placeholder="https://facebook.com/pagename">
                            <div class="form-check form-switch flex-shrink-0" style="min-width: 50px;">
                                <input type="hidden" name="social_facebook_enabled" value="0">
                                <input class="form-check-input" type="checkbox" id="social_facebook_enabled" name="social_facebook_enabled" value="1"
                                       {{ old('social_facebook_enabled', $settings['social_facebook_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>
                        @error('social_facebook')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Twitter/X --}}
                    <div class="mb-3">
                        <label for="social_twitter" class="form-label">Twitter / X 連結</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text"
                                   class="form-control @error('social_twitter') is-invalid @enderror"
                                   id="social_twitter"
                                   name="social_twitter"
                                   value="{{ old('social_twitter', $settings['social_twitter'] ?? '#') }}"
                                   placeholder="https://x.com/username">
                            <div class="form-check form-switch flex-shrink-0" style="min-width: 50px;">
                                <input type="hidden" name="social_twitter_enabled" value="0">
                                <input class="form-check-input" type="checkbox" id="social_twitter_enabled" name="social_twitter_enabled" value="1"
                                       {{ old('social_twitter_enabled', $settings['social_twitter_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>
                        @error('social_twitter')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Instagram --}}
                    <div class="mb-3">
                        <label for="social_instagram" class="form-label">Instagram 連結</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text"
                                   class="form-control @error('social_instagram') is-invalid @enderror"
                                   id="social_instagram"
                                   name="social_instagram"
                                   value="{{ old('social_instagram', $settings['social_instagram'] ?? '#') }}"
                                   placeholder="https://instagram.com/username">
                            <div class="form-check form-switch flex-shrink-0" style="min-width: 50px;">
                                <input type="hidden" name="social_instagram_enabled" value="0">
                                <input class="form-check-input" type="checkbox" id="social_instagram_enabled" name="social_instagram_enabled" value="1"
                                       {{ old('social_instagram_enabled', $settings['social_instagram_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>
                        @error('social_instagram')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- YouTube --}}
                    <div class="mb-3">
                        <label for="social_youtube" class="form-label">YouTube 連結</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text"
                                   class="form-control @error('social_youtube') is-invalid @enderror"
                                   id="social_youtube"
                                   name="social_youtube"
                                   value="{{ old('social_youtube', $settings['social_youtube'] ?? '#') }}"
                                   placeholder="https://youtube.com/@channelname">
                            <div class="form-check form-switch flex-shrink-0" style="min-width: 50px;">
                                <input type="hidden" name="social_youtube_enabled" value="0">
                                <input class="form-check-input" type="checkbox" id="social_youtube_enabled" name="social_youtube_enabled" value="1"
                                       {{ old('social_youtube_enabled', $settings['social_youtube_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>
                        @error('social_youtube')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 社群嵌入 --}}
            <div class="card mt-3">
                <div class="card-header">
                    <strong>社群嵌入</strong>
                    <small class="text-muted ms-2">首頁 Instagram / YouTube 嵌入區塊</small>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="social_embed_enabled" value="0">
                            <input class="form-check-input" type="checkbox" id="social_embed_enabled" name="social_embed_enabled" value="1"
                                   {{ old('social_embed_enabled', $settings['social_embed_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="social_embed_enabled">啟用首頁社群嵌入區塊</label>
                        </div>
                        <div class="form-text">開啟後，首頁將在 Blog 區塊與 Contact 區塊之間顯示社群嵌入內容。</div>
                    </div>

                    <div class="mb-3">
                        <label for="social_youtube_embed" class="form-label">YouTube 影片 URL</label>
                        <input type="text"
                               class="form-control @error('social_youtube_embed') is-invalid @enderror"
                               id="social_youtube_embed"
                               name="social_youtube_embed"
                               value="{{ old('social_youtube_embed', $settings['social_youtube_embed'] ?? '') }}"
                               placeholder="https://www.youtube.com/watch?v=VIDEO_ID">
                        <div class="form-text">貼上 YouTube 影片網址，系統將自動嵌入播放器。</div>
                        @error('social_youtube_embed')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="social_instagram_embed" class="form-label">Instagram 貼文 URL</label>
                        <input type="text"
                               class="form-control @error('social_instagram_embed') is-invalid @enderror"
                               id="social_instagram_embed"
                               name="social_instagram_embed"
                               value="{{ old('social_instagram_embed', $settings['social_instagram_embed'] ?? '') }}"
                               placeholder="https://www.instagram.com/p/POST_ID/">
                        <div class="form-text">貼上 Instagram 貼文網址，系統將自動嵌入顯示。</div>
                        @error('social_instagram_embed')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 技術棧 --}}
            <div class="card mt-3">
                <div class="card-header">
                    <strong>技術棧</strong>
                </div>
                <div class="card-body">
                    <div id="tech-stack-fields">
                        @php
                            $techStack = old('tech_stack_names')
                                ? collect(old('tech_stack_names'))->map(fn($n, $i) => ['name' => $n, 'type' => old('tech_stack_types')[$i] ?? ''])
                                : collect($settings['tech_stack'] ?? []);
                        @endphp
                        @foreach($techStack as $tech)
                        <div class="row mb-2 tech-stack-item">
                            <div class="col-5">
                                <input type="text" name="tech_stack_names[]" class="form-control" placeholder="技術名稱" value="{{ $tech['name'] ?? '' }}">
                            </div>
                            <div class="col-5">
                                <input type="text" name="tech_stack_types[]" class="form-control" placeholder="類型 (如 Android, Backend)" value="{{ $tech['type'] ?? '' }}">
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-outline-danger remove-tech-stack">
                                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-tech-stack">
                        <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg>
                        新增技術
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- 預覽說明 --}}
            <div class="card">
                <div class="card-header">
                    <strong>說明</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3" style="font-size: 14px;">
                        此頁面管理前台首頁顯示的所有動態內容，包含：
                    </p>
                    <ul class="text-muted" style="font-size: 13px;">
                        <li class="mb-1">Hero 區域的標題與描述</li>
                        <li class="mb-1">統計數據（經驗年數、專案數等）</li>
                        <li class="mb-1">聯繫資訊（Email、地點）</li>
                        <li class="mb-1">社群連結（GitHub、LinkedIn、LINE、Facebook、Twitter/X、Instagram、YouTube）</li>
                        <li class="mb-1">社群嵌入（YouTube / Instagram）</li>
                        <li class="mb-1">技術棧列表</li>
                    </ul>
                    <hr>
                    <p class="text-muted mb-0" style="font-size: 13px;">
                        <strong>提示：</strong>修改後設定將即時反映在前台頁面上。
                    </p>
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
                            儲存設定
                        </button>
                        <button type="reset" class="btn btn-light">重置</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    // Tech Stack 動態新增/移除
    document.getElementById('add-tech-stack').addEventListener('click', function() {
        const container = document.getElementById('tech-stack-fields');
        const row = document.createElement('div');
        row.className = 'row mb-2 tech-stack-item';
        row.innerHTML = `
            <div class="col-5">
                <input type="text" name="tech_stack_names[]" class="form-control" placeholder="技術名稱">
            </div>
            <div class="col-5">
                <input type="text" name="tech_stack_types[]" class="form-control" placeholder="類型 (如 Android, Backend)">
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-outline-danger remove-tech-stack">
                    <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg>
                </button>
            </div>
        `;
        container.appendChild(row);
        row.querySelector('input').focus();
    });

    document.getElementById('tech-stack-fields').addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-tech-stack');
        if (btn) btn.closest('.tech-stack-item').remove();
    });
</script>
@endpush
@endsection
