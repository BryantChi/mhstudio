@extends('layouts.admin')

@section('title', 'Robots.txt 編輯')

@php
    $breadcrumbs = [
        ['title' => 'SEO 管理', 'url' => route('admin.seo.index')],
        ['title' => 'Robots.txt', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">Robots.txt 編輯</h2>
        <p class="text-muted">編輯搜尋引擎爬蟲規則</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ url('robots.txt') }}" target="_blank" class="btn btn-light">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-external-link"></use>
            </svg>
            查看當前 Robots.txt
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.seo.robots-txt.update') }}" id="robots-form">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>編輯模式</strong>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="edit_mode" id="mode_simple" value="simple" checked>
                        <label class="btn btn-outline-secondary" for="mode_simple">簡易模式</label>

                        <input type="radio" class="btn-check" name="edit_mode" id="mode_advanced" value="advanced">
                        <label class="btn btn-outline-secondary" for="mode_advanced">進階模式</label>
                    </div>
                </div>
                <div class="card-body">
                    <!-- 簡易模式 -->
                    <div id="simple-mode">
                        <div class="mb-3">
                            <label class="form-label">允許的搜尋引擎</label>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input user-agent-check" type="checkbox" id="ua_all" value="*" checked>
                                        <label class="form-check-label" for="ua_all">
                                            所有搜尋引擎 (*)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input user-agent-check" type="checkbox" id="ua_googlebot" value="Googlebot">
                                        <label class="form-check-label" for="ua_googlebot">
                                            Google (Googlebot)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input user-agent-check" type="checkbox" id="ua_bingbot" value="Bingbot">
                                        <label class="form-check-label" for="ua_bingbot">
                                            Bing (Bingbot)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input user-agent-check" type="checkbox" id="ua_slurp" value="Slurp">
                                        <label class="form-check-label" for="ua_slurp">
                                            Yahoo (Slurp)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input user-agent-check" type="checkbox" id="ua_duckduckbot" value="DuckDuckBot">
                                        <label class="form-check-label" for="ua_duckduckbot">
                                            DuckDuckGo (DuckDuckBot)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input user-agent-check" type="checkbox" id="ua_baiduspider" value="Baiduspider">
                                        <label class="form-check-label" for="ua_baiduspider">
                                            百度 (Baiduspider)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-text mt-2">選擇允許哪些搜尋引擎爬取網站。「所有搜尋引擎」選中時，其他選項將被忽略。</div>
                        </div>

                        <div class="mb-3">
                            <label for="disallow-paths" class="form-label">禁止訪問的路徑</label>
                            <div id="disallow-paths">
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Disallow: /</span>
                                    <input type="text" class="form-control disallow-path" value="admin" placeholder="admin">
                                    <button type="button" class="btn btn-outline-danger remove-path" disabled>
                                        <svg class="icon">
                                            <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                                        </svg>
                                    </button>
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Disallow: /</span>
                                    <input type="text" class="form-control disallow-path" value="api" placeholder="api">
                                    <button type="button" class="btn btn-outline-danger remove-path" disabled>
                                        <svg class="icon">
                                            <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-path">
                                <svg class="icon me-1">
                                    <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
                                </svg>
                                新增路徑
                            </button>
                        </div>

                        <div class="mb-3">
                            <label for="sitemap-url" class="form-label">Sitemap 位置</label>
                            <input type="url" class="form-control" id="sitemap-url" value="{{ url('sitemap.xml') }}">
                        </div>

                        <div class="mb-3">
                            <label for="crawl-delay" class="form-label">爬蟲延遲（秒）</label>
                            <input type="number" class="form-control" id="crawl-delay" value="0" min="0" max="60">
                            <div class="form-text">設定搜尋引擎爬蟲的訪問間隔，0 表示無延遲</div>
                        </div>
                    </div>

                    <!-- 進階模式 -->
                    <div id="advanced-mode" style="display: none;">
                        <div class="mb-3">
                            <label for="content-advanced" class="form-label">Robots.txt 內容</label>
                            <textarea class="form-control font-monospace"
                                      id="content-advanced"
                                      rows="20"
                                      placeholder="User-agent: *&#10;Disallow: /admin&#10;&#10;Sitemap: {{ url('sitemap.xml') }}">{{ old('content', $content) }}</textarea>
                            <small class="text-muted">直接編輯 robots.txt 完整內容</small>
                        </div>
                    </div>

                    <!-- 實際提交的欄位 -->
                    <textarea name="content" id="content-final" style="display: none;"></textarea>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="/assets/icons/free.svg#cil-save"></use>
                            </svg>
                            儲存變更
                        </button>
                        <a href="{{ route('admin.seo.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong>預覽</strong>
                </div>
                <div class="card-body">
                    <pre class="mb-0 p-3 bg-light rounded" style="max-height: 400px; overflow-y: auto;"><code id="preview-content"># 預覽將顯示在這裡</code></pre>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>快速範本</strong>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary load-preset" data-preset="allow_all">
                            允許所有
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary load-preset" data-preset="block_admin">
                            封鎖後台
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary load-preset" data-preset="standard">
                            標準設定
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const simpleMode = document.getElementById('simple-mode');
    const advancedMode = document.getElementById('advanced-mode');
    const modeSimpleRadio = document.getElementById('mode_simple');
    const modeAdvancedRadio = document.getElementById('mode_advanced');
    const previewContent = document.getElementById('preview-content');
    const contentAdvanced = document.getElementById('content-advanced');
    const addPathBtn = document.getElementById('add-path');
    const disallowPaths = document.getElementById('disallow-paths');

    // 從現有內容解析簡易模式的值
    function parseCurrentContent() {
        const content = @json($content);
        if (!content) return;

        const lines = content.split('\n');
        const paths = [];
        let sitemap = '';
        let delay = 0;

        lines.forEach(line => {
            const trimmed = line.trim();
            if (trimmed.startsWith('Disallow:')) {
                const path = trimmed.substring(9).trim().replace(/^\//, '');
                if (path) paths.push(path);
            } else if (trimmed.startsWith('Sitemap:')) {
                sitemap = trimmed.substring(8).trim();
            } else if (trimmed.startsWith('Crawl-delay:')) {
                delay = parseInt(trimmed.substring(12).trim()) || 0;
            }
        });

        // 清空現有路徑
        disallowPaths.innerHTML = '';

        // 添加解析出的路徑
        paths.forEach(path => addDisallowPath(path));

        // 如果沒有路徑，添加預設的
        if (paths.length === 0) {
            addDisallowPath('admin');
            addDisallowPath('api');
        }

        // 設定 sitemap 和 delay
        if (sitemap) document.getElementById('sitemap-url').value = sitemap;
        document.getElementById('crawl-delay').value = delay;
    }

    // 添加禁止路徑
    function addDisallowPath(value = '') {
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <span class="input-group-text">Disallow: /</span>
            <input type="text" class="form-control disallow-path" value="${value}" placeholder="例如: private">
            <button type="button" class="btn btn-outline-danger remove-path">
                <svg class="icon">
                    <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                </svg>
            </button>
        `;
        disallowPaths.appendChild(div);
        updatePreview();
    }

    // 移除路徑
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-path')) {
            const pathCount = disallowPaths.querySelectorAll('.disallow-path').length;
            if (pathCount > 1) {
                e.target.closest('.input-group').remove();
                updatePreview();
            }
        }
    });

    // 新增路徑按鈕
    addPathBtn.addEventListener('click', function() {
        addDisallowPath();
    });

    // 生成 robots.txt 內容（簡易模式）
    function generateContent() {
        let content = '';

        // 獲取選中的搜尋引擎
        const userAgents = [];
        const allChecked = document.getElementById('ua_all').checked;

        if (allChecked) {
            userAgents.push('*');
        } else {
            document.querySelectorAll('.user-agent-check:not(#ua_all):checked').forEach(checkbox => {
                userAgents.push(checkbox.value);
            });
        }

        // 如果沒有選擇任何搜尋引擎，預設為所有
        if (userAgents.length === 0) {
            userAgents.push('*');
        }

        // 獲取 Disallow 路徑
        const paths = [];
        document.querySelectorAll('.disallow-path').forEach(input => {
            const value = input.value.trim();
            if (value) {
                paths.push(value);
            }
        });

        // 生成每個 User-agent 的規則
        userAgents.forEach((agent, index) => {
            content += `User-agent: ${agent}\n`;

            // 添加 Disallow 路徑
            paths.forEach(path => {
                content += `Disallow: /${path}\n`;
            });

            // 如果不是最後一個 User-agent，添加空行分隔
            if (index < userAgents.length - 1) {
                content += '\n';
            }
        });

        // 添加 Sitemap
        const sitemap = document.getElementById('sitemap-url').value.trim();
        if (sitemap) {
            content += `\nSitemap: ${sitemap}`;
        }

        // 添加 Crawl-delay
        const delay = parseInt(document.getElementById('crawl-delay').value) || 0;
        if (delay > 0) {
            content += `\nCrawl-delay: ${delay}`;
        }

        return content;
    }

    // 更新預覽
    function updatePreview() {
        const mode = document.querySelector('input[name="edit_mode"]:checked').value;
        let content = '';

        if (mode === 'simple') {
            content = generateContent();
        } else {
            content = contentAdvanced.value;
        }

        previewContent.textContent = content || '# 空白內容';
    }

    // 切換模式
    function switchMode() {
        const mode = document.querySelector('input[name="edit_mode"]:checked').value;

        if (mode === 'simple') {
            simpleMode.style.display = 'block';
            advancedMode.style.display = 'none';
        } else {
            simpleMode.style.display = 'none';
            advancedMode.style.display = 'block';
            // 切換到進階模式時，將簡易模式的內容同步過去
            contentAdvanced.value = generateContent();
        }

        updatePreview();
    }

    // 載入範本
    document.querySelectorAll('.load-preset').forEach(btn => {
        btn.addEventListener('click', function() {
            const preset = this.dataset.preset;
            const presets = {
                'allow_all': 'User-agent: *\nAllow: /\n\nSitemap: {{ url("sitemap.xml") }}',
                'block_admin': 'User-agent: *\nDisallow: /admin\nDisallow: /api\n\nSitemap: {{ url("sitemap.xml") }}',
                'standard': 'User-agent: *\nDisallow: /admin\nDisallow: /api\nDisallow: /login\n\nSitemap: {{ url("sitemap.xml") }}'
            };

            if (presets[preset]) {
                contentAdvanced.value = presets[preset];
                if (document.querySelector('input[name="edit_mode"]:checked').value === 'advanced') {
                    updatePreview();
                }
            }
        });
    });

    // 監聽輸入變化
    document.querySelectorAll('.disallow-path, #sitemap-url, #crawl-delay').forEach(input => {
        input.addEventListener('input', updatePreview);
    });

    document.querySelectorAll('.user-agent-check').forEach(checkbox => {
        checkbox.addEventListener('change', updatePreview);
    });

    contentAdvanced.addEventListener('input', updatePreview);

    modeSimpleRadio.addEventListener('change', switchMode);
    modeAdvancedRadio.addEventListener('change', switchMode);

    // 表單提交
    document.getElementById('robots-form').addEventListener('submit', function(e) {
        const mode = document.querySelector('input[name="edit_mode"]:checked').value;
        const finalContent = document.getElementById('content-final');

        if (mode === 'simple') {
            finalContent.value = generateContent();
        } else {
            finalContent.value = contentAdvanced.value;
        }

        console.log('提交內容:', finalContent.value);
    });

    // 初始化
    parseCurrentContent();
    updatePreview();
});
</script>
@endpush
@endsection
