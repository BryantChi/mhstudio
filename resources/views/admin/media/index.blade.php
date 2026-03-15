@extends('layouts.admin')

@section('title', '媒體庫')

@php
    $breadcrumbs = [
        ['title' => '媒體庫', 'url' => '#']
    ];
@endphp

@push('styles')
<style>
    /* 統計卡片 */
    .stats-bar .stat-item {
        text-align: center;
        padding: 0.75rem;
    }
    .stats-bar .stat-value {
        font-size: 1.25rem;
        font-weight: 600;
    }
    .stats-bar .stat-label {
        font-size: 0.8rem;
        color: #8a93a2;
    }

    /* 媒體格線 */
    .media-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        padding: 1rem;
    }
    @media (max-width: 1200px) {
        .media-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .media-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) {
        .media-grid { grid-template-columns: 1fr; }
    }

    /* 媒體卡片 */
    .media-card {
        position: relative;
        border: 2px solid transparent;
        border-radius: 0.5rem;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .media-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .media-card.selected {
        border-color: #321fdb;
    }

    /* 選取核取方塊 */
    .media-card .select-check {
        position: absolute;
        top: 0.5rem;
        left: 0.5rem;
        z-index: 10;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .media-card:hover .select-check,
    .media-card.selected .select-check,
    .bulk-mode .media-card .select-check {
        opacity: 1;
    }

    /* 預覽區 */
    .media-preview {
        width: 100%;
        height: 160px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0f4f7;
        overflow: hidden;
    }
    .media-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .media-preview .file-icon {
        width: 48px;
        height: 48px;
        color: #8a93a2;
    }

    /* 懸浮操作覆蓋層 */
    .media-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .media-card:hover .media-overlay {
        opacity: 1;
    }

    /* 卡片資訊 */
    .media-info {
        padding: 0.5rem 0.75rem;
    }
    .media-info .media-name {
        font-size: 0.85rem;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .media-info .media-meta {
        font-size: 0.75rem;
        color: #8a93a2;
    }

    /* 拖放區域 */
    .drop-zone {
        border: 2px dashed #c4cdd5;
        border-radius: 0.5rem;
        padding: 3rem 2rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .drop-zone.drag-over {
        border-color: #321fdb;
        background: #f0f0ff;
    }
    .drop-zone-icon {
        width: 48px;
        height: 48px;
        color: #8a93a2;
        margin-bottom: 1rem;
    }
    .drop-zone-text {
        font-size: 1.1rem;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    .drop-zone-hint {
        color: #8a93a2;
        margin-bottom: 0.5rem;
    }

    /* 上傳項目 */
    .upload-item {
        display: flex;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #eee;
    }
    .upload-item:last-child {
        border-bottom: none;
    }
    .upload-item .upload-name {
        flex: 1;
        font-size: 0.85rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-right: 1rem;
    }
    .upload-item .progress {
        width: 120px;
        height: 6px;
    }
    .upload-item .upload-status {
        width: 80px;
        text-align: right;
        font-size: 0.8rem;
    }

    /* Alt text 編輯 */
    .alt-text-form {
        display: none;
    }
    .media-card.editing .alt-text-form {
        display: block;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">媒體庫</h2>
        <p class="text-muted">管理網站媒體檔案</p>
    </div>
    <div class="col-md-6 text-md-end">
        <button type="button" class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#uploadModal">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-cloud-upload"></use>
            </svg>
            上傳檔案
        </button>
    </div>
</div>

{{-- 統計列 --}}
<div class="row mb-4">
    <div class="col-md-3 col-6">
        <div class="card stats-bar">
            <div class="stat-item">
                <div class="stat-value">{{ number_format($stats['total_count']) }}</div>
                <div class="stat-label">檔案總數</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stats-bar">
            <div class="stat-item">
                <div class="stat-value">
                    @php
                        $totalSize = $stats['total_size'];
                        $units = ['B', 'KB', 'MB', 'GB'];
                        $i = 0;
                        for (; $totalSize >= 1024 && $i < count($units) - 1; $i++) {
                            $totalSize /= 1024;
                        }
                    @endphp
                    {{ round($totalSize, 2) }} {{ $units[$i] }}
                </div>
                <div class="stat-label">總容量</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stats-bar">
            <div class="stat-item">
                <div class="stat-value">{{ number_format($stats['images_count']) }}</div>
                <div class="stat-label">圖片數量</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stats-bar">
            <div class="stat-item">
                <div class="stat-value">{{ number_format($stats['documents_count']) }}</div>
                <div class="stat-label">文件數量</div>
            </div>
        </div>
    </div>
</div>

{{-- 篩選列 --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.media.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">搜尋</label>
                <input type="text"
                       class="form-control"
                       name="search"
                       placeholder="搜尋檔案名稱或替代文字..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">檔案類型</label>
                <select class="form-select" name="type">
                    <option value="">全部類型</option>
                    <option value="images" {{ request('type') == 'images' ? 'selected' : '' }}>圖片</option>
                    <option value="documents" {{ request('type') == 'documents' ? 'selected' : '' }}>文件</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-secondary">
                    <svg class="icon">
                        <use xlink:href="/assets/icons/free.svg#cil-search"></use>
                    </svg>
                    搜尋
                </button>
                <a href="{{ route('admin.media.index') }}" class="btn btn-light">清除</a>
            </div>
        </form>
    </div>
</div>

{{-- 批次操作列 --}}
<div id="bulkActions" class="card mb-3 d-none">
    <div class="card-body d-flex align-items-center justify-content-between py-2">
        <div>
            <span id="selectedCount" class="fw-semibold">0</span> 個檔案已選取
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="clearSelection()">
                取消選取
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="bulkDelete()">
                <svg class="icon me-1">
                    <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                </svg>
                批次刪除
            </button>
        </div>
    </div>
</div>

{{-- 媒體格線 --}}
<div class="card">
    <div class="card-body p-0">
        @if($mediaItems->count() > 0)
        <div class="media-grid" id="mediaGrid">
            @foreach($mediaItems as $item)
            <div class="media-card" data-id="{{ $item->id }}">
                {{-- 選取核取方塊 --}}
                <div class="select-check">
                    <input type="checkbox"
                           class="form-check-input media-checkbox"
                           value="{{ $item->id }}"
                           onclick="event.stopPropagation(); toggleSelect({{ $item->id }})">
                </div>

                {{-- 預覽區 --}}
                <div class="media-preview">
                    @if($item->is_image)
                        <img src="{{ $item->url }}" alt="{{ $item->alt_text ?? $item->original_name }}" loading="lazy">
                    @else
                        @php
                            $ext = pathinfo($item->original_name, PATHINFO_EXTENSION);
                            $iconMap = [
                                'pdf' => 'cil-file',
                                'doc' => 'cil-file',
                                'docx' => 'cil-file',
                                'xls' => 'cil-spreadsheet',
                                'xlsx' => 'cil-spreadsheet',
                                'zip' => 'cil-compressed',
                            ];
                            $icon = $iconMap[$ext] ?? 'cil-file';
                        @endphp
                        <svg class="file-icon">
                            <use xlink:href="/assets/icons/free.svg#{{ $icon }}"></use>
                        </svg>
                    @endif

                    {{-- 懸浮操作 --}}
                    <div class="media-overlay">
                        @if($item->is_image)
                        <a href="{{ $item->url }}" target="_blank" class="btn btn-sm btn-light" title="檢視">
                            <svg class="icon">
                                <use xlink:href="/assets/icons/free.svg#cil-external-link"></use>
                            </svg>
                        </a>
                        @endif
                        <button type="button" class="btn btn-sm btn-light" onclick="event.stopPropagation(); copyUrl('{{ $item->url }}')" title="複製網址">
                            <svg class="icon">
                                <use xlink:href="/assets/icons/free.svg#cil-copy"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-sm btn-light" onclick="event.stopPropagation(); editAltText({{ $item->id }})" title="編輯替代文字">
                            <svg class="icon">
                                <use xlink:href="/assets/icons/free.svg#cil-pencil"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteMedia({{ $item->id }})" title="刪除">
                            <svg class="icon">
                                <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- 檔案資訊 --}}
                <div class="media-info">
                    <div class="media-name" title="{{ $item->original_name }}">{{ $item->original_name }}</div>
                    <div class="media-meta">{{ $item->human_size }} &middot; {{ $item->created_at->format('Y-m-d') }}</div>
                </div>

                {{-- Alt text 編輯表單（隱藏） --}}
                <div class="alt-text-form p-2 border-top" id="altForm-{{ $item->id }}">
                    <div class="input-group input-group-sm">
                        <input type="text"
                               class="form-control"
                               id="altInput-{{ $item->id }}"
                               placeholder="替代文字..."
                               value="{{ $item->alt_text }}">
                        <button class="btn btn-outline-primary" type="button" onclick="saveAltText({{ $item->id }})">
                            儲存
                        </button>
                        <button class="btn btn-outline-secondary" type="button" onclick="cancelAltText({{ $item->id }})">
                            取消
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg class="icon" style="width:48px;height:48px;color:#8a93a2;">
                    <use xlink:href="/assets/icons/free.svg#cil-image"></use>
                </svg>
            </div>
            <div>尚無媒體檔案</div>
            <button type="button" class="btn btn-primary mt-3" data-coreui-toggle="modal" data-coreui-target="#uploadModal">
                上傳第一個檔案
            </button>
        </div>
        @endif
    </div>

    @if($mediaItems->hasPages())
    <div class="card-footer">
        {{ $mediaItems->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- 上傳 Modal --}}
@include('admin.media.partials.upload-modal')

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');

    // 拖放事件
    if (dropZone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('drag-over');
            });
        });

        dropZone.addEventListener('drop', function(e) {
            const files = e.dataTransfer.files;
            if (files.length) {
                handleFiles(files);
            }
        });

        dropZone.addEventListener('click', function() {
            fileInput.click();
        });
    }

    // 檔案選擇
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length) {
                handleFiles(this.files);
                this.value = '';
            }
        });
    }

    // 處理上傳
    window.handleFiles = function(files) {
        const uploadQueue = document.getElementById('uploadQueue');
        const uploadList = document.getElementById('uploadList');
        uploadQueue.classList.remove('d-none');

        Array.from(files).forEach(function(file) {
            const itemId = 'upload-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);

            const itemHtml = '<div class="upload-item" id="' + itemId + '">' +
                '<span class="upload-name">' + escapeHtml(file.name) + '</span>' +
                '<div class="progress me-2"><div class="progress-bar" role="progressbar" style="width: 0%"></div></div>' +
                '<span class="upload-status text-muted">上傳中...</span>' +
                '</div>';
            uploadList.insertAdjacentHTML('beforeend', itemHtml);

            uploadFile(file, itemId);
        });
    };

    window.uploadFile = function(file, itemId) {
        const formData = new FormData();
        formData.append('file', file);

        const xhr = new XMLHttpRequest();
        const itemEl = document.getElementById(itemId);
        const progressBar = itemEl.querySelector('.progress-bar');
        const statusEl = itemEl.querySelector('.upload-status');

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
            }
        });

        xhr.addEventListener('load', function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                progressBar.classList.add('bg-success');
                progressBar.style.width = '100%';
                statusEl.textContent = '完成';
                statusEl.classList.remove('text-muted');
                statusEl.classList.add('text-success');

                // 新增卡片到格線（或重新載入頁面）
                setTimeout(function() {
                    window.location.reload();
                }, 800);
            } else {
                let errorMsg = '失敗';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.errors && response.errors.file) {
                        errorMsg = response.errors.file[0];
                    } else if (response.message) {
                        errorMsg = response.message;
                    }
                } catch(e) {}
                progressBar.classList.add('bg-danger');
                progressBar.style.width = '100%';
                statusEl.textContent = errorMsg;
                statusEl.classList.remove('text-muted');
                statusEl.classList.add('text-danger');
            }
        });

        xhr.addEventListener('error', function() {
            progressBar.classList.add('bg-danger');
            progressBar.style.width = '100%';
            statusEl.textContent = '上傳失敗';
            statusEl.classList.remove('text-muted');
            statusEl.classList.add('text-danger');
        });

        xhr.open('POST', '{{ route("admin.media.store") }}');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.send(formData);
    };
});

