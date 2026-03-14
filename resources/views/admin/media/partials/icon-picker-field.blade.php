{{--
    Icon Picker Field — 圖示選擇器（CoreUI 內建 + 媒體庫自訂圖片）
    @include('admin.media.partials.icon-picker-field', [
        'fieldName'   => 'icon',
        'fieldId'     => 'icon',
        'fieldValue'  => old('icon'),
        'placeholder' => '選擇或輸入圖示',
    ])
    ※ 使用此元件的頁面須同時 @include('admin.media.partials.picker-modal')（只需放一次）
--}}
@php
    $fieldName   = $fieldName   ?? 'icon';
    $fieldId     = $fieldId     ?? $fieldName;
    $fieldValue  = $fieldValue  ?? '';
    $placeholder = $placeholder ?? '選擇圖示或從媒體庫上傳';
    $previewId   = 'icon-picker-preview-' . $fieldId;
    $panelId     = 'icon-picker-panel-' . $fieldId;
    $isUrl       = $fieldValue && (str_starts_with($fieldValue, 'http') || str_starts_with($fieldValue, '/storage'));

    $builtinIcons = [
        'cil-code' => '程式碼',
        'cil-mobile' => '手機',
        'cil-globe-alt' => '網頁',
        'cil-settings' => '系統',
        'cil-chart-line' => '數據',
        'cil-lightbulb' => '創意',
        'cil-brush' => '設計',
        'cil-cloud' => '雲端',
        'cil-shield-alt' => '安全',
        'cil-people' => '團隊',
        'cil-layers' => '架構',
        'cil-storage' => '資料庫',
        'cil-calculator' => '計算器',
        'cil-screen-desktop' => '桌面',
        'cil-check' => '勾選',
        'cil-star' => '星星',
        'cil-dollar' => '費用',
        'cil-briefcase' => '商務',
        'cil-folder' => '資料夾',
        'cil-image' => '圖片',
        'cil-envelope-closed' => '信件',
        'cil-clock' => '時間',
        'cil-location-pin' => '地點',
        'cil-phone' => '電話',
    ];
@endphp

{{-- 目前選擇預覽 --}}
<div class="d-flex align-items-center gap-2 mb-2" id="{{ $previewId }}" style="{{ $fieldValue ? '' : 'display:none;' }}">
    @if($isUrl)
        <img src="{{ $fieldValue }}" alt="icon preview" style="width:36px;height:36px;object-fit:contain;border:1px solid #dee2e6;border-radius:4px;padding:2px;">
    @else
        <span style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;border:1px solid #dee2e6;border-radius:4px;">
            <svg class="icon"><use xlink:href="/assets/icons/free.svg#{{ $fieldValue ?: 'cil-image' }}"></use></svg>
        </span>
    @endif
    <small class="text-muted" id="{{ $previewId }}-label">{{ $fieldValue }}</small>
</div>

{{-- Hidden input --}}
<input type="hidden"
       id="{{ $fieldId }}"
       name="{{ $fieldName }}"
       value="{{ $fieldValue }}">

{{-- 按鈕列 --}}
<div class="d-flex gap-2 mb-2">
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleIconPanel('{{ $panelId }}')">
        <svg class="icon me-1" style="width:14px;height:14px"><use xlink:href="/assets/icons/free.svg#cil-apps"></use></svg>
        選擇內建圖示
    </button>
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openMediaPicker('{{ $fieldId }}')" title="從媒體庫選擇自訂圖示">
        <svg class="icon me-1" style="width:14px;height:14px"><use xlink:href="/assets/icons/free.svg#cil-image"></use></svg>
        從媒體庫選擇
    </button>
    @if($fieldValue)
    <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearIconPicker('{{ $fieldId }}')">
        <svg class="icon" style="width:14px;height:14px"><use xlink:href="/assets/icons/free.svg#cil-x"></use></svg>
    </button>
    @endif
</div>

{{-- CoreUI 圖示選擇面板 --}}
<div id="{{ $panelId }}" class="icon-picker-panel" style="display:none;">
    <div class="icon-picker-grid">
        @foreach($builtinIcons as $iconName => $iconLabel)
        <button type="button"
                class="icon-picker-tile {{ $fieldValue === $iconName ? 'active' : '' }}"
                data-icon="{{ $iconName }}"
                data-target="{{ $fieldId }}"
                title="{{ $iconLabel }}（{{ $iconName }}）"
                onclick="selectBuiltinIcon(this, '{{ $fieldId }}')">
            <svg class="icon"><use xlink:href="/assets/icons/free.svg#{{ $iconName }}"></use></svg>
            <span>{{ $iconLabel }}</span>
        </button>
        @endforeach
    </div>
