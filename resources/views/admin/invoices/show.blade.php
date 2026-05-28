@extends('layouts.admin')

@section('title', '發票詳情')

@php
    $breadcrumbs = [
        ['title' => '發票管理', 'url' => route('admin.invoices.index')],
        ['title' => '發票詳情', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">{{ $invoice->title }}</h2>
        <p class="text-muted">{{ $invoice->invoice_number }}</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-primary" data-coreui-toggle="tooltip" title="編輯發票">編輯</a>
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-light" data-coreui-toggle="tooltip" title="返回發票列表">返回列表</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><strong>發票項目明細</strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr><th>#</th><th>項目說明</th><th class="text-center">數量</th><th class="text-center">單位</th><th class="text-end">單價</th><th class="text-end">小計</th></tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->description }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-center">{{ $item->unit }}</td>
                                <td class="text-end">NT$ {{ number_format($item->unit_price) }}</td>
                                <td class="text-end">NT$ {{ number_format($item->amount) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr><td colspan="5" class="text-end">小計</td><td class="text-end">NT$ {{ number_format($invoice->subtotal) }}</td></tr>
                            @if($invoice->discount > 0)
                            <tr><td colspan="5" class="text-end">折扣</td><td class="text-end text-danger">- NT$ {{ number_format($invoice->discount) }}</td></tr>
                            @endif
                            <tr><td colspan="5" class="text-end">稅額 ({{ $invoice->tax_rate }}%)</td><td class="text-end">NT$ {{ number_format($invoice->tax_amount) }}</td></tr>
                            <tr class="fw-bold"><td colspan="5" class="text-end">總計</td><td class="text-end">NT$ {{ number_format($invoice->total) }}</td></tr>
                            <tr class="text-success"><td colspan="5" class="text-end">已付金額</td><td class="text-end">NT$ {{ number_format($invoice->paid_amount) }}</td></tr>
                            <tr class="fw-bold text-danger"><td colspan="5" class="text-end">餘額</td><td class="text-end">NT$ {{ number_format($invoice->balance_due) }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- 記錄付款 --}}
        @if($invoice->balance_due > 0 && !in_array($invoice->status, ['cancelled', 'draft']))
        <div class="card mt-3">
            <div class="card-header"><strong>記錄付款</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.invoices.record-payment', $invoice) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">付款金額 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">NT$</span>
                                <input type="number" class="form-control" name="amount" id="paymentAmount"
                                       value="{{ $invoice->balance_due }}" min="0.01"
                                       max="{{ $invoice->balance_due }}" step="0.01" required>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="document.getElementById('paymentAmount').value = '{{ $invoice->balance_due }}';" data-coreui-toggle="tooltip" title="自動填入剩餘金額">
                                    全額付清
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">付款方式</label>
                            <select class="form-select" name="payment_method">
                                <option value="">選擇方式</option>
                                <option value="銀行轉帳">銀行轉帳</option>
                                <option value="信用卡">信用卡</option>
                                <option value="現金">現金</option>
                                <option value="支票">支票</option>
                                <option value="其他">其他</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">收款日期</label>
                            <input type="date" class="form-control" name="paid_on" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">備註</label>
                            <input type="text" class="form-control" name="note" placeholder="選填">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">收款憑證</label>
                            <input type="file" class="form-control" name="proof" accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-text">選填，可上傳轉帳截圖或收據（PDF／圖檔，10MB 內）</div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">
                                <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-check-circle"></use></svg>
                                記錄付款
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- 收款明細 --}}
        @if($invoice->payments->isNotEmpty())
        <div class="card mt-3">
            <div class="card-header"><strong>收款明細</strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead>
                            <tr><th>日期</th><th>金額</th><th>方式</th><th>備註</th><th class="text-end">操作</th></tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->payments as $payment)
                            <tr>
                                <td class="text-nowrap">{{ $payment->paid_on->format('Y-m-d') }}</td>
                                <td>NT$ {{ number_format($payment->amount) }}</td>
                                <td class="text-muted">{{ $payment->payment_method ?: '-' }}</td>
                                <td class="small text-muted">{{ $payment->note }}</td>
                                <td class="text-end">
                                    @if($payment->proof_path)
                                    <a href="{{ $payment->proof_url }}" target="_blank" class="btn btn-sm btn-link p-0 me-2" title="檢視收款憑證">憑證</a>
                                    @endif
                                    <form method="POST" action="{{ route('admin.invoices.destroy-payment', [$invoice, $payment]) }}" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" data-confirm-delete title="刪除此筆收款">✕</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if($invoice->notes)
        <div class="card mt-3">
            <div class="card-header"><strong>備註</strong></div>
            <div class="card-body" style="white-space: pre-line;">{{ $invoice->notes }}</div>
        </div>
        @endif

        {{-- 活動紀錄 --}}
        @if($activities->isNotEmpty())
        <div class="card mt-3">
            <div class="card-header"><strong>活動紀錄</strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr><th>時間</th><th>操作者</th><th>描述</th><th>變更內容</th></tr>
                        </thead>
                        <tbody>
                            @foreach($activities as $activity)
                            <tr>
                                <td class="text-nowrap">{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $activity->causer?->name ?? '系統' }}</td>
                                <td>{{ $activity->description }}</td>
                                <td>
                                    @if($activity->properties->has('old'))
                                        @foreach($activity->properties['attributes'] ?? [] as $key => $value)
                                            @if(isset($activity->properties['old'][$key]) && $activity->properties['old'][$key] != $value)
                                                <span class="badge bg-light text-dark me-1">{{ $key }}: {{ $activity->properties['old'][$key] }} → {{ $value }}</span>
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><strong>發票資訊</strong></div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr><th width="100">發票編號</th><td>{{ $invoice->invoice_number }}</td></tr>
                        <tr><th>客戶</th><td><a href="{{ route('admin.clients.show', $invoice->client) }}">{{ $invoice->client->name }}</a></td></tr>
                        <tr><th>狀態</th><td><span class="badge bg-{{ $invoice->status_color }}">{{ $invoice->status_label }}</span></td></tr>
                        <tr><th>開立日期</th><td>{{ $invoice->issued_date->format('Y-m-d') }}</td></tr>
                        <tr><th>到期日</th><td>{{ $invoice->due_date->format('Y-m-d') }}</td></tr>
                        @if($invoice->paid_at)
                        <tr><th>付款日期</th><td>{{ $invoice->paid_at->format('Y-m-d H:i') }}</td></tr>
                        @endif
                        @if($invoice->payment_method)
                        <tr><th>付款方式</th><td>{{ $invoice->payment_method }}</td></tr>
                        @endif
                        @if($invoice->quote)
                        <tr><th>來源報價</th><td><a href="{{ route('admin.quotes.show', $invoice->quote) }}">{{ $invoice->quote->quote_number }}</a></td></tr>
                        @endif
                        @if($invoice->project)
                        <tr><th>關聯專案</th><td><a href="{{ route('admin.projects.show', $invoice->project) }}">{{ $invoice->project->title }}</a></td></tr>
                        @endif
                        <tr><th>建立者</th><td>{{ $invoice->creator->name }}</td></tr>
                        <tr><th>建立時間</th><td>{{ $invoice->created_at->format('Y-m-d H:i') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.invoices.destroy', $invoice) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100" data-confirm-delete data-coreui-toggle="tooltip" title="刪除此發票">
                        <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg> 刪除發票
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
