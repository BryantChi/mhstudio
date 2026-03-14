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

<form method="POST" action="{{ route('admin.seo.robots-txt.update') }}">
    @csrf
    @method('PUT')

    <!-- 隱藏的 content 欄位，用於提交資料 -->
    <input type="hidden" name="content" id="content-hidden">

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>編輯模式</strong>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="edit_mode" id="mode_simple" value="simple" checked onclick="switchMode('simple')">
                        <label class="btn btn-outline-secondary" for="mode_simple">簡易模式</label>

                        <input type="radio" class="btn-check" name="edit_mode" id="mode_advanced" value="advanced" onclick="switchMode('advanced')">
                        <label class="btn btn-outline-secondary" for="mode_advanced">進階模式</label>
                    </div>
                </div>
                <div class="card-body">
                    <!-- 簡易模式 -->
                    <div id="simple-mode">
                        <div class="mb-3">
                            <label class="form-label">預設規則</label>
                            <select class="form-select" id="preset" onchange="loadPreset(this.value)">
                                <option value="">選擇預設規則...</option>
                                <option value="allow_all">允許所有爬蟲</option>
                                <option value="block_all">封鎖所有爬蟲</option>
                                <option value="standard">標準規則</option>
                                <option value="strict">嚴格規則</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">允許的爬蟲</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allow_googlebot" name="allowed_bots[]" value="Googlebot" checked>
                                <label class="form-check-label" for="allow_googlebot">Googlebot (Google)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allow_bingbot" name="allowed_bots[]" value="Bingbot" checked>
                                <label class="form-check-label" for="allow_bingbot">Bingbot (Microsoft Bing)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allow_slurp" name="allowed_bots[]" value="Slurp" checked>
                                <label class="form-check-label" for="allow_slurp">Slurp (Yahoo)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allow_duckduckbot" name="allowed_bots[]" value="DuckDuckBot" checked>
                                <label class="form-check-label" for="allow_duckduckbot">DuckDuckBot (DuckDuckGo)</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">禁止訪問的路徑</label>
                            <div id="disallowed-paths">
                                <div class="input-group mb-2">
                                    <span class="input-group-text">/</span>
                                    <input type="text" class="form-control" name="disallowed_paths[]" value="admin" placeholder="例如: admin">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeDisallowedPath(this)">
                                        <svg class="icon">
                                            <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                                        </svg>
                                    </button>
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">/</span>
                                    <input type="text" class="form-control" name="disallowed_paths[]" value="api" placeholder="例如: api">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeDisallowedPath(this)">
                                        <svg class="icon">
                                            <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addDisallowedPath()">
                                <svg class="icon me-1">
                                    <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
                                </svg>
                                新增路徑
                            </button>
                        </div>

                        <div class="mb-3">
                            <label for="sitemap_url" class="form-label">Sitemap 網址</label>
                            <input type="url" class="form-control" id="sitemap_url" name="sitemap_url" value="{{ url('sitemap.xml') }}">
                        </div>

                        <div class="mb-3">
                            <label for="crawl_delay" class="form-label">爬取延遲（秒）</label>
                            <input type="number" class="form-control" id="crawl_delay" name="crawl_delay" value="0" min="0" max="60">
                            <small class="text-muted">0 表示無延遲</small>
                        </div>
                    </div>

                    <!-- 進階模式 -->
                    <div id="advanced-mode" style="display: none;">
                        <div class="mb-3">
                            <label for="content-textarea" class="form-label">Robots.txt 內容</label>
                            <textarea class="form-control font-monospace"
                                      id="content-textarea"
                                      rows="20"
                                      placeholder="User-agent: *&#10;Disallow: /admin&#10;Sitemap: {{ url('sitemap.xml') }}">{{ old('content', $content) }}</textarea>
                            <small class="text-muted">直接編輯 robots.txt 內容</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>預覽</strong>
                </div>
                <div class="card-body">
                    <pre class="mb-0 p-3 bg-light rounded"><code id="preview-content"># 生成的 Robots.txt 將顯示在這裡</code></pre>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong>說明</strong>
                </div>
                <div class="card-body">
                    <h6>什麼是 Robots.txt？</h6>
                    <p class="small">Robots.txt 是一個文本文件，用於告訴搜尋引擎爬蟲哪些頁面可以訪問，哪些不可以。</p>

                    <h6 class="mt-3">常用指令</h6>
                    <dl class="small">
                        <dt>User-agent</dt>
                        <dd>指定爬蟲類型，* 表示所有爬蟲</dd>

                        <dt>Disallow</dt>
                        <dd>禁止訪問的路徑</dd>

                        <dt>Allow</dt>
                        <dd>明確允許訪問的路徑</dd>

                        <dt>Sitemap</dt>
                        <dd>指定 Sitemap 位置</dd>

                        <dt>Crawl-delay</dt>
                        <dd>爬取延遲時間（秒）</dd>
                    </dl>

                    <h6 class="mt-3">範例</h6>
                    <pre class="small bg-light p-2 rounded"><code>User-agent: *
