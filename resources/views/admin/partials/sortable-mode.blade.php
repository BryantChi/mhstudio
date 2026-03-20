{{--
    Sortable Mode Component — 拖曳排序模式
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
/* ===== Sortable Mode v2 ===== */
.sortable-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 1050;
    background: rgba(0,0,0,.5);
    backdrop-filter: blur(2px);
}
.sortable-overlay.active { display: flex; align-items: center; justify-content: center; }

.sortable-panel {
    background: var(--cui-card-bg, #fff);
    border-radius: .75rem;
    box-shadow: 0 1rem 3rem rgba(0,0,0,.25);
    width: 92%;
    max-width: 640px;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
}

.sortable-panel-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--cui-border-color, #d8dbe0);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.sortable-panel-header h5 { margin: 0; font-weight: 600; }

.sortable-panel-body {
    flex: 1;
    overflow-y: auto;
    padding: .25rem 0;
}

.sortable-panel-footer {
    padding: .75rem 1.25rem;
    border-top: 1px solid var(--cui-border-color, #d8dbe0);
    display: flex;
    justify-content: flex-end;
    gap: .5rem;
}

.sortable-hint {
    padding: .5rem 1.25rem;
    font-size: .85rem;
    color: var(--cui-text-medium-emphasis, #768192);
    background: var(--cui-tertiary-bg, #f0f4f7);
    display: flex;
    align-items: center;
    gap: .5rem;
}

/* Sortable list items */
.sortable-list { list-style: none; margin: 0; padding: 0; }

.sortable-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .875rem 1.25rem;
    cursor: grab;
    border-bottom: 1px solid var(--cui-border-color-translucent, rgba(0,0,0,.08));
    transition: background .15s, box-shadow .15s;
    user-select: none;
}
.sortable-item:last-child { border-bottom: none; }
.sortable-item:hover {
    background: var(--cui-primary-bg-subtle, #cfe2ff);
}
.sortable-item:active { cursor: grabbing; }

.sortable-item .drag-handle {
    color: var(--cui-text-medium-emphasis, #768192);
    flex-shrink: 0;
    font-size: 1.25rem;
    line-height: 1;
    opacity: .5;
    transition: opacity .15s;
}
.sortable-item:hover .drag-handle { opacity: 1; }

.sortable-item .item-title {
    flex: 1;
    font-size: .9375rem;
    font-weight: 500;
    line-height: 1.3;
}

.sortable-item .item-order {
    flex-shrink: 0;
    font-size: .8125rem;
    font-weight: 600;
    color: #fff;
    background: var(--cui-primary, #3b82f6);
    padding: .2rem .6rem;
    border-radius: 1rem;
    min-width: 1.75rem;
    text-align: center;
    line-height: 1.3;
}

/* SortableJS ghost & chosen */
.sortable-ghost {
    opacity: .3;
    background: var(--cui-primary-bg-subtle, #cfe2ff) !important;
}
.sortable-chosen {
    background: #fff !important;
    box-shadow: 0 4px 16px rgba(0,0,0,.15);
    border-radius: .375rem;
    z-index: 10;
}
.sortable-drag {
    box-shadow: 0 8px 24px rgba(0,0,0,.2);
    border-radius: .375rem;
}

/* Loading state */
.sortable-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    color: var(--cui-text-medium-emphasis, #768192);
}

/* Touch-friendly: larger tap targets on mobile */
@media (max-width: 768px) {
    .sortable-item { padding: 1rem 1.25rem; }
    .sortable-item .item-title { font-size: 1rem; }
    .sortable-panel { width: 96%; max-height: 90vh; }
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
            <svg class="icon" style="width:16px;height:16px"><use xlink:href="/assets/icons/free.svg#cil-cursor-move"></use></svg>
            直接拖曳項目調整順序，完成後點擊「儲存排序」
        </div>
        <div class="sortable-panel-body" id="sortablePanelBody">
            <div class="sortable-loading" id="sortableLoading">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                載入中...
            </div>
            <ul class="sortable-list" id="sortableList"></ul>
        </div>
        <div class="sortable-panel-footer">
            <button type="button" class="btn btn-secondary btn-sm" id="sortableCancelBtn">取消</button>
            <button type="button" class="btn btn-primary btn-sm" id="sortableSaveBtn" disabled>
                <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg>
                儲存排序
            </button>
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
    var sortableInstance = null;
    var isDirty = false;

    function open() {
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        loadData();
    }

    function close() {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        isDirty = false;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg>儲存排序';
        if (sortableInstance) {
            sortableInstance.destroy();
            sortableInstance = null;
        }
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
            renderList(items);
            initSortable();
        })
        .catch(function(err) {
            loadingEl.style.display = 'none';
            listEl.innerHTML = '<li class="sortable-item text-danger">載入失敗，請重試</li>';
            console.error('Sortable fetch error:', err);
        });
    }

    function renderList(items) {
        var html = '';
        items.forEach(function(item, i) {
            var title = item[TITLE_FIELD] || item.name || item.title || item.client_name || ('ID: ' + item.id);
            html += '<li class="sortable-item" data-id="' + item.id + '">'
                  + '  <span class="drag-handle">&#9776;</span>'
                  + '  <span class="item-title">' + escapeHtml(title) + '</span>'
                  + '  <span class="item-order">' + (i + 1) + '</span>'
                  + '</li>';
        });
        listEl.innerHTML = html;
    }

    function initSortable() {
        if (sortableInstance) sortableInstance.destroy();
        sortableInstance = new Sortable(listEl, {
            animation: 200,
            delay: 50,
            delayOnTouchOnly: true,
            touchStartThreshold: 3,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function() {
                isDirty = true;
                saveBtn.disabled = false;
                updateOrderNumbers();
            }
        });
    }

    function updateOrderNumbers() {
        listEl.querySelectorAll('.sortable-item').forEach(function(el, i) {
            el.querySelector('.item-order').textContent = i + 1;
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
            saveBtn.innerHTML = '<svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg>儲存排序';
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

    // Close on overlay background click
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) close();
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('active')) {
            close();
        }
    });
})();
</script>
@endpush
