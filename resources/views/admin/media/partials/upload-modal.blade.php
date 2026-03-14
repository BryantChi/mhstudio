{{-- 上傳媒體 Modal --}}
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">
                    <svg class="icon me-2">
                        <use xlink:href="/assets/icons/free.svg#cil-cloud-upload"></use>
                    </svg>
                    上傳檔案
                </h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- 拖放區域 --}}
                <div id="dropZone" class="drop-zone mb-3">
                    <div class="drop-zone-content">
                        <svg class="drop-zone-icon">
                            <use xlink:href="/assets/icons/free.svg#cil-cloud-upload"></use>
                        </svg>
                        <p class="drop-zone-text">將檔案拖放到此處</p>
                        <p class="drop-zone-hint">或點擊下方按鈕選擇檔案</p>
                        <p class="drop-zone-formats text-muted small">
                            支援格式：JPG, PNG, GIF, WebP, SVG, PDF, DOC, DOCX, XLS, XLSX, ZIP（最大 10MB）
                        </p>
                    </div>
                </div>

                {{-- 檔案選擇 --}}
                <div class="text-center mb-3">
                    <input type="file" id="fileInput" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.doc,.docx,.xls,.xlsx,.zip" class="d-none">
                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('fileInput').click()">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-folder-open"></use>
                        </svg>
                        選擇檔案
                    </button>
                </div>

                {{-- 上傳佇列 --}}
                <div id="uploadQueue" class="d-none">
                    <h6 class="mb-3">上傳進度</h6>
                    <div id="uploadList"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">關閉</button>
            </div>
        </div>
    </div>
</div>
