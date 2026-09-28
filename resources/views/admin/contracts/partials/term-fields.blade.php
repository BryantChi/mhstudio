{{--
    合約期間欄位（建立／編輯共用）。
    合約自「上線日」起算：簽約時填預計交件日 + 期間月數，自動帶出預估起訖日；
    實際上線時於詳情頁轉為「執行中」，再以實際上線日覆寫。
    傳入：$contract（編輯時；建立時為 null）
--}}
@php
    $bufferDays = \App\Models\Contract::goLiveBufferDays();
@endphp
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="expected_delivery_date" class="form-label">預計交件日</label>
        <input type="date" class="form-control @error('expected_delivery_date') is-invalid @enderror"
               id="expected_delivery_date" name="expected_delivery_date"
               value="{{ old('expected_delivery_date', $contract?->expected_delivery_date?->format('Y-m-d')) }}">
        @error('expected_delivery_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="term_months" class="form-label">合約期間（月）</label>
        <input type="number" class="form-control @error('term_months') is-invalid @enderror"
               id="term_months" name="term_months" min="1" max="600" placeholder="例：12"
               value="{{ old('term_months', $contract?->term_months) }}">
        @error('term_months') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
    填入預計交件日與期間後，會自動帶出預估起訖日（交件日 + {{ $bufferDays }} 天視為上線日），仍可手動調整。
    實際上線時，在詳情頁將狀態改為「執行中」並填入上線日，即會以實際日期重算。
</small>

@push('scripts')
<script>
(function () {
    const bufferDays = {{ $bufferDays }};
    const delivery = document.getElementById('expected_delivery_date');
    const term = document.getElementById('term_months');
    const start = document.getElementById('start_date');
    const end = document.getElementById('end_date');

    // 以 UTC 計算，避免時區造成 toISOString() 跨日
    const parse = (v) => { const [y, m, d] = v.split('-').map(Number); return new Date(Date.UTC(y, m - 1, d)); };
    const fmt = (dt) => dt.toISOString().slice(0, 10);

    // 與後端 Contract::termDatesFrom() 同一公式：結束日 = 開始 + N 個月 − 1 天，月底不溢位
    function addMonthsNoOverflow(dt, months) {
        const y = dt.getUTCFullYear(), m = dt.getUTCMonth() + months, d = dt.getUTCDate();
        const lastDay = new Date(Date.UTC(y, m + 1, 0)).getUTCDate();
        return new Date(Date.UTC(y, m, Math.min(d, lastDay)));
    }

    function recalc() {
        if (!delivery.value) return;
        const goLive = parse(delivery.value);
        goLive.setUTCDate(goLive.getUTCDate() + bufferDays);
        start.value = fmt(goLive);

        const months = parseInt(term.value, 10);
        if (months > 0) {
            const e = addMonthsNoOverflow(goLive, months);
            e.setUTCDate(e.getUTCDate() - 1);
            end.value = fmt(e);
        }
    }

    // 只在使用者改動這兩欄時才重算，不在載入時覆蓋既有（可能手動調整過）的日期
    delivery.addEventListener('change', recalc);
    term.addEventListener('change', recalc);
})();
</script>
@endpush