// 選取相關
let selectedIds = new Set();

function toggleSelect(id) {
    const card = document.querySelector('.media-card[data-id="' + id + '"]');
    const checkbox = card.querySelector('.media-checkbox');

    if (selectedIds.has(id)) {
        selectedIds.delete(id);
        card.classList.remove('selected');
        checkbox.checked = false;
    } else {
        selectedIds.add(id);
        card.classList.add('selected');
        checkbox.checked = true;
    }
    updateBulkActions();
}

function updateBulkActions() {
    const bulkBar = document.getElementById('bulkActions');
    const countEl = document.getElementById('selectedCount');

    if (selectedIds.size > 0) {
        bulkBar.classList.remove('d-none');
        countEl.textContent = selectedIds.size;
    } else {
        bulkBar.classList.add('d-none');
    }
}

function clearSelection() {
    selectedIds.clear();
    document.querySelectorAll('.media-card.selected').forEach(function(card) {
        card.classList.remove('selected');
        card.querySelector('.media-checkbox').checked = false;
    });
    updateBulkActions();
}

// 批次刪除
function bulkDelete() {
    if (!selectedIds.size) return;
    if (!confirm('確定要刪除選取的 ' + selectedIds.size + ' 個檔案嗎？此操作無法復原。')) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('{{ route("admin.media.bulk-destroy") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ ids: Array.from(selectedIds) }),
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || '刪除失敗');
        }
    })
    .catch(function() {
        alert('操作失敗，請重試');
    });
}