</div>

<div class="form-text">點「選擇內建圖示」瀏覽 CoreUI 圖示，或點「從媒體庫選擇」上傳自訂圖片</div>
@error($fieldName)
    <div class="invalid-feedback d-block">{{ $message }}</div>
@enderror

@once
@push('styles')
<style>
    .icon-picker-panel {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        background: #f8f9fa;
        max-height: 240px;
        overflow-y: auto;
    }
    .icon-picker-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(72px, 1fr));
        gap: 6px;
    }
    .icon-picker-tile {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        padding: 8px 4px;
        border: 1px solid transparent;
        border-radius: 4px;
        background: #fff;
        cursor: pointer;
        transition: all 0.15s;
        font-size: 0;
    }
    .icon-picker-tile:hover {
        border-color: #86b7fe;
        background: #e7f1ff;
    }
    .icon-picker-tile.active {
        border-color: #0d6efd;
        background: #cfe2ff;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
    }
    .icon-picker-tile .icon {
        width: 22px;
        height: 22px;
    }
    .icon-picker-tile span {
        font-size: 9px;
        color: #6c757d;
        line-height: 1.2;
        text-align: center;
        word-break: keep-all;
    }
    .icon-picker-tile.active span {
        color: #0d6efd;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 展開/收合圖示面板
    window.toggleIconPanel = function(panelId) {
        var panel = document.getElementById(panelId);
        if (panel) {
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }
    };

    // 選擇內建圖示
    window.selectBuiltinIcon = function(tileEl, inputId) {
        var iconName = tileEl.getAttribute('data-icon');
        var input = document.getElementById(inputId);
        if (!input) return;

        // 清除同面板其他 active
        var panel = tileEl.closest('.icon-picker-panel');
        if (panel) {
            panel.querySelectorAll('.icon-picker-tile.active').forEach(function(t) { t.classList.remove('active'); });
        }
        tileEl.classList.add('active');

        // 寫入值
        input.value = iconName;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));

        // 更新預覽
        updateIconPreview(inputId, iconName);

        // 收合面板
        if (panel) panel.style.display = 'none';
    };

    // 清除圖示
    window.clearIconPicker = function(inputId) {
        var input = document.getElementById(inputId);
        if (input) {
            input.value = '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
        updateIconPreview(inputId, '');
    };

    // 即時預覽
    function updateIconPreview(inputId, value) {
        var previewEl = document.getElementById('icon-picker-preview-' + inputId);
        if (!previewEl) return;

        if (!value) {
            previewEl.style.display = 'none';
            return;
        }

        previewEl.style.display = 'flex';

        var isUrl = value.indexOf('http') === 0 || value.indexOf('/storage') === 0;
        if (isUrl) {
            previewEl.innerHTML = '<img src="' + value + '" alt="icon" style="width:36px;height:36px;object-fit:contain;border:1px solid #dee2e6;border-radius:4px;padding:2px;">'
                + '<small class="text-muted" id="icon-picker-preview-' + inputId + '-label">' + value.split('/').pop() + '</small>';
        } else {
            previewEl.innerHTML = '<span style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;border:1px solid #dee2e6;border-radius:4px;">'
                + '<svg class="icon"><use xlink:href="/assets/icons/free.svg#' + value + '"></use></svg>'
                + '</span>'
                + '<small class="text-muted" id="icon-picker-preview-' + inputId + '-label">' + value + '</small>';
        }
    }

    // 監聽 media picker 回填（hidden input 的 input/change 事件）
    document.querySelectorAll('[id^="icon-picker-preview-"]').forEach(function(previewEl) {
        var inputId = previewEl.id.replace('icon-picker-preview-', '');
        // 避免匹配到 -label 結尾的元素
        if (inputId.indexOf('-label') !== -1) return;
        var input = document.getElementById(inputId);
        if (!input) return;

        input.addEventListener('input', function() {
            updateIconPreview(inputId, this.value.trim());
        });
        input.addEventListener('change', function() {
            updateIconPreview(inputId, this.value.trim());
        });
    });
});
</script>
@endpush
@endonce
