{{--
    View Toggle Component
    用法：@include('admin.partials.view-toggle', ['pageKey' => 'services'])
    pageKey 會存入 localStorage 以記住每頁偏好
--}}
<div class="btn-group ms-2" role="group" aria-label="切換檢視" id="viewToggle">
    <button type="button" class="btn btn-sm btn-outline-secondary active" data-view="list"
            data-coreui-toggle="tooltip" title="列表檢視">
        <svg class="icon" style="width:16px;height:16px"><use xlink:href="/assets/icons/free.svg#cil-list"></use></svg>
    </button>
    <button type="button" class="btn btn-sm btn-outline-secondary" data-view="grid"
            data-coreui-toggle="tooltip" title="卡片檢視">
        <svg class="icon" style="width:16px;height:16px"><use xlink:href="/assets/icons/free.svg#cil-grid"></use></svg>
    </button>
</div>

@once
@push('styles')
<style>
/* ===== Admin Grid View ===== */
.admin-grid { display: none; }
.admin-grid.active { display: flex; flex-wrap: wrap; gap: 1rem; padding: 1rem; }
.admin-list.active { display: block; }
.admin-list { display: none; }
/* 預設 list active */
.admin-list:not(.active):not(.inactive) { display: block; }

.admin-grid-card {
    flex: 1 1 calc(33.333% - 1rem);
    min-width: 280px;
    max-width: calc(33.333% - 0.67rem);
    border: 1px solid var(--cui-border-color, #d8dbe0);
    border-radius: .375rem;
    background: var(--cui-card-bg, #fff);
    display: flex;
    flex-direction: column;
    transition: box-shadow .15s ease-in-out, transform .1s ease;
}
.admin-grid-card:hover {
    box-shadow: 0 .25rem .75rem rgba(0,0,0,.08);
    transform: translateY(-1px);
}

.admin-grid-card-header {
    padding: .75rem 1rem .5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
    border-bottom: 1px solid var(--cui-border-color, #d8dbe0);
    min-height: 42px;
}
.admin-grid-card-header .badge { font-size: 11px; }

.admin-grid-card-body {
    padding: .75rem 1rem;
    flex: 1;
}
.admin-grid-card-body h6 {
    font-size: .9rem;
    font-weight: 600;
    margin-bottom: .35rem;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.admin-grid-card-body h6 a {
    color: inherit;
    text-decoration: none;
}
.admin-grid-card-body h6 a:hover { color: var(--cui-primary, #321fdb); }
.admin-grid-card-subtitle {
    font-size: .78rem;
    color: var(--cui-text-medium-emphasis, #768192);
    margin-bottom: .5rem;
}

.admin-grid-card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .25rem .75rem;
    font-size: .78rem;
    color: var(--cui-text-medium-emphasis, #768192);
}
.admin-grid-card-meta dt { font-weight: 500; }
.admin-grid-card-meta dd { margin: 0; }

.admin-grid-card-footer {
    padding: .5rem 1rem;
    border-top: 1px solid var(--cui-border-color, #d8dbe0);
    display: flex;
    justify-content: flex-end;
    gap: .25rem;
}
.admin-grid-card-footer .btn { padding: .2rem .45rem; }

.admin-grid-card-price {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--cui-primary, #321fdb);
    margin-bottom: .35rem;
}

/* Highlighted / featured card */
.admin-grid-card.featured { border-color: var(--cui-primary, #321fdb); }
.admin-grid-card.danger   { border-color: var(--cui-danger, #e55353); }
.admin-grid-card.warning  { border-color: var(--cui-warning, #f9b115); }

/* Responsive grid sizing */
@media (max-width: 992px) {
    .admin-grid-card { flex: 1 1 calc(50% - .5rem); max-width: calc(50% - .5rem); }
}
@media (max-width: 576px) {
    .admin-grid-card { flex: 1 1 100%; max-width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    var KEY = 'adminViewPref_{{ $pageKey ?? "default" }}';
    var toggle = document.getElementById('viewToggle');
    if (!toggle) return;

    var listEl  = document.querySelector('.admin-list');
    var gridEl  = document.querySelector('.admin-grid');
    if (!listEl || !gridEl) return;

    function setView(mode) {
        if (mode === 'grid') {
            listEl.classList.remove('active'); listEl.classList.add('inactive');
            listEl.style.display = 'none';
            gridEl.classList.add('active');
        } else {
            gridEl.classList.remove('active');
            listEl.classList.remove('inactive'); listEl.classList.add('active');
            listEl.style.display = '';
        }
        toggle.querySelectorAll('[data-view]').forEach(function(b) {
            b.classList.toggle('active', b.dataset.view === mode);
        });
        try { localStorage.setItem(KEY, mode); } catch(e) {}
    }

    toggle.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-view]');
        if (btn) setView(btn.dataset.view);
    });

    // restore preference
    try {
        var saved = localStorage.getItem(KEY);
        if (saved === 'grid') setView('grid');
    } catch(e) {}
})();
</script>
@endpush
@endonce
