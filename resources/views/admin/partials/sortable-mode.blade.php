{{--
    Sortable Mode Component v3 — 拖曳排序模式
    用法：@include('admin.partials.sortable-mode', [
        'reorderUrl' => route('admin.services.reorder'),
        'fetchUrl' => route('admin.services.index', ['_sortable' => 1]),
        'itemLabel' => '服務',
        'titleField' => 'title',  // 可選，預設 'title'
    ])
--}}

@php
    $titleField = $titleField ?? 'title';
@endphp

<button type="button"
        class="btn btn-sm btn-outline-secondary ms-2"
        id="sortableModeBtn"
        data-coreui-toggle="tooltip"
        title="拖曳排序模式">
    <svg class="icon" style="width:16px;height:16px"><use xlink:href="/assets/icons/free.svg#cil-swap-vertical"></use></svg>
</button>

@once
@push('styles')
<style>
/* ===== Sortable Mode v3 ===== */
.sortable-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 1050;
    background: rgba(0,0,0,.45);
    backdrop-filter: blur(3px);
}
.sortable-overlay.active { display: flex; align-items: center; justify-content: center; }

.sortable-panel {
    background: var(--cui-card-bg, #fff);
    border-radius: .75rem;
    box-shadow: 0 1.5rem 4rem rgba(0,0,0,.25);
    width: clamp(580px, 50vw, 900px);
    max-height: 88vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.sortable-panel-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--cui-border-color, #d8dbe0);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.sortable-panel-header h5 { margin: 0; font-weight: 600; font-size: 1rem; }

.sortable-panel-body {
    flex: 1;
    overflow-y: auto;
    padding: .5rem;
}

.sortable-panel-footer {
    padding: .75rem 1.25rem;
    border-top: 1px solid var(--cui-border-color, #d8dbe0);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    background: var(--cui-tertiary-bg, #f0f4f7);
}
.sortable-panel-footer .sortable-count {
    font-size: .8125rem;
    color: var(--cui-text-medium-emphasis, #768192);
}

.sortable-hint {
    padding: .625rem 1.25rem;
    font-size: .8125rem;
    color: var(--cui-text-medium-emphasis, #768192);
    background: var(--cui-tertiary-bg, #f0f4f7);
    border-bottom: 1px solid var(--cui-border-color, #d8dbe0);
    flex-shrink: 0;
}

/* Sortable list items */
.sortable-list { list-style: none; margin: 0; padding: 0; }

.sortable-item {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .5rem .625rem;
    margin-bottom: .375rem;
    background: var(--cui-card-bg, #fff);
    border: 1px solid var(--cui-border-color, #d8dbe0);
    border-radius: .5rem;
    transition: box-shadow .15s, border-color .15s;
    user-select: none;
}
.sortable-item:last-child { margin-bottom: 0; }
.sortable-item:hover {
    border-color: var(--cui-primary, #3b82f6);
    box-shadow: 0 2px 8px rgba(59, 130, 246, .1);
}

/* 拖曳區域 — 佔據主要空間 */
.sortable-item .drag-zone {
    flex: 1;
    display: flex;
    align-items: center;
    gap: .625rem;
    cursor: grab;
    padding: .375rem .25rem;
    border-radius: .25rem;
    min-height: 2.5rem;
}
.sortable-item .drag-zone:active { cursor: grabbing; }

.sortable-item .drag-icon {
    color: var(--cui-text-disabled, #b1b7c1);
    flex-shrink: 0;
    display: flex;
    transition: color .15s;
}
.sortable-item:hover .drag-icon { color: var(--cui-primary, #3b82f6); }

.sortable-item .item-order-badge {
    flex-shrink: 0;
    font-size: .75rem;
    font-weight: 700;
    color: var(--cui-primary, #3b82f6);
    background: var(--cui-primary-bg-subtle, #dbeafe);
    width: 1.75rem;
    height: 1.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.sortable-item .item-title {
    flex: 1;
    font-size: .875rem;
    font-weight: 500;
    line-height: 1.4;
    color: var(--cui-body-color, #212631);
}

/* 上下移動按鈕 */
.sortable-item .move-btns {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex-shrink: 0;
}
.sortable-item .move-btn {
    width: 1.625rem;
    height: 1.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--cui-border-color, #d8dbe0);
    border-radius: .25rem;
    background: var(--cui-card-bg, #fff);
    color: var(--cui-text-medium-emphasis, #768192);
    cursor: pointer;
    transition: all .1s;
    padding: 0;
    line-height: 1;
}
.sortable-item .move-btn:hover {
    background: var(--cui-primary, #3b82f6);
    border-color: var(--cui-primary, #3b82f6);
    color: #fff;
}
.sortable-item .move-btn:disabled {
    opacity: .25;
    cursor: not-allowed;
    pointer-events: none;
}
.sortable-item .move-btn svg {
    width: 12px;
    height: 12px;
    fill: none;
    stroke: currentColor;
    stroke-width: 2.5;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* SortableJS ghost & chosen */
.sortable-ghost {
    opacity: .25;
}
.sortable-chosen {
    box-shadow: 0 6px 20px rgba(59,130,246,.2) !important;
    border-color: var(--cui-primary, #3b82f6) !important;
    background: var(--cui-primary-bg-subtle, #dbeafe) !important;
    z-index: 10;
}
.sortable-drag {
    box-shadow: 0 10px 30px rgba(0,0,0,.2) !important;
    transform: rotate(1deg);
}

/* Loading state */
.sortable-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    gap: .75rem;
    color: var(--cui-text-medium-emphasis, #768192);
}

/* Touch-friendly on mobile */
@media (max-width: 768px) {
    .sortable-overlay.active { align-items: flex-end; }
    .sortable-panel {
        width: 100%;
        max-height: 80vh;
        border-radius: .75rem .75rem 0 0;
        box-shadow: 0 -4px 20px rgba(0,0,0,.2);
    }
    .sortable-panel-header { padding: .75rem 1rem; }
    .sortable-panel-header h5 { font-size: .9375rem; }
    .sortable-hint { padding: .375rem 1rem; font-size: .75rem; }
    .sortable-panel-body { padding: .375rem; }
    .sortable-panel-footer { padding: .5rem 1rem; }
    .sortable-item { padding: .375rem .5rem; margin-bottom: .25rem; }
    .sortable-item .drag-zone { min-height: 2.25rem; padding: .25rem 0; gap: .5rem; }
    .sortable-item .item-title { font-size: .8125rem; }
    .sortable-item .item-order-badge { width: 1.5rem; height: 1.5rem; font-size: .6875rem; }
    .sortable-item .drag-icon svg { width: 14px; height: 14px; }
    .sortable-item .move-btn { width: 1.5rem; height: 1.25rem; }
    .sortable-item .move-btn svg { width: 10px; height: 10px; }
}
</style>
@endpush
@endonce

{{-- Sortable Overlay Panel --}}
<div class="sortable-overlay" id="sortableOverlay">
    <div class="sortable-panel">
        <div class="sortable-panel-header">
            <h5>
                <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-swap-vertical"></use></svg>
                排序{{ $itemLabel ?? '項目' }}
            </h5>
            <button type="button" class="btn-close" id="sortableCancel" aria-label="關閉"></button>
        </div>
        <div class="sortable-hint">
            拖曳項目或使用 ▲▼ 按鈕調整順序，完成後點擊「儲存排序」
        </div>
        <div class="sortable-panel-body" id="sortablePanelBody">
            <div class="sortable-loading" id="sortableLoading">
                <div class="spinner-border spinner-border-sm" role="status"></div>
                <span>載入中...</span>
            </div>
            <ul class="sortable-list" id="sortableList"></ul>
        </div>
        <div class="sortable-panel-footer">
            <span class="sortable-count" id="sortableCount"></span>
            <div style="display:flex;gap:.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" id="sortableCancelBtn">取消</button>
                <button type="button" class="btn btn-primary btn-sm" id="sortableSaveBtn" disabled>
                    <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg>
                    儲存排序
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="/vendor/sortable/Sortable.min.js"></script>
<script>
(function() {
    var FETCH_URL = @json($fetchUrl);
    var REORDER_URL = @json($reorderUrl);
    var TITLE_FIELD = @json($titleField);
    var CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    var overlay    = document.getElementById('sortableOverlay');
    var openBtn    = document.getElementById('sortableModeBtn');
    var cancelBtn  = document.getElementById('sortableCancelBtn');
    var cancelX    = document.getElementById('sortableCancel');
    var saveBtn    = document.getElementById('sortableSaveBtn');
    var listEl     = document.getElementById('sortableList');
    var loadingEl  = document.getElementById('sortableLoading');
    var countEl    = document.getElementById('sortableCount');
    var sortableInstance = null;

    function open() {
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        loadData();
    }

    function close() {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        saveBtn.disabled = true;
        resetSaveBtn();
        if (sortableInstance) { sortableInstance.destroy(); sortableInstance = null; }
    }

    function markDirty() {
        saveBtn.disabled = false;
    }

    function resetSaveBtn() {
        saveBtn.innerHTML = '<svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg>儲存排序';
    }

    function loadData() {
        loadingEl.style.display = 'flex';
        listEl.innerHTML = '';
        saveBtn.disabled = true;

        fetch(FETCH_URL, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(items) {
            loadingEl.style.display = 'none';
            countEl.textContent = '共 ' + items.length + ' 項';
            renderList(items);
            initSortable();
            updateMoveButtons();
        })
        .catch(function(err) {
            loadingEl.style.display = 'none';
            listEl.innerHTML = '<li class="sortable-item"><span class="item-title text-danger">載入失敗，請重試</span></li>';
            console.error('Sortable fetch error:', err);
        });
    }

    function renderList(items) {
        var html = '';
        items.forEach(function(item, i) {
            var title = item[TITLE_FIELD] || item.name || item.title || item.client_name || ('ID: ' + item.id);
            html += '<li class="sortable-item" data-id="' + item.id + '">'
                  + '  <div class="drag-zone">'
                  + '    <span class="drag-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg></span>'
                  + '    <span class="item-order-badge">' + (i + 1) + '</span>'
                  + '    <span class="item-title">' + escapeHtml(title) + '</span>'
                  + '  </div>'
                  + '  <div class="move-btns">'
                  + '    <button type="button" class="move-btn move-up" title="上移"><svg viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg></button>'
                  + '    <button type="button" class="move-btn move-down" title="下移"><svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg></button>'
                  + '  </div>'
                  + '</li>';
        });
        listEl.innerHTML = html;

        // Bind move buttons
        listEl.querySelectorAll('.move-up').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var li = btn.closest('.sortable-item');
                var prev = li.previousElementSibling;
                if (prev) {
                    li.parentNode.insertBefore(li, prev);
                    markDirty();
                    updateOrderNumbers();
                    updateMoveButtons();
                    li.style.background = 'var(--cui-primary-bg-subtle, #dbeafe)';
                    setTimeout(function() { li.style.background = ''; }, 300);
                }
            });
        });

        listEl.querySelectorAll('.move-down').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var li = btn.closest('.sortable-item');
                var next = li.nextElementSibling;
                if (next) {
                    li.parentNode.insertBefore(next, li);
                    markDirty();
                    updateOrderNumbers();
                    updateMoveButtons();
                    li.style.background = 'var(--cui-primary-bg-subtle, #dbeafe)';
                    setTimeout(function() { li.style.background = ''; }, 300);
                }
            });
        });
    }

    function initSortable() {
        if (sortableInstance) sortableInstance.destroy();
        sortableInstance = new Sortable(listEl, {
            animation: 200,
            handle: '.drag-zone',
            delay: 80,
            delayOnTouchOnly: true,
            touchStartThreshold: 5,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function() {
                markDirty();
                updateOrderNumbers();
                updateMoveButtons();
            }
        });
    }

    function updateOrderNumbers() {
        listEl.querySelectorAll('.sortable-item').forEach(function(el, i) {
            el.querySelector('.item-order-badge').textContent = i + 1;
        });
    }

    function updateMoveButtons() {
        var items = listEl.querySelectorAll('.sortable-item');
        items.forEach(function(el, i) {
            el.querySelector('.move-up').disabled = (i === 0);
            el.querySelector('.move-down').disabled = (i === items.length - 1);
        });
    }

    function save() {
        var ids = [];
        listEl.querySelectorAll('.sortable-item').forEach(function(el) {
            ids.push(parseInt(el.dataset.id, 10));
        });

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>儲存中...';

        fetch(REORDER_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                if (typeof window.showToast === 'function') {
                    window.showToast('排序已儲存', 'success');
                }
                close();
                window.location.reload();
            } else {
                throw new Error('Save failed');
            }
        })
        .catch(function(err) {
            saveBtn.disabled = false;
            resetSaveBtn();
            if (typeof window.showToast === 'function') {
                window.showToast('儲存失敗，請重試', 'danger');
            }
            console.error('Sortable save error:', err);
        });
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Event listeners
    openBtn.addEventListener('click', open);
    cancelBtn.addEventListener('click', close);
    cancelX.addEventListener('click', close);
    saveBtn.addEventListener('click', save);
    overlay.addEventListener('click', function(e) { if (e.target === overlay) close(); });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('active')) close();
    });
})();
</script>
@endpush