Disallow: /admin
Disallow: /api
Allow: /api/public

User-agent: Googlebot
Crawl-delay: 10

Sitemap: {{ url('sitemap.xml') }}</code></pre>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>驗證工具</strong>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="testRobots()">
                            <svg class="icon me-2">
                                <use xlink:href="/assets/icons/free.svg#cil-check"></use>
                            </svg>
                            語法驗證
                        </button>

                        <a href="https://www.google.com/webmasters/tools/robots-testing-tool" target="_blank" class="btn btn-outline-secondary">
                            <svg class="icon me-2">
                                <use xlink:href="/assets/icons/free.svg#cil-external-link"></use>
                            </svg>
                            Google 驗證工具
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
                            儲存
                        </button>
                        <a href="{{ route('admin.seo.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    function switchMode(mode) {
        if (mode === 'simple') {
            document.getElementById('simple-mode').style.display = 'block';
            document.getElementById('advanced-mode').style.display = 'none';
            updatePreview();
        } else {
            document.getElementById('simple-mode').style.display = 'none';
            document.getElementById('advanced-mode').style.display = 'block';
        }
    }

    function loadPreset(preset) {
        const presets = {
            'allow_all': `User-agent: *\nAllow: /\n\nSitemap: ${window.location.origin}/sitemap.xml`,
            'block_all': `User-agent: *\nDisallow: /`,
            'standard': `User-agent: *\nDisallow: /{{ config('admin.prefix', 'admin') }}\nDisallow: /api\nDisallow: /deploy\n\nSitemap: ${window.location.origin}/sitemap.xml`,
            'strict': `User-agent: *\nDisallow: /{{ config('admin.prefix', 'admin') }}\nDisallow: /api\nDisallow: /deploy\n\nCrawl-delay: 10\n\nSitemap: ${window.location.origin}/sitemap.xml`
        };

        if (presets[preset]) {
            document.getElementById('content-textarea').value = presets[preset];
        }
    }

    function addDisallowedPath() {
        const container = document.getElementById('disallowed-paths');
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <span class="input-group-text">/</span>
            <input type="text" class="form-control" name="disallowed_paths[]" placeholder="例如: private">
            <button type="button" class="btn btn-outline-danger" onclick="removeDisallowedPath(this)">
                <svg class="icon">
                    <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                </svg>
            </button>
        `;
        container.appendChild(div);
    }

    function removeDisallowedPath(button) {
        button.closest('.input-group').remove();
        updatePreview();
    }

    function generateRobotsContent() {
        // 從簡易模式生成內容
        let content = 'User-agent: *\n';

        const paths = document.querySelectorAll('input[name="disallowed_paths[]"]');
        paths.forEach(input => {
            if (input.value.trim()) {
                content += `Disallow: /${input.value.trim()}\n`;
            }
        });

        const sitemapUrl = document.getElementById('sitemap_url').value;
        if (sitemapUrl) {
            content += `\nSitemap: ${sitemapUrl}`;
        }

        const crawlDelay = document.getElementById('crawl_delay').value;
        if (crawlDelay && crawlDelay > 0) {
            content += `\n\nCrawl-delay: ${crawlDelay}`;
        }

        return content;
    }

    function updatePreview() {
        const content = generateRobotsContent();
        document.getElementById('preview-content').textContent = content;
    }

    function testRobots() {
        // 實作語法驗證
        alert('語法驗證功能待實作');
    }

    // 監聽輸入變化更新預覽
    document.addEventListener('DOMContentLoaded', function() {
        // 初始化隱藏欄位和 textarea 的值
        const initialContent = @json($content);
        document.getElementById('content-hidden').value = initialContent;
        document.getElementById('content-textarea').value = initialContent;

        const inputs = document.querySelectorAll('#simple-mode input, #simple-mode select');
        inputs.forEach(input => {
            input.addEventListener('input', updatePreview);
            input.addEventListener('change', updatePreview);
        });
        updatePreview();

        // 表單提交時處理
        document.querySelector('form').addEventListener('submit', function(e) {
            const mode = document.querySelector('input[name="edit_mode"]:checked').value;

            if (mode === 'simple') {
                // 簡單模式：使用共用函數生成內容
                const content = generateRobotsContent();
                document.getElementById('content-hidden').value = content;
            } else {
                // 進階模式：使用 textarea 的值
                const advancedContent = document.getElementById('content-textarea').value;
                document.getElementById('content-hidden').value = advancedContent;
            }

            console.log('Submitting with content:', document.getElementById('content-hidden').value);
        });
    });
</script>
@endpush
@endsection
