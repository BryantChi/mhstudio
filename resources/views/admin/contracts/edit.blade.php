@extends('layouts.admin')

@section('title', '編輯合約')

@php
    $breadcrumbs = [
        ['title' => '合約管理', 'url' => route('admin.contracts.index')],
        ['title' => '編輯合約', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">編輯合約</h2>
        <p class="text-muted">{{ $contract->contract_number }}</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-light">查看</a>
    </div>
</div>

<form method="POST" action="{{ route('admin.contracts.update', $contract) }}" id="contractForm" onsubmit="showLoading()">
    @csrf @method('PUT')
    <div class="row">
        <div class="col-lg-8">
            {{-- 合約項目明細 --}}
            <div class="card mb-3">
                <div class="card-header"><strong>合約項目</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">合約標題 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title', $contract->title) }}" required placeholder="例如：官網設計合約">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                @foreach($contract->items as $i => $item)
                                <tr class="item-row">
                                    <td class="align-middle text-center item-number">{{ $i + 1 }}</td>
                                    <td><input type="text" class="form-control form-control-sm" name="items[{{ $i }}][description]" value="{{ old("items.{$i}.description", $item->description) }}" placeholder="項目名稱" required></td>
                                    <td><input type="number" class="form-control form-control-sm item-qty" name="items[{{ $i }}][quantity]" value="{{ old("items.{$i}.quantity", $item->quantity) }}" min="0.01" step="0.01" required></td>
                                    <td>
                                        <select class="form-select form-select-sm" name="items[{{ $i }}][unit]">
                                            @foreach(['項', '小時', '天', '月', '頁', '個'] as $unit)
                                                <option value="{{ $unit }}" {{ old("items.{$i}.unit", $item->unit) == $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" class="form-control form-control-sm item-price" name="items[{{ $i }}][unit_price]" value="{{ old("items.{$i}.unit_price", $item->unit_price) }}" placeholder="0" min="0" step="0.01" required></td>
                                    <td class="item-amount text-end align-middle">NT$ {{ number_format($item->amount) }}</td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-item" data-coreui-toggle="tooltip" title="移除此項目">✕</button></td>
                                </tr>
                                @endforeach
                                @if($contract->items->isEmpty())
                                <tr class="item-row">
                                    <td class="align-middle text-center item-number">1</td>
                                    <td><input type="text" class="form-control form-control-sm" name="items[0][description]" placeholder="項目名稱" required></td>
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
                                @endif
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
                                <tr><td>小計</td><td class="text-end" id="subtotal">NT$ {{ number_format($contract->subtotal) }}</td></tr>
                                <tr>
                                    <td>折扣</td>
                                    <td class="text-end">
                                        <input type="number" class="form-control form-control-sm text-end" name="discount"
                                               id="discount" value="{{ old('discount', $contract->discount) }}" min="0" step="0.01" style="width: 120px; display: inline-block;">
                                    </td>
                                </tr>
                                <tr>
                                    <td>稅率 (%)</td>
                                    <td class="text-end">
                                        <input type="number" class="form-control form-control-sm text-end" name="tax_rate"
                                               id="taxRate" value="{{ old('tax_rate', $contract->tax_rate) }}" min="0" max="100" step="0.01" style="width: 80px; display: inline-block;">
                                    </td>
                                </tr>
                                <tr><td>稅額</td><td class="text-end" id="taxAmount">NT$ {{ number_format($contract->tax_amount) }}</td></tr>
                                <tr class="fw-bold"><td>總計</td><td class="text-end" id="totalAmount">NT$ {{ number_format($contract->total) }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 合約正文 --}}
            <div class="card mb-3">
                <div class="card-header"><strong>合約正文</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="content" class="form-label">合約內容 <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content" name="content" rows="12" required>{{ old('content', $contract->content) }}</textarea>
                        @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">支援純文字，可貼上合約全文</small>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">備註</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="內部備註，客戶不可見">{{ old('notes', $contract->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- 基本設定 --}}
            <div class="card mb-3">
                <div class="card-header"><strong>合約設定</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="client_id" class="form-label">客戶 <span class="text-danger">*</span></label>
                        <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                            <option value="">選擇客戶</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $contract->client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                        @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="project_id" class="form-label">關聯專案</label>
                        <select class="form-select" id="project_id" name="project_id">
                            <option value="">不關聯</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id', $contract->project_id) == $project->id ? 'selected' : '' }}>{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">合約類型</label>
                        <select class="form-select" id="type" name="type" required>
                            @foreach(['service' => '服務合約', 'maintenance' => '維護合約', 'retainer' => '長期顧問', 'nda' => '保密協議', 'other' => '其他'] as $val => $label)
                                <option value="{{ $val }}" {{ old('type', $contract->type) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">狀態</label>
                        <select class="form-select" id="status" name="status">
                            @foreach(['draft' => '草稿', 'sent' => '已送出', 'signed' => '已簽署', 'active' => '執行中', 'completed' => '已完成', 'cancelled' => '已取消'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $contract->status) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">開始日期</label>
                            <input type="date" class="form-control" id="start_date" name="start_date"
                                   value="{{ old('start_date', $contract->start_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">結束日期</label>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                   value="{{ old('end_date', $contract->end_date?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="signed_at" class="form-label">簽署日期</label>
                        <input type="date" class="form-control" id="signed_at" name="signed_at"
                               value="{{ old('signed_at', $contract->signed_at?->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>

            {{-- 付款設定 --}}
            <div class="card mb-3">
                <div class="card-header"><strong>付款設定</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="payment_terms" class="form-label">付款條件</label>
                        <select class="form-select" id="payment_terms" name="payment_terms">
                            @foreach(['due_on_signing' => '簽約時付款', 'net15' => 'Net 15', 'net30' => 'Net 30', 'net60' => 'Net 60', 'milestone' => '依里程碑', 'custom' => '自訂'] as $val => $label)
                                <option value="{{ $val }}" {{ old('payment_terms', $contract->payment_terms) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">付款方式</label>
                        <input type="text" class="form-control" id="payment_method" name="payment_method"
                               value="{{ old('payment_method', $contract->payment_method) }}" placeholder="例如：銀行轉帳">
                    </div>
                    <div class="mb-3">
                        <label for="paid_amount" class="form-label">已付金額</label>
                        <div class="input-group">
                            <span class="input-group-text">NT$</span>
                            <input type="number" class="form-control" id="paid_amount" name="paid_amount"
                                   value="{{ old('paid_amount', $contract->paid_amount) }}" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label">付款到期日</label>
                        <input type="date" class="form-control" id="due_date" name="due_date"
                               value="{{ old('due_date', $contract->due_date?->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>

            {{-- 續約與保固 --}}
            <div class="card mb-3">
                <div class="card-header"><strong>續約與保固</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto_renew" name="auto_renew" value="1"
                                   {{ old('auto_renew', $contract->auto_renew) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_renew">自動續約</label>
                        </div>
                    </div>
                    <div class="mb-3" id="renewalNoticeDaysGroup" style="{{ old('auto_renew', $contract->auto_renew) ? '' : 'display:none;' }}">
                        <label for="renewal_notice_days" class="form-label">續約通知提前天數</label>
                        <input type="number" class="form-control" id="renewal_notice_days" name="renewal_notice_days"
                               value="{{ old('renewal_notice_days', $contract->renewal_notice_days) }}" min="1">
                    </div>
                    <div class="mb-3">
                        <label for="warranty_months" class="form-label">保固期（月）</label>
                        <input type="number" class="form-control" id="warranty_months" name="warranty_months"
                               value="{{ old('warranty_months', $contract->warranty_months) }}" min="0" placeholder="例如：12">
                    </div>
                    <div class="mb-3">
                        <label for="ip_ownership" class="form-label">智慧財產歸屬</label>
                        <select class="form-select" id="ip_ownership" name="ip_ownership">
                            <option value="client" {{ old('ip_ownership', $contract->ip_ownership) == 'client' ? 'selected' : '' }}>客戶擁有</option>
                            <option value="shared" {{ old('ip_ownership', $contract->ip_ownership) == 'shared' ? 'selected' : '' }}>共同擁有</option>
                            <option value="studio" {{ old('ip_ownership', $contract->ip_ownership) == 'studio' ? 'selected' : '' }}>工作室擁有</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- 簽署方 --}}
            <div class="card mb-3">
                <div class="card-header"><strong>簽署方資訊</strong></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="client_signer_name" class="form-label">客戶簽署人姓名</label>
                        <input type="text" class="form-control" id="client_signer_name" name="client_signer_name"
                               value="{{ old('client_signer_name', $contract->client_signer_name) }}">
                    </div>
                    <div class="mb-3">
                        <label for="client_signer_title" class="form-label">客戶簽署人職稱</label>
                        <input type="text" class="form-control" id="client_signer_title" name="client_signer_title"
                               value="{{ old('client_signer_title', $contract->client_signer_title) }}">
                    </div>
                    <div class="mb-3">
                        <label for="client_signer_email" class="form-label">客戶簽署人 Email</label>
                        <input type="email" class="form-control" id="client_signer_email" name="client_signer_email"
                               value="{{ old('client_signer_email', $contract->client_signer_email) }}">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label for="company_signer_name" class="form-label">公司簽署人姓名</label>
                        <input type="text" class="form-control" id="company_signer_name" name="company_signer_name"
                               value="{{ old('company_signer_name', $contract->company_signer_name) }}">
                    </div>
                    <div class="mb-3">
                        <label for="execution_method" class="form-label">簽署方式</label>
                        <select class="form-select" id="execution_method" name="execution_method">
                            <option value="wet_ink" {{ old('execution_method', $contract->execution_method) == 'wet_ink' ? 'selected' : '' }}>紙本簽署</option>
                            <option value="esignature" {{ old('execution_method', $contract->execution_method) == 'esignature' ? 'selected' : '' }}>電子簽章</option>
                            <option value="email_consent" {{ old('execution_method', $contract->execution_method) == 'email_consent' ? 'selected' : '' }}>Email 同意</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- 儲存按鈕 --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-save"></use></svg> 更新合約
                        </button>
                        <a href="{{ route('admin.contracts.index') }}" class="btn btn-light">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = {{ $contract->items->count() ?: 1 }};
    const itemsBody = document.getElementById('itemsBody');

    // 自動續約 toggle
    document.getElementById('auto_renew').addEventListener('change', function() {
        document.getElementById('renewalNoticeDaysGroup').style.display = this.checked ? '' : 'none';
    });

    document.getElementById('addItem').addEventListener('click', function() {
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td class="align-middle text-center item-number"></td>
            <td><input type="text" class="form-control form-control-sm" name="items[${itemIndex}][description]" placeholder="項目名稱" required></td>
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
        const taxable = subtotal - discount;
        const tax = Math.round(taxable * (taxRate / 100));
        const total = taxable + tax;
        document.getElementById('subtotal').textContent = 'NT$ ' + subtotal.toLocaleString();
        document.getElementById('taxAmount').textContent = 'NT$ ' + tax.toLocaleString();
        document.getElementById('totalAmount').textContent = 'NT$ ' + total.toLocaleString();
    }

    bindCalculation();
    updateRemoveButtons();
    document.getElementById('discount').addEventListener('input', calculateTotal);
    document.getElementById('taxRate').addEventListener('input', calculateTotal);
});
</script>
@endpush
@endsection
