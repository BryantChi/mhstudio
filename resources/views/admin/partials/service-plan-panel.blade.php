{{-- 共用：從服務方案快速建立（報價單／合約）。
     需傳入 $servicePlans（Service 依 type 分組），選用 $titleSuffix（自動帶入標題的後綴）。
     依賴 window.LineItems（line-items-script）來帶入項目。
     觸發按鈕：另置於頁面，使用 data-coreui-toggle="collapse" data-coreui-target="#quickCreatePanel"。 --}}
@php($titleSuffix = $titleSuffix ?? '報價')
<div class="collapse mb-4" id="quickCreatePanel">
    <div class="card border-primary">
        <div class="card-header bg-primary bg-opacity-10">
            <strong>從服務方案快速建立</strong>
            <span class="text-muted ms-2">— 選擇方案後自動帶入項目明細</span>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- 網站方案（單選） --}}
                @if(isset($servicePlans['website']) && $servicePlans['website']->isNotEmpty())
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">網站設計方案（可選，單選）</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($servicePlans['website'] as $plan)
                        <button type="button" class="btn btn-sm btn-outline-primary quick-plan-btn"
                                data-plan-name="{{ $plan->title }}"
                                data-plan-price="{{ $plan->price }}"
                                data-plan-items='@json($plan->items->map(fn($i) => $i->name)->toArray())'>
                            {{ $plan->title }}
                            @if($plan->is_featured)<span class="badge bg-warning text-dark ms-1">推薦</span>@endif
                            <small class="d-block">{{ $plan->formatted_price }}</small>
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- 附加服務（可多選） --}}
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
                                    {{ $plan->title }}
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
                    套用項目
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var selectedPlanBtn = null;

    // 網站方案（單選）：一次只亮一顆
    document.querySelectorAll('.quick-plan-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.quick-plan-btn').forEach(function (b) {
                b.classList.remove('active', 'btn-success');
                b.classList.add('btn-outline-primary');
            });
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('active', 'btn-success');
            selectedPlanBtn = btn;
        });
    });

    // 附加服務 checkbox 樣式切換
    document.querySelectorAll('.addon-plan-check').forEach(function (cb) {
        cb.addEventListener('change', function () {
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

    // 套用項目到報價單／合約
    var applyBtn = document.getElementById('applyQuickPlan');
    if (applyBtn) {
        applyBtn.addEventListener('click', function () {
            if (!window.LineItems) return;

            var checkedAddons = document.querySelectorAll('.addon-plan-check:checked');
            if (!selectedPlanBtn && checkedAddons.length === 0) {
                alert('請至少選擇一個網站方案或附加項目');
                return;
            }

            window.LineItems.reset();

            // 主方案（非必選）
            if (selectedPlanBtn) {
                var planName = selectedPlanBtn.dataset.planName;
                var planPrice = parseFloat(selectedPlanBtn.dataset.planPrice) || 0;
                var planItems = [];
                try { planItems = JSON.parse(selectedPlanBtn.dataset.planItems); } catch (e) {}
                var desc = planName + '（含：' + planItems.slice(0, 5).join('、') +
                    (planItems.length > 5 ? '…等 ' + planItems.length + ' 項' : '') + '）';
                window.LineItems.addItemRow(desc, 1, '項', planPrice);
            }

            // 附加項目（可多選）
            checkedAddons.forEach(function (cb) {
                var addonName = cb.dataset.planName;
                var addonPrice = parseFloat(cb.dataset.planPrice) || 0;
                var cycle = cb.dataset.planCycle;
                var priceLabel = cb.dataset.planPriceLabel;
                var unit = cycle === '年繳' ? '年' : (cycle === '月繳' ? '月' : '項');
                window.LineItems.addItemRow(addonName + (priceLabel ? '（' + priceLabel + '）' : ''), 1, unit, addonPrice);
            });

            // 標題：有選主方案才自動帶入
            var titleInput = document.getElementById('title');
            if (titleInput && !titleInput.value && selectedPlanBtn) {
                titleInput.value = selectedPlanBtn.dataset.planName + ' {{ $titleSuffix }}';
            }

            window.LineItems.refresh();

            var panel = document.getElementById('quickCreatePanel');
            if (panel && typeof coreui !== 'undefined') {
                coreui.Collapse.getInstance(panel)?.hide();
            }
        });
    }
});
</script>
