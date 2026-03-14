@extends('layouts.admin')

@section('title', '新增報價單')

@php
    $breadcrumbs = [
        ['title' => '報價單管理', 'url' => route('admin.quotes.index')],
        ['title' => '新增報價單', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">新增報價單</h2>
        <p class="text-muted">建立新的報價單</p>
    </div>
    <div class="col-md-6 text-md-end">
        <button type="button" class="btn btn-outline-primary" data-coreui-toggle="collapse" data-coreui-target="#quickCreatePanel">
            <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-speedometer"></use></svg>
            從服務方案快速建立
        </button>
    </div>
</div>

{{-- 快速建立面板 --}}
<div class="collapse mb-4" id="quickCreatePanel">
    <div class="card border-primary">
        <div class="card-header bg-primary bg-opacity-10">
            <strong>從服務方案快速建立</strong>
            <span class="text-muted ms-2">— 選擇方案後自動帶入報價項目與標準條款</span>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- 網站方案 --}}
                @if(isset($servicePlans['website']) && $servicePlans['website']->isNotEmpty())
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">網站設計方案</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($servicePlans['website'] as $plan)
                        <button type="button" class="btn btn-sm {{ $plan->is_featured ? 'btn-primary' : 'btn-outline-primary' }} quick-plan-btn"
                                data-plan-id="{{ $plan->id }}"
                                data-plan-name="{{ $plan->title }}"
                                data-plan-price="{{ $plan->price }}"
                                data-plan-items='@json($plan->items->map(fn($i) => $i->name)->toArray())'>
                            {{ $plan->name }}
                            <small class="d-block">{{ $plan->formatted_price }}</small>
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- 附加服務 --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">附加項目（可多選）</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(['hosting', 'maintenance', 'addon'] as $type)
                            @if(isset($servicePlans[$type]))
                                @foreach($servicePlans[$type] as $plan)
                                <label class="btn btn-sm btn-outline-secondary addon-plan-label">
                                    <input type="checkbox" class="d-none addon-plan-check"
                                           data-plan-name="{{ $plan->title }}"
                                           data-plan-price="{{ $plan->price }}"
                                           data-plan-price-label="{{ $plan->price_label }}"
                                           data-plan-cycle="{{ $plan->billing_cycle_label }}">
                                    {{ $plan->name }}
                                    <small class="d-block">{{ $plan->formatted_price }}</small>
                                </label>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="text-end mt-2">
                <button type="button" class="btn btn-primary" id="applyQuickPlan">
                    <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-arrow-right"></use></svg>
                    套用到報價單
                </button>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.quotes.store') }}" id="quoteForm" onsubmit="showLoading()">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><strong>報價項目</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">報價單標題 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}" placeholder="例如：官網改版報價" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">說明</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="報價說明，例如專案範圍摘要">{{ old('description') }}</textarea>
                    </div>

                    {{-- 動態項目列表 --}}
                    <div class="table-responsive">
                        <table class="table" id="itemsTable">
                            <thead>
                                <tr>
                                    <th width="40">#</th>
                                    <th>項目說明 <span class="text-danger">*</span></th>
                                    <th width="100">數量</th>
                                    <th width="80">單位</th>
                                    <th width="150">單價</th>
                                    <th width="130">小計</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr class="item-row">
                                    <td class="align-middle text-center item-number">1</td>
                                    <td><input type="text" class="form-control form-control-sm" name="items[0][description]" placeholder="項目名稱，例如：首頁設計" required></td>
                                    <td><input type="number" class="form-control form-control-sm item-qty" name="items[0][quantity]" value="1" min="0.01" step="0.01" required></td>
                                    <td>
                                        <select class="form-select form-select-sm" name="items[0][unit]">
                                            <option value="項" selected>項</option>
                                            <option value="小時">小時</option>
                                            <option value="天">天</option>
                                            <option value="月">月</option>
                                            <option value="頁">頁</option>
                                            <option value="個">個</option>
                                        </select>
                                    </td>
                                    <td><input type="number" class="form-control form-control-sm item-price" name="items[0][unit_price]" placeholder="0" min="0" step="0.01" required></td>
                                    <td class="item-amount text-end align-middle">NT$ 0</td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-item" data-coreui-toggle="tooltip" title="移除此項目" disabled>✕</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addItem">
                        <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg> 新增項目
                    </button>

                    {{-- 金額匯總 --}}
                    <hr>
                    <div class="row mt-4 justify-content-end">
                        <div class="col-md-5">
                            <table class="table table-sm">
                                <tr><td>小計</td><td class="text-end" id="subtotal">NT$ 0</td></tr>
                                <tr>
                                    <td>折扣</td>
                                    <td class="text-end">
                                        <input type="number" class="form-control form-control-sm text-end" name="discount"
                                               id="discount" value="{{ old('discount', 0) }}" min="0" step="0.01" style="width: 120px; display: inline-block;">
                                    </td>
                                </tr>
                                <tr>
                                    <td>稅率 (%)</td>
                                    <td class="text-end">
                                        <input type="number" class="form-control form-control-sm text-end" name="tax_rate"
                                               id="taxRate" value="{{ old('tax_rate', 0) }}" min="0" max="100" step="0.01" style="width: 80px; display: inline-block;">
                                    </td>
                                </tr>
                                <tr><td>稅額</td><td class="text-end" id="taxAmount">NT$ 0</td></tr>
                                <tr class="fw-bold"><td>總計</td><td class="text-end" id="totalAmount">NT$ 0</td></tr>
                            </table>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="notes" class="form-label mb-0">備註 / 條款</label>
                            <button type="button" class="btn btn-sm btn-outline-info" id="fillStandardNotes">
                                <svg class="icon me-1" style="width:14px;height:14px"><use xlink:href="/assets/icons/free.svg#cil-file"></use></svg>
                                帶入標準條款
                            </button>
                        </div>
                        <textarea class="form-control" id="notes" name="notes" rows="6" placeholder="付款條件、交付時程等備註">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><strong>報價設定</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="client_id" class="form-label">客戶 <span class="text-danger">*</span></label>
                        <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                            <option value="">選擇客戶</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $selectedClientId) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                        @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="project_id" class="form-label">關聯專案</label>
                        <select class="form-select" id="project_id" name="project_id">
                            <option value="">不關聯</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="status" value="draft">
                    <div class="mb-3">
                        <label for="valid_until" class="form-label">有效期限</label>
                        <input type="date" class="form-control" id="valid_until" name="valid_until"
                               value="{{ old('valid_until', now()->addDays(30)->format('Y-m-d')) }}">
                    </div>
                    <div class="mb-3">
                        <label for="currency" class="form-label">幣別</label>
                        <select class="form-select" id="currency" name="currency">
                            <option value="TWD" {{ old('currency', 'TWD') == 'TWD' ? 'selected' : '' }}>TWD (新台幣)</option>
                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD (美元)</option>
                            <option value="CNY" {{ old('currency') == 'CNY' ? 'selected' : '' }}>CNY (人民幣)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg> 儲存報價單
                        </button>
                        <a href="{{ route('admin.quotes.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 1;
    const itemsBody = document.getElementById('itemsBody');

    document.getElementById('addItem').addEventListener('click', function() {
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td class="align-middle text-center item-number"></td>
            <td><input type="text" class="form-control form-control-sm" name="items[${itemIndex}][description]" placeholder="項目名稱，例如：首頁設計" required></td>
            <td><input type="number" class="form-control form-control-sm item-qty" name="items[${itemIndex}][quantity]" value="1" min="0.01" step="0.01" required></td>
            <td>
                <select class="form-select form-select-sm" name="items[${itemIndex}][unit]">
                    <option value="項" selected>項</option>
                    <option value="小時">小時</option>
                    <option value="天">天</option>
                    <option value="月">月</option>
                    <option value="頁">頁</option>
                    <option value="個">個</option>
                </select>
            </td>
            <td><input type="number" class="form-control form-control-sm item-price" name="items[${itemIndex}][unit_price]" placeholder="0" min="0" step="0.01" required></td>
            <td class="item-amount text-end align-middle">NT$ 0</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-item" data-coreui-toggle="tooltip" title="移除此項目">✕</button></td>
        `;
        itemsBody.appendChild(row);
        itemIndex++;
        updateRemoveButtons();
        updateRowNumbers();
        bindCalculation();
        row.querySelector('input[name$="[description]"]').focus();
    });

    itemsBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('tr').remove();
            updateRemoveButtons();
            updateRowNumbers();
            calculateTotal();
        }
    });

    function updateRowNumbers() {
        document.querySelectorAll('.item-row').forEach((row, index) => {
            row.querySelector('.item-number').textContent = index + 1;
        });
    }

    function updateRemoveButtons() {
        const rows = itemsBody.querySelectorAll('.item-row');
        rows.forEach(row => {
            row.querySelector('.remove-item').disabled = rows.length <= 1;
        });
    }

    function bindCalculation() {
        document.querySelectorAll('.item-qty, .item-price').forEach(input => {
            input.removeEventListener('input', calculateTotal);
            input.addEventListener('input', calculateTotal);
        });
    }

    function calculateTotal() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const amount = qty * price;
            row.querySelector('.item-amount').textContent = 'NT$ ' + amount.toLocaleString();
            subtotal += amount;
        });

        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const taxRate = parseFloat(document.getElementById('taxRate').value) || 0;
        const taxable = subtotal - discount;
        const tax = Math.round(taxable * (taxRate / 100));
        const total = taxable + tax;

        document.getElementById('subtotal').textContent = 'NT$ ' + subtotal.toLocaleString();
        document.getElementById('taxAmount').textContent = 'NT$ ' + tax.toLocaleString();
        document.getElementById('totalAmount').textContent = 'NT$ ' + total.toLocaleString();
    }

    bindCalculation();
    document.getElementById('discount').addEventListener('input', calculateTotal);
    document.getElementById('taxRate').addEventListener('input', calculateTotal);

    // ===== 從服務方案快速建立 =====
    var selectedPlanBtn = null;

    // 點選網站方案按鈕（單選）
    document.querySelectorAll('.quick-plan-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.quick-plan-btn').forEach(function(b) { b.classList.remove('active'); b.classList.remove('btn-success'); });
            btn.classList.add('active');
            btn.classList.add('btn-success');
            selectedPlanBtn = btn;
        });
    });

    // 附加服務 checkbox 樣式切換
    document.querySelectorAll('.addon-plan-check').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var label = cb.closest('.addon-plan-label');
            if (cb.checked) {
                label.classList.remove('btn-outline-secondary');
                label.classList.add('btn-success');
            } else {
                label.classList.remove('btn-success');
                label.classList.add('btn-outline-secondary');
            }
        });
    });

    // 套用到報價單
    document.getElementById('applyQuickPlan')?.addEventListener('click', function() {
        if (!selectedPlanBtn) {
            alert('請先選擇一個網站設計方案');
            return;
        }

        var planName = selectedPlanBtn.dataset.planName;
        var planPrice = parseFloat(selectedPlanBtn.dataset.planPrice) || 0;
        var planItems = [];
        try { planItems = JSON.parse(selectedPlanBtn.dataset.planItems); } catch(e) {}

        // 清空現有項目
        itemsBody.innerHTML = '';
        itemIndex = 0;

        // 主方案項目
        var desc = planName + '（含：' + planItems.slice(0, 5).join('、') + (planItems.length > 5 ? '…等 ' + planItems.length + ' 項' : '') + '）';
        addItemRow(desc, 1, '項', planPrice);

        // 附加項目
        document.querySelectorAll('.addon-plan-check:checked').forEach(function(cb) {
            var addonName = cb.dataset.planName;
            var addonPrice = parseFloat(cb.dataset.planPrice) || 0;
            var cycle = cb.dataset.planCycle;
            var priceLabel = cb.dataset.planPriceLabel;
            var unit = cycle === '年繳' ? '年' : (cycle === '月繳' ? '月' : '項');
            addItemRow(addonName + (priceLabel ? '（' + priceLabel + '）' : ''), 1, unit, addonPrice);
        });

        // 更新標題
        var titleInput = document.getElementById('title');
        if (!titleInput.value) {
            titleInput.value = planName + ' 網站設計報價';
        }

        updateRemoveButtons();
        updateRowNumbers();
        calculateTotal();

        // 收合面板
        var panel = document.getElementById('quickCreatePanel');
        if (panel && typeof coreui !== 'undefined') {
            coreui.Collapse.getInstance(panel)?.hide();
        }
    });

    function addItemRow(description, qty, unit, price) {
        var row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML =
            '<td class="align-middle text-center item-number"></td>' +
            '<td><input type="text" class="form-control form-control-sm" name="items[' + itemIndex + '][description]" value="' + description.replace(/"/g, '&quot;') + '" required></td>' +
            '<td><input type="number" class="form-control form-control-sm item-qty" name="items[' + itemIndex + '][quantity]" value="' + qty + '" min="0.01" step="0.01" required></td>' +
            '<td><select class="form-select form-select-sm" name="items[' + itemIndex + '][unit]">' +
                '<option value="項"' + (unit === '項' ? ' selected' : '') + '>項</option>' +
                '<option value="小時"' + (unit === '小時' ? ' selected' : '') + '>小時</option>' +
                '<option value="天"' + (unit === '天' ? ' selected' : '') + '>天</option>' +
                '<option value="月"' + (unit === '月' ? ' selected' : '') + '>月</option>' +
                '<option value="年"' + (unit === '年' ? ' selected' : '') + '>年</option>' +
                '<option value="頁"' + (unit === '頁' ? ' selected' : '') + '>頁</option>' +
                '<option value="個"' + (unit === '個' ? ' selected' : '') + '>個</option>' +
            '</select></td>' +
            '<td><input type="number" class="form-control form-control-sm item-price" name="items[' + itemIndex + '][unit_price]" value="' + price + '" min="0" step="0.01" required></td>' +
            '<td class="item-amount text-end align-middle">NT$ ' + (qty * price).toLocaleString() + '</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-item" data-coreui-toggle="tooltip" title="移除此項目">✕</button></td>';
        itemsBody.appendChild(row);
        itemIndex++;
        bindCalculation();
    }

    // ===== 帶入標準條款 =====
    document.getElementById('fillStandardNotes')?.addEventListener('click', function() {
        var notes = document.getElementById('notes');
        if (notes.value && !confirm('目前備註欄已有內容，確定要覆蓋嗎？')) return;

        notes.value = '一、付款方式\n' +
            '簽約金 50%，驗收完成後付尾款 50%。\n\n' +
            '二、驗收方式\n' +
            '1. 乙方完成製作後，甲方應於 7 個工作日內進行驗收。\n' +
            '2. 驗收期間如有 Bug 或功能異常，乙方應於 7 日內修正完畢。\n' +
            '3. 上線後 7 日內提供後台操作教育訓練。\n\n' +
            '三、保固範圍\n' +
            '包含程式錯誤修正、系統問題排除（不含新功能開發）。\n\n' +
            '四、修改定義\n' +
            '每次修改以不超過原設計 30% 為原則，超出規格另行報價。\n\n' +
            '五、注意事項\n' +
            '1. 以上報價有效期為 30 天。\n' +
            '2. 網站設計製作不含文案撰寫，如需文案服務請另行報價。\n' +
            '3. 網站圖片如需購買圖庫素材，費用由甲方負擔。\n' +
            '4. 網域名稱註冊費用不包含在本報價中（約 NT$ 800/年）。\n' +
            '5. 客戶需提供網站所需文字、圖片素材。\n' +
            '6. 本報價未含營業稅，如需開立發票另加 5% 營業稅。';
    });
});
</script>
@endpush
@endsection
