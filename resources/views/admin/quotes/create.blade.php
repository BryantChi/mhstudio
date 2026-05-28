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

@include('admin.partials.service-plan-panel', ['titleSuffix' => '網站設計報價'])

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
@include('admin.partials.line-items-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== 帶入標準條款 =====
    document.getElementById('fillStandardNotes')?.addEventListener('click', function() {
        var notes = document.getElementById('notes');
        if (notes.value && !confirm('目前備註欄已有內容，確定要覆蓋嗎？')) return;

        notes.value = @json(setting('quote_standard_terms', ''));
    });
});
</script>
@endpush
@endsection
