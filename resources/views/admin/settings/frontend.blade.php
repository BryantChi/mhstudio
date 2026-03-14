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
                                   value="{{ old('contact_email', $settings['contact_email'] ?? 'hello@mhstudio.dev') }}"
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
                    <div class="mb-3">
                        <label for="social_github" class="form-label">GitHub 連結</label>
                        <input type="text"
                               class="form-control @error('social_github') is-invalid @enderror"
                               id="social_github"
                               name="social_github"
                               value="{{ old('social_github', $settings['social_github'] ?? '#') }}"
                               placeholder="https://github.com/username">
                        @error('social_github')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="social_linkedin" class="form-label">LinkedIn 連結</label>
                        <input type="text"
                               class="form-control @error('social_linkedin') is-invalid @enderror"
                               id="social_linkedin"
                               name="social_linkedin"
                               value="{{ old('social_linkedin', $settings['social_linkedin'] ?? '#') }}"
                               placeholder="https://linkedin.com/in/username">
                        @error('social_linkedin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="social_line" class="form-label">LINE 連結</label>
                        <input type="text"
                               class="form-control @error('social_line') is-invalid @enderror"
                               id="social_line"
                               name="social_line"
                               value="{{ old('social_line', $settings['social_line'] ?? '#') }}"
                               placeholder="https://line.me/ti/p/@username">
                        @error('social_line')
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
                        <li class="mb-1">社群連結（GitHub、LinkedIn、LINE）</li>
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
