{{--
    合約期間欄位（建立／編輯共用）。
    期間起算點三選一：上線日（預設）／實際交件日／自訂日期。
    上線日、實際交件日在詳情頁記錄；尚未記錄時以「預計交件日 + 緩衝天數」帶出預估起訖日。
    傳入：$contract（編輯時；建立時為 null）
--}}
@php
    $bufferDays = \App\Models\Contract::goLiveBufferDays();
    $termAnchor = old('term_anchor', $contract?->term_anchor ?? 'go_live');
@endphp
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="expected_delivery_date" class="form-label">預計交件日</label>
        <input type="date" class="form-control @error('expected_delivery_date') is-invalid @enderror"
               id="expected_delivery_date" name="expected_delivery_date"
               value="{{ old('expected_delivery_date', $contract?->expected_delivery_date?->format('Y-m-d')) }}">
        @error('expected_delivery_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="term_months" class="form-label">合約期間（月）</label>
        <input type="number" class="form-control @error('term_months') is-invalid @enderror"
               id="term_months" name="term_months" min="1" max="600" placeholder="例：12"
               value="{{ old('term_months', $contract?->term_months) }}">
        @error('term_months') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="term_anchor" class="form-label">期間起算點</label>
        {{-- 已記錄的實際日期交給 JS：有值時以實際日期起算，避免改月數時被預估值蓋掉已確定的起訖日 --}}
        <select class="form-select @error('term_anchor') is-invalid @enderror" id="term_anchor" name="term_anchor"
                data-go-live-date="{{ $contract?->go_live_date?->format('Y-m-d') }}"
                data-delivery-date="{{ $contract?->actual_delivery_date?->format('Y-m-d') }}">
            @foreach(\App\Models\Contract::TERM_ANCHORS as $value => $label)
                <option value="{{ $value }}" @selected($termAnchor === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('term_anchor') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="start_date" class="form-label">開始日期</label>
        <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date"
               value="{{ old('start_date', $contract?->start_date?->format('Y-m-d')) }}">
        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="end_date" class="form-label">結束日期</label>
        <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date"
               value="{{ old('end_date', $contract?->end_date?->format('Y-m-d')) }}">
        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<small class="text-muted d-block mb-3" style="margin-top: -0.5rem;">
    起算點為上線日或實際交件日時：該日期尚未記錄前，以預計交件日 + {{ $bufferDays }} 天帶出預估起訖日；實際日期在詳情頁記錄後會自動重算。
    起算點為自訂日期時：填入開始日期即帶出結束日。起訖日皆可手動調整。
</small>

@push('scripts')
<script>
(function () {
    const bufferDays = {{ $bufferDays }};
    const delivery = document.getElementById('expected_delivery_date');
    const term = document.getElementById('term_months');
    const anchor = document.getElementById('term_anchor');
    const start = document.getElementById('start_date');
    const end = document.getElementById('end_date');

    // 編輯頁才有值；建立頁為空字串
    const recorded = { go_live: anchor.dataset.goLiveDate, delivery: anchor.dataset.deliveryDate };

    // 以 UTC 計算，避免時區造成 toISOString() 跨日
    const parse = (v) => { const [y, m, d] = v.split('-').map(Number); return new Date(Date.UTC(y, m - 1, d)); };
    const fmt = (dt) => dt.toISOString().slice(0, 10);

    // 與後端 Contract::termDatesFrom() 同一公式：結束日 = 開始 + N 個月 − 1 天，月底不溢位
    function addMonthsNoOverflow(dt, months) {
        const y = dt.getUTCFullYear(), m = dt.getUTCMonth() + months, d = dt.getUTCDate();
        const lastDay = new Date(Date.UTC(y, m + 1, 0)).getUTCDate();
        return new Date(Date.UTC(y, m, Math.min(d, lastDay)));
    }

    function fillEnd(from) {
        const months = parseInt(term.value, 10);
        if (months > 0) {
            const e = addMonthsNoOverflow(from, months);
            e.setUTCDate(e.getUTCDate() - 1);
            end.value = fmt(e);
        }
    }

    function recalc() {
        if (anchor.value === 'custom') {
            if (start.value) fillEnd(parse(start.value));
            return;
        }

        let from;
        if (recorded[anchor.value]) {
            from = parse(recorded[anchor.value]);
        } else if (delivery.value) {
            from = parse(delivery.value);
            from.setUTCDate(from.getUTCDate() + bufferDays);
        } else {
            return;
        }
        start.value = fmt(from);
        fillEnd(from);
    }

    // 只在使用者改動欄位時才重算，不在載入時覆蓋既有（可能手動調整過）的日期
    // 自訂起算與預計交件日無關；放行的話會把使用者手動調過的結束日重算蓋掉
    delivery.addEventListener('change', () => { if (anchor.value !== 'custom') recalc(); });
    term.addEventListener('change', recalc);
    anchor.addEventListener('change', recalc);
    // 自訂起算時開始日期就是起點，要連動結束日；其他起算點下手動改開始日視為微調，不連動
    start.addEventListener('change', () => { if (anchor.value === 'custom') recalc(); });
})();
</script>
@endpush
