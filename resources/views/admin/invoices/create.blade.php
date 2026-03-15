@extends('layouts.admin')

@section('title', '新增發票')

@php
    $breadcrumbs = [
        ['title' => '發票管理', 'url' => route('admin.invoices.index')],
        ['title' => '新增發票', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">新增發票</h2>
        <p class="text-muted">建立新的發票</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.invoices.store') }}" onsubmit="showLoading()">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><strong>發票項目</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">發票標題 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}" placeholder="例如：官網設計第一期款" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

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

                    <hr>
                    <div class="row mt-4 justify-content-end">
                        <div class="col-md-5">
                            <table class="table table-sm">
                                <tr><td>小計</td><td class="text-end" id="subtotal">NT$ 0</td></tr>
                                <tr><td>折扣</td><td class="text-end"><input type="number" class="form-control form-control-sm text-end" name="discount" id="discount" value="0" min="0" step="0.01" style="width:120px;display:inline-block;"></td></tr>
                                <tr><td>稅率 (%)</td><td class="text-end"><input type="number" class="form-control form-control-sm text-end" name="tax_rate" id="taxRate" value="0" min="0" max="100" step="0.01" style="width:80px;display:inline-block;"></td></tr>
                                <tr><td>稅額</td><td class="text-end" id="taxAmount">NT$ 0</td></tr>
                                <tr class="fw-bold"><td>總計</td><td class="text-end" id="totalAmount">NT$ 0</td></tr>
                            </table>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">備註</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="付款資訊（銀行帳號等）">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><strong>發票設定</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">客戶 <span class="text-danger">*</span></label>
                        <select class="form-select @error('client_id') is-invalid @enderror" name="client_id" required>
                            <option value="">選擇客戶</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $selectedClientId) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                        @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">關聯專案</label>
                        <select class="form-select" name="project_id">
                            <option value="">不關聯</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="status" value="draft">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">開立日期 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="issued_date" value="{{ old('issued_date', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">到期日 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="due_date" value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}" required>
                            <small class="text-muted">預設 30 天後到期</small>
                        </div>
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
                            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg> 儲存發票
                        </button>
                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-light">取消</a>
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
        if (e.target.classList.contains('remove-item')) { e.target.closest('tr').remove(); updateRemoveButtons(); updateRowNumbers(); calculateTotal(); }
    });

    function updateRowNumbers() {
        document.querySelectorAll('.item-row').forEach((row, index) => {
            row.querySelector('.item-number').textContent = index + 1;
        });
    }

    function updateRemoveButtons() {
        const rows = itemsBody.querySelectorAll('.item-row');
        rows.forEach(row => { row.querySelector('.remove-item').disabled = rows.length <= 1; });
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
        const tax = Math.round((subtotal - discount) * (taxRate / 100));
        document.getElementById('subtotal').textContent = 'NT$ ' + subtotal.toLocaleString();
        document.getElementById('taxAmount').textContent = 'NT$ ' + tax.toLocaleString();
        document.getElementById('totalAmount').textContent = 'NT$ ' + (subtotal - discount + tax).toLocaleString();
    }

    bindCalculation();
    document.getElementById('discount').addEventListener('input', calculateTotal);
    document.getElementById('taxRate').addEventListener('input', calculateTotal);
});
</script>
@endpush
@endsection