// 單個刪除
function deleteMedia(id) {
    if (!confirm('確定要刪除此檔案嗎？此操作無法復原。')) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const url = '{{ route("admin.media.index") }}/' + id;

    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            const card = document.querySelector('.media-card[data-id="' + id + '"]');
            if (card) card.remove();
        } else {
            alert(data.message || '刪除失敗');
        }
    })
    .catch(function() {
        alert('操作失敗，請重試');
    });
}

// 複製網址
function copyUrl(url) {
    navigator.clipboard.writeText(url).then(function() {
        // 簡單提示
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = '<div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-0" role="alert">已複製網址<button type="button" class="btn-close btn-close-sm" data-coreui-dismiss="alert"></button></div>';
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 2000);
    });
}

// Alt text 編輯
function editAltText(id) {
    const card = document.querySelector('.media-card[data-id="' + id + '"]');
    card.classList.add('editing');
    document.getElementById('altForm-' + id).style.display = 'block';
    document.getElementById('altInput-' + id).focus();
}

function cancelAltText(id) {
    const card = document.querySelector('.media-card[data-id="' + id + '"]');
    card.classList.remove('editing');
    document.getElementById('altForm-' + id).style.display = 'none';
}

function saveAltText(id) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const altText = document.getElementById('altInput-' + id).value;
    const url = '{{ route("admin.media.index") }}/' + id;

    fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ alt_text: altText }),
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            cancelAltText(id);
        } else {
            alert(data.message || '更新失敗');
        }
    })
    .catch(function() {
        alert('操作失敗，請重試');
    });
}

// HTML 轉義
function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
