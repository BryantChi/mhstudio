{{--
    Media Picker Modal - 可重用的媒體選擇器
    使用方式：
    1. @include('admin.media.partials.picker-modal')
    2. 在需要選圖的 input 旁加上按鈕：
       <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openMediaPicker('target_input_id')">
           從媒體庫選擇
       </button>
    3. 選取後會將圖片 URL 寫入 target input，並在 #media-picker-preview-{id} 顯示預覽
--}}

<div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-labelledby="mediaPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaPickerModalLabel">
                    <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-image"></use></svg>
                    從媒體庫選擇圖片
                </h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- 搜尋 + 上傳 --}}
                <div class="d-flex gap-2 mb-3">
                    <div class="flex-grow-1">
                        <input type="text" class="form-control" id="mediaPickerSearch"
                               placeholder="搜尋圖片名稱..." autocomplete="off">
                    </div>
                    <div>
                        <label class="btn btn-primary mb-0" for="mediaPickerUpload">
                            <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-cloud-upload"></use></svg>
                            上傳新圖片
                        </label>
                        <input type="file" id="mediaPickerUpload" accept="image/*" class="d-none">
                    </div>
                </div>

                {{-- 上傳進度 --}}
                <div id="mediaPickerUploadProgress" class="mb-3 d-none">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block">正在上傳...</small>
                </div>

                {{-- 圖片格線 --}}
                <div id="mediaPickerGrid" class="media-picker-grid">
                    <div class="media-picker-empty">點擊「媒體庫」按鈕後將載入圖片</div>
                </div>

                {{-- 分頁 --}}
                <div id="mediaPickerPagination" class="d-flex justify-content-center mt-3"></div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div id="mediaPickerSelected" class="text-muted small">尚未選取圖片</div>
                <div>
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="mediaPickerConfirmBtn" disabled>
                        <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-check"></use></svg>
                        <span id="mediaPickerConfirmText">確認選取</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .media-picker-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 0.75rem;
        min-height: 200px;
    }
    @media (max-width: 1200px) { .media-picker-grid { grid-template-columns: repeat(4, 1fr); } }
    @media (max-width: 768px) { .media-picker-grid { grid-template-columns: repeat(3, 1fr); } }

    .media-picker-item {
        position: relative;
        aspect-ratio: 1;
        border: 2px solid #dee2e6;
        border-radius: 0.375rem;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s;
    }
    .media-picker-item:hover {
        border-color: #3399ff;
        box-shadow: 0 0 0 2px rgba(51, 153, 255, 0.25);
    }
    .media-picker-item.selected {
        border-color: #3399ff;
        box-shadow: 0 0 0 3px rgba(51, 153, 255, 0.35);
    }
    .media-picker-item.selected::after {
        content: '\2713';
        position: absolute;
        top: 4px; right: 4px;
        width: 22px; height: 22px;
        background: #3399ff;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        z-index: 2;
    }
    .media-picker-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .media-picker-item-name {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        background: rgba(0,0,0,0.65);
        color: #fff;
        font-size: 10px;
        padding: 3px 6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .media-picker-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 3rem 0;
        color: #8a93a2;
    }
    /* 多選模式序號 badge */
    .media-picker-item.selected .media-picker-order-badge {
        position: absolute;
        top: 4px; right: 4px;
        width: 22px; height: 22px;
        background: #3399ff;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
        z-index: 2;
    }
    /* 多選模式隱藏預設勾勾 */
    .media-picker-grid.multi-select .media-picker-item.selected::after {
        display: none;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== Media Picker State =====
    var _mpTargetInput = null;
    var _mpSelectedUrl = null;       // 單選模式
    var _mpSelectedUrls = [];        // 多選模式
    var _mpMultiMode = false;        // 是否為多選模式
    var _mpMultiCallback = null;     // 多選確認後的 callback
    var _mpModalInstance = null;

    var mpModalEl = document.getElementById('mediaPickerModal');
    var mpGrid = document.getElementById('mediaPickerGrid');
    var mpAdminPrefix = '{{ config("admin.prefix", "admin") }}';
    var mpCsrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // 取得或建立 modal 實例（延遲初始化）
    function getPickerModal() {
        if (_mpModalInstance) return _mpModalInstance;
        if (!mpModalEl) return null;

        if (window.coreui && window.coreui.Modal) {
            _mpModalInstance = window.coreui.Modal.getOrCreateInstance(mpModalEl);
            return _mpModalInstance;
        }
        if (window.bootstrap && window.bootstrap.Modal) {
            _mpModalInstance = window.bootstrap.Modal.getOrCreateInstance(mpModalEl);
            return _mpModalInstance;
        }
        return null;
    }

    // ===== 重置選取狀態 =====
    function resetPickerState() {
        _mpSelectedUrl = null;
        _mpSelectedUrls = [];
        if (mpGrid) mpGrid.classList.remove('multi-select');
    }

    // ===== 單選開啟函式（向後相容） =====
    window.openMediaPicker = function(targetInputId) {
        _mpTargetInput = document.getElementById(targetInputId);
        if (!_mpTargetInput) {
            console.error('Media Picker: target input not found:', targetInputId);
            return;
        }

        _mpMultiMode = false;
        _mpMultiCallback = null;
        resetPickerState();
        updatePickerSelection();

        var confirmText = document.getElementById('mediaPickerConfirmText');
        if (confirmText) confirmText.textContent = '確認選取';

        var searchInput = document.getElementById('mediaPickerSearch');
        if (searchInput) searchInput.value = '';

        loadPickerMedia(1);

        var modal = getPickerModal();
        if (modal) {
            modal.show();
        } else {
            console.error('Media Picker: 無法初始化 modal');
        }
    };

    // ===== 多選開啟函式 =====
    window.openMediaPickerMulti = function(callback) {
        if (typeof callback !== 'function') {
            console.error('Media Picker Multi: callback is required');
            return;
        }

        _mpTargetInput = null;
        _mpMultiMode = true;
        _mpMultiCallback = callback;
        resetPickerState();
        if (mpGrid) mpGrid.classList.add('multi-select');
        updatePickerSelection();

        var confirmText = document.getElementById('mediaPickerConfirmText');
        if (confirmText) confirmText.textContent = '確認選取';

        var searchInput = document.getElementById('mediaPickerSearch');
        if (searchInput) searchInput.value = '';

        loadPickerMedia(1);

        var modal = getPickerModal();
        if (modal) {
            modal.show();
        } else {
            console.error('Media Picker: 無法初始化 modal');
        }
    };

    // ===== 載入媒體 =====
    function loadPickerMedia(page, search) {
        page = page || 1;
        search = search || document.getElementById('mediaPickerSearch')?.value || '';

        var grid = document.getElementById('mediaPickerGrid');
        if (!grid) return;
        grid.innerHTML = '<div class="media-picker-empty"><div class="spinner-border spinner-border-sm me-2"></div> 載入中...</div>';

        var params = new URLSearchParams({ images_only: '1', page: String(page) });
        if (search) params.append('search', search);

        fetch('/' + mpAdminPrefix + '/media/browse?' + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            renderPickerGrid(data.data || []);
            renderPickerPagination(data.current_page || 1, data.last_page || 1);
        })
        .catch(function(err) {
            console.error('Media Picker fetch error:', err);
            grid.innerHTML = '<div class="media-picker-empty">載入失敗，請重試</div>';
        });
    }

    // ===== 渲染格線 =====
    function renderPickerGrid(items) {
        var grid = document.getElementById('mediaPickerGrid');
        if (!grid) return;

        // 保留多選模式的 class
        var extraClass = _mpMultiMode ? ' multi-select' : '';

        if (!items || items.length === 0) {
            grid.innerHTML = '<div class="media-picker-empty">沒有找到圖片。請先到<a href="/' + mpAdminPrefix + '/media">媒體庫</a>上傳圖片，或使用上方「上傳新圖片」按鈕。</div>';
            if (_mpMultiMode) grid.classList.add('multi-select');
            return;
        }

        var html = '';
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            // 檢查是否已選取（單選或多選）
            var isSelected = false;
            var orderNum = 0;
            if (_mpMultiMode) {
                var idx = _mpSelectedUrls.indexOf(item.url);
                if (idx !== -1) { isSelected = true; orderNum = idx + 1; }
            } else {
                isSelected = (_mpSelectedUrl && _mpSelectedUrl === item.url);
            }

            var altText = (item.alt_text || item.original_name || '').replace(/"/g, '&quot;');
            var fileName = (item.original_name || '').replace(/</g, '&lt;');
            html += '<div class="media-picker-item' + (isSelected ? ' selected' : '') + '" data-url="' + item.url + '" data-name="' + altText + '">'
                  + '<img src="' + item.url + '" alt="' + altText + '" loading="lazy">';
            // 多選模式顯示序號 badge
            if (_mpMultiMode && isSelected) {
                html += '<span class="media-picker-order-badge">' + orderNum + '</span>';
            }
            html += '<div class="media-picker-item-name">' + fileName + '</div>'
                  + '</div>';
        }
        grid.innerHTML = html;
        if (_mpMultiMode) grid.classList.add('multi-select');

        // 綁定點擊事件（事件委派）
        grid.querySelectorAll('.media-picker-item').forEach(function(el) {
            el.addEventListener('click', function() {
                var url = el.getAttribute('data-url');

                if (_mpMultiMode) {
                    // 多選：toggle 選取
                    var idx = _mpSelectedUrls.indexOf(url);
                    if (idx !== -1) {
                        _mpSelectedUrls.splice(idx, 1);
                        el.classList.remove('selected');
                        var badge = el.querySelector('.media-picker-order-badge');
                        if (badge) badge.remove();
                    } else {
                        _mpSelectedUrls.push(url);
                        el.classList.add('selected');
                        var newBadge = document.createElement('span');
                        newBadge.className = 'media-picker-order-badge';
                        newBadge.textContent = _mpSelectedUrls.length;
                        el.appendChild(newBadge);
                    }
                    // 重新更新所有序號
                    refreshMultiOrderBadges(grid);
                } else {
                    // 單選：取代
                    grid.querySelectorAll('.media-picker-item.selected').forEach(function(s) { s.classList.remove('selected'); });
                    el.classList.add('selected');
                    _mpSelectedUrl = url;
                }
                updatePickerSelection();
            });
        });
    }

    // ===== 重新整理多選序號 =====
    function refreshMultiOrderBadges(grid) {
        grid.querySelectorAll('.media-picker-item').forEach(function(el) {
            var url = el.getAttribute('data-url');
            var badge = el.querySelector('.media-picker-order-badge');
            var idx = _mpSelectedUrls.indexOf(url);
            if (idx !== -1) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'media-picker-order-badge';
                    el.appendChild(badge);
                }
                badge.textContent = idx + 1;
            } else {
                if (badge) badge.remove();
            }
        });
    }

    // ===== 渲染分頁 =====
    function renderPickerPagination(current, last) {
        var container = document.getElementById('mediaPickerPagination');
        if (!container) return;
        if (last <= 1) { container.innerHTML = ''; return; }

        var html = '<nav><ul class="pagination pagination-sm mb-0">';
        for (var i = 1; i <= last; i++) {
            html += '<li class="page-item' + (i === current ? ' active' : '') + '">'
                  + '<button type="button" class="page-link" data-picker-page="' + i + '">' + i + '</button>'
                  + '</li>';
        }
        html += '</ul></nav>';
        container.innerHTML = html;

        container.querySelectorAll('[data-picker-page]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                loadPickerMedia(parseInt(this.getAttribute('data-picker-page'), 10));
            });
        });
    }

    // ===== 更新選取狀態 =====
    function updatePickerSelection() {
        var btn = document.getElementById('mediaPickerConfirmBtn');
        var info = document.getElementById('mediaPickerSelected');
        var confirmText = document.getElementById('mediaPickerConfirmText');
        if (!btn || !info) return;

        if (_mpMultiMode) {
            // 多選模式
            var count = _mpSelectedUrls.length;
            if (count > 0) {
                btn.disabled = false;
                info.textContent = '已選取 ' + count + ' 張圖片';
                if (confirmText) confirmText.textContent = '確認選取 (' + count + ')';
            } else {
                btn.disabled = true;
                info.textContent = '點擊圖片可多選（再次點擊取消）';
                if (confirmText) confirmText.textContent = '確認選取';
            }
        } else {
            // 單選模式
            if (_mpSelectedUrl) {
                btn.disabled = false;
                var selectedEl = document.querySelector('.media-picker-item.selected');
                var name = selectedEl ? (selectedEl.getAttribute('data-name') || '') : '';
                info.innerHTML = '<img src="' + _mpSelectedUrl + '" style="width:24px;height:24px;object-fit:cover;border-radius:3px;" class="me-1"> 已選取：' + name;
            } else {
                btn.disabled = true;
                info.textContent = '尚未選取圖片';
            }
            if (confirmText) confirmText.textContent = '確認選取';
        }
    }

    // ===== 確認選取 =====
    var confirmBtn = document.getElementById('mediaPickerConfirmBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (_mpMultiMode) {
                // 多選模式：呼叫 callback
                if (_mpSelectedUrls.length === 0 || typeof _mpMultiCallback !== 'function') return;
                var urls = _mpSelectedUrls.slice(); // 複製
                var modal = getPickerModal();
                if (modal) modal.hide();
                _mpMultiCallback(urls);
            } else {
                // 單選模式：寫入 input
                if (!_mpSelectedUrl || !_mpTargetInput) return;

                _mpTargetInput.value = _mpSelectedUrl;
                _mpTargetInput.dispatchEvent(new Event('input', { bubbles: true }));
                _mpTargetInput.dispatchEvent(new Event('change', { bubbles: true }));

                var previewId = 'media-picker-preview-' + _mpTargetInput.id;
                var preview = document.getElementById(previewId);
                if (preview) {
                    preview.src = _mpSelectedUrl;
                    preview.style.display = 'block';
                    var container = preview.closest('.media-picker-preview-container');
                    if (container) container.style.display = 'block';
                }

                var modal = getPickerModal();
                if (modal) modal.hide();
            }
        });
    }

    // ===== 搜尋防抖 =====
    var searchTimer = null;
    var searchInput = document.getElementById('mediaPickerSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var val = this.value;
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() { loadPickerMedia(1, val); }, 300);
        });
    }

    // ===== 上傳新圖片 =====
    var uploadInput = document.getElementById('mediaPickerUpload');
    if (uploadInput) {
        uploadInput.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (!file) return;

            var progressContainer = document.getElementById('mediaPickerUploadProgress');
            var progressBar = progressContainer ? progressContainer.querySelector('.progress-bar') : null;
            if (progressContainer) progressContainer.classList.remove('d-none');

            var formData = new FormData();
            formData.append('file', file);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/' + mpAdminPrefix + '/media');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            if (mpCsrfToken) {
                xhr.setRequestHeader('X-CSRF-TOKEN', mpCsrfToken);
            }

            xhr.upload.onprogress = function(ev) {
                if (ev.lengthComputable && progressBar) {
                    var pct = Math.round((ev.loaded / ev.total) * 100);
                    progressBar.style.width = pct + '%';
                }
            };

            xhr.onload = function() {
                if (progressContainer) progressContainer.classList.add('d-none');
                if (progressBar) progressBar.style.width = '0%';
                uploadInput.value = '';

                if (xhr.status === 200) {
                    try {
                        var resp = JSON.parse(xhr.responseText);
                        if (resp.success && resp.media) {
                            if (_mpMultiMode) {
                                // 多選模式：自動加入已選取清單
                                _mpSelectedUrls.push(resp.media.url);
                                updatePickerSelection();
                            } else {
                                _mpSelectedUrl = resp.media.url;
                                updatePickerSelection();
                            }
                            loadPickerMedia(1);
                        }
                    } catch(ex) {
                        alert('上傳回應解析失敗');
                    }
                } else {
                    alert('上傳失敗（' + xhr.status + '），請重試');
                }
            };

            xhr.onerror = function() {
                if (progressContainer) progressContainer.classList.add('d-none');
                if (progressBar) progressBar.style.width = '0%';
                uploadInput.value = '';
                alert('上傳失敗，請檢查網路連線');
            };

            xhr.send(formData);
        });
    }
});
</script>
@endpush
