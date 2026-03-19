@extends('layouts.admin')

@section('title', '部署工具')

@php
    $breadcrumbs = [
        ['title' => '系統設定', 'url' => route('admin.settings.index')],
        ['title' => '部署工具', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col">
        <h2 class="mb-0">部署工具</h2>
        <p class="text-muted">一鍵部署或個別執行資料庫遷移、快取最佳化等操作</p>
    </div>
</div>

{{-- 一鍵部署 --}}
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <strong>
            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-cloud-upload"></use></svg>
            一鍵部署
        </strong>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">依序執行：Composer Install → 資料庫遷移 → 資料填充 → Storage 連結 → 快取最佳化 → View 快取</p>
        <button type="button" class="btn btn-primary btn-lg deploy-btn" data-action="{{ route('admin.deploy.init') }}">
            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-media-play"></use></svg>
            執行一鍵部署
        </button>
    </div>
</div>

{{-- 個別操作 --}}
<div class="row">
    <div class="col-md-6 col-xl-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <svg class="icon me-2 text-primary"><use xlink:href="/assets/icons/free.svg#cil-terminal"></use></svg>
                    Composer Install
                </h5>
                <p class="card-text text-muted">執行 <code>composer install --no-dev</code>，安裝或更新 PHP 套件依賴。</p>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-outline-primary btn-sm deploy-btn" data-action="{{ route('admin.deploy.composer-install') }}">
                    執行 Composer
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <svg class="icon me-2 text-secondary"><use xlink:href="/assets/icons/free.svg#cil-code"></use></svg>
                    NPM Build
                </h5>
                <p class="card-text text-muted">執行 <code>npm install && npm run build</code>，編譯前端資源（CSS/JS）。</p>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm deploy-btn" data-action="{{ route('admin.deploy.npm-build') }}">
                    執行 NPM Build
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <svg class="icon me-2 text-info"><use xlink:href="/assets/icons/free.svg#cil-storage"></use></svg>
                    資料庫遷移
                </h5>
                <p class="card-text text-muted">執行 <code>php artisan migrate --force</code>，套用新的資料庫變更。</p>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-outline-info btn-sm deploy-btn" data-action="{{ route('admin.deploy.migrate') }}">
                    執行遷移
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <svg class="icon me-2 text-success"><use xlink:href="/assets/icons/free.svg#cil-seed"></use></svg>
                    資料填充
                </h5>
                <p class="card-text text-muted">執行 <code>php artisan db:seed --force</code>，填入預設資料（使用 updateOrCreate 不會覆蓋）。</p>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-outline-success btn-sm deploy-btn" data-action="{{ route('admin.deploy.seed') }}">
                    執行填充
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <svg class="icon me-2 text-warning"><use xlink:href="/assets/icons/free.svg#cil-link"></use></svg>
                    Storage 連結
                </h5>
                <p class="card-text text-muted">執行 <code>php artisan storage:link</code>，建立 public/storage 符號連結。</p>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-outline-warning btn-sm deploy-btn" data-action="{{ route('admin.deploy.storage-link') }}">
                    建立連結
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <svg class="icon me-2 text-danger"><use xlink:href="/assets/icons/free.svg#cil-bolt"></use></svg>
                    快取最佳化
                </h5>
                <p class="card-text text-muted">清除所有快取並重建：config、route、view cache。</p>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-outline-danger btn-sm deploy-btn" data-action="{{ route('admin.deploy.optimize') }}">
                    執行最佳化
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Telescope 管理 --}}
<div class="card mb-4">
    <div class="card-header">
        <strong>
            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-monitor"></use></svg>
            Telescope 除錯工具管理
        </strong>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
                <p class="mb-1">
                    <strong>目前狀態：</strong>
                    @if(config('telescope.enabled'))
                        <span class="badge bg-success">啟用中</span>
                        <small class="text-muted d-block mt-1">Telescope 正在記錄所有請求，會佔用大量資料庫空間。正式環境建議停用。</small>
                    @else
                        <span class="badge bg-secondary">已停用</span>
                        <small class="text-muted d-block mt-1">Telescope 已停用，不會寫入任何記錄。</small>
                    @endif
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <button type="button" class="btn btn-{{ config('telescope.enabled') ? 'warning' : 'success' }} btn-sm deploy-btn" data-action="{{ route('admin.deploy.telescope-toggle') }}">
                    <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-{{ config('telescope.enabled') ? 'ban' : 'check-circle' }}"></use></svg>
                    {{ config('telescope.enabled') ? '停用 Telescope' : '啟用 Telescope' }}
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm deploy-btn ms-2" data-action="{{ route('admin.deploy.telescope-prune') }}">
                    <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg>
                    清理記錄 + 回收空間
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 執行結果 --}}
<div class="card" id="resultCard" style="display:none;">
    <div class="card-header" id="resultHeader">
        <strong>執行結果</strong>
    </div>
    <div class="card-body">
        <pre class="mb-0" id="resultOutput" style="white-space: pre-wrap; font-size: .85rem; max-height: 400px; overflow-y: auto;"></pre>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.deploy-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var action = this.dataset.action;
        var buttonEl = this;
        var originalHtml = buttonEl.innerHTML;

        if (!confirm('確定要執行此操作嗎？')) return;

        // Disable button and show loading
        buttonEl.disabled = true;
        buttonEl.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>執行中...';

        // Show result card
        var resultCard = document.getElementById('resultCard');
        var resultHeader = document.getElementById('resultHeader');
        var resultOutput = document.getElementById('resultOutput');
        resultCard.style.display = 'block';
        resultOutput.textContent = '正在執行，請稍候...';
        resultHeader.className = 'card-header';

        fetch(action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
        .then(function(result) {
            if (result.data.success) {
                resultHeader.className = 'card-header bg-success text-white';
                resultHeader.innerHTML = '<strong>✅ 執行成功</strong>' +
                    (result.data.elapsed_seconds ? ' <small>（' + result.data.elapsed_seconds + ' 秒）</small>' : '');

                if (result.data.steps) {
                    var output = '';
                    for (var key in result.data.steps) {
                        output += '━━━ ' + key.toUpperCase() + ' ━━━\n' + result.data.steps[key] + '\n\n';
                    }
                    resultOutput.textContent = output.trim();
                } else {
                    resultOutput.textContent = result.data.output || result.data.message || '執行完成';
                }

                if (typeof window.showToast === 'function') {
                    window.showToast(result.data.message || '執行成功', 'success');
                }
            } else {
                throw new Error(result.data.error || '執行失敗');
            }
        })
        .catch(function(err) {
            resultHeader.className = 'card-header bg-danger text-white';
            resultHeader.innerHTML = '<strong>❌ 執行失敗</strong>';
            resultOutput.textContent = err.message;

            if (typeof window.showToast === 'function') {
                window.showToast('執行失敗：' + err.message, 'danger');
            }
        })
        .finally(function() {
            buttonEl.disabled = false;
            buttonEl.innerHTML = originalHtml;

            // Scroll to result
            resultCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
});
</script>
@endpush
@endsection
