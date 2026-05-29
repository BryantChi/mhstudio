@extends('layouts.admin')

@section('title', '合約詳情')

@php
    $breadcrumbs = [
        ['title' => '合約管理', 'url' => route('admin.contracts.index')],
        ['title' => '合約詳情', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">{{ $contract->title }}</h2>
        <p class="text-muted">{{ $contract->contract_number }}</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.contracts.pdf', $contract) }}" class="btn btn-outline-primary" data-coreui-toggle="tooltip" title="下載 PDF">
            <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-cloud-download"></use></svg> PDF
        </a>
        <form method="POST" action="{{ route('admin.contracts.duplicate', $contract) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-secondary" data-coreui-toggle="tooltip" title="複製合約">
                <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-copy"></use></svg> 複製
            </button>
        </form>
        <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-primary" data-coreui-toggle="tooltip" title="編輯合約">編輯</a>
        <a href="{{ route('admin.contracts.index') }}" class="btn btn-light" data-coreui-toggle="tooltip" title="返回合約列表">返回列表</a>
    </div>
</div>

{{-- 狀態流程指示器 --}}
<div class="card mb-3">
    <div class="card-body">
        @php
            $statuses = ['draft' => '草稿', 'sent' => '已發送', 'signed' => '已簽署', 'active' => '執行中', 'completed' => '已完成'];
            $statusKeys = array_keys($statuses);
            $currentIndex = array_search($contract->status, $statusKeys);
            if ($currentIndex === false) $currentIndex = -1;
        @endphp
        <div class="d-flex justify-content-between position-relative" style="z-index: 1;">
            <div class="position-absolute top-50 start-0 end-0" style="height: 2px; background: #e9ecef; z-index: 0; transform: translateY(-50%);"></div>
            @foreach($statuses as $key => $label)
                @php
                    $stepIndex = array_search($key, $statusKeys);
                    $isActive = $contract->status === $key;
                    $isPast = $stepIndex < $currentIndex;
                    $bgClass = $isActive ? 'bg-primary text-white' : ($isPast ? 'bg-success text-white' : 'bg-light text-muted');
                @endphp
                <div class="text-center position-relative" style="z-index: 1;">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $bgClass }}" style="width: 36px; height: 36px; font-size: 0.8rem;">
                        @if($isPast)
                            <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-check"></use></svg>
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </div>
                    <div class="small mt-1 {{ $isActive ? 'fw-bold text-primary' : 'text-muted' }}">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        {{-- 合約狀態與動作 --}}
        @php $statusLabels = ['draft' => '草稿', 'sent' => '已送出', 'signed' => '已簽署', 'active' => '執行中', 'completed' => '已完成', 'cancelled' => '已取消']; @endphp
        <div class="card mb-3">
            <div class="card-header"><strong>合約狀態與動作</strong></div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-{{ $contract->status_color }} fs-6">{{ $contract->status_label }}</span>
                    @if(count($contract->allowedNextStatuses()))
                    <span class="text-muted mx-2">變更為：</span>
                    @foreach($contract->allowedNextStatuses() as $val)
                        <form method="POST" action="{{ route('admin.contracts.update-status', $contract) }}" class="d-inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="{{ $val }}">
                            <button type="submit" class="btn btn-sm btn-outline-secondary"
                                    onclick="return confirm('確定要變更為「{{ $statusLabels[$val] }}」嗎？')">{{ $statusLabels[$val] }}</button>
                        </form>
                    @endforeach
                    @endif
                </div>

                {{-- 送出給客戶 --}}
                @if(in_array($contract->status, ['draft', 'sent']))
                <hr>
                <div class="d-flex gap-2 flex-wrap">
                    <form method="POST" action="{{ route('admin.contracts.send', $contract) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-check"></use></svg> 標記為已送出
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.contracts.email', $contract) }}" class="d-inline"
                          onsubmit="return confirm('確定要將合約 PDF Email 給客戶嗎？')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-info">
                            <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-envelope-closed"></use></svg> Email 給客戶
                        </button>
                    </form>
                </div>
                @endif

                {{-- 上傳客戶回簽合約 → 已簽署 --}}
                @if(in_array($contract->status, ['draft', 'sent', 'signed']))
                <hr>
                <form method="POST" action="{{ route('admin.contracts.sign', $contract) }}" enctype="multipart/form-data" class="row g-2 align-items-center">
                    @csrf
                    <div class="col-12"><label class="form-label small text-muted mb-1">標記為已簽署（紙本可不附檔；如有客戶回簽 PDF／掃描圖可一併上傳）</label></div>
                    <div class="col-auto">
                        <input type="file" name="signed_document" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-success">標記為已簽署</button>
                    </div>
                </form>
                @endif

                {{-- 已簽署檔案 --}}
                @if($contract->signed_document_path)
                <hr>
                <div class="small">
                    <svg class="icon text-success me-1"><use xlink:href="/assets/icons/free.svg#cil-check-circle"></use></svg>
                    <span class="text-muted">客戶回簽合約：</span>
                    <a href="/storage/{{ $contract->signed_document_path }}" target="_blank">檢視 / 下載</a>
                    @if($contract->signed_document_uploaded_at)
                    <span class="text-muted ms-2">（{{ $contract->signed_document_uploaded_at->format('Y-m-d H:i') }} 上傳）</span>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- 合約項目明細 --}}
        @if($contract->items->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header"><strong>合約項目明細</strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr><th>#</th><th>項目說明</th><th class="text-center">數量</th><th class="text-center">單位</th><th class="text-end">單價</th><th class="text-end">小計</th></tr>
                        </thead>
                        <tbody>
                            @foreach($contract->items as $i => $item)
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
                            <tr><td colspan="5" class="text-end">小計</td><td class="text-end">NT$ {{ number_format($contract->subtotal) }}</td></tr>
                            @if($contract->discount > 0)
                            <tr><td colspan="5" class="text-end">折扣</td><td class="text-end text-danger">- NT$ {{ number_format($contract->discount) }}</td></tr>
                            @endif
                            <tr><td colspan="5" class="text-end">稅額 ({{ $contract->tax_rate }}%)</td><td class="text-end">NT$ {{ number_format($contract->tax_amount) }}</td></tr>
                            <tr class="fw-bold"><td colspan="5" class="text-end">總計</td><td class="text-end">NT$ {{ number_format($contract->total) }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- 合約內容 --}}
        <div class="card mb-3">
            <div class="card-header"><strong>合約正文</strong></div>
            <div class="card-body">
                <div class="content-preview">
                    {{ $contract->renderedContent() }}
                </div>
            </div>
        </div>

        @if($contract->notes)
        <div class="card mb-3">
            <div class="card-header"><strong>備註</strong></div>
            <div class="card-body" style="white-space: pre-line;">{{ $contract->notes }}</div>
        </div>
        @endif

        {{-- 活動紀錄 --}}
        @if($activities->isNotEmpty())
        <div class="card mb-3">
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
        {{-- 合約基本資訊 --}}
        <div class="card mb-3">
            <div class="card-header"><strong>合約資訊</strong></div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr><th width="100">合約編號</th><td>{{ $contract->contract_number }}</td></tr>
                        <tr><th>客戶</th><td><a href="{{ route('admin.clients.show', $contract->client) }}">{{ $contract->client->name }}</a></td></tr>
                        <tr><th>類型</th><td>{{ $contract->type_label }}</td></tr>
                        <tr><th>狀態</th><td><span class="badge bg-{{ $contract->status_color }}">{{ $contract->status_label }}</span></td></tr>
                        <tr><th>總金額</th><td>{{ $contract->total > 0 ? $contract->currency . ' ' . number_format($contract->total) : '-' }}</td></tr>
                        <tr><th>開始日期</th><td>{{ $contract->start_date?->format('Y-m-d') ?? '-' }}</td></tr>
                        <tr><th>結束日期</th><td>{{ $contract->end_date?->format('Y-m-d') ?? '-' }}</td></tr>
                        <tr><th>簽署日期</th><td>{{ $contract->signed_at?->format('Y-m-d') ?? '-' }}</td></tr>
                        @if($contract->project)
                        <tr><th>關聯專案</th><td><a href="{{ route('admin.projects.show', $contract->project) }}">{{ $contract->project->title }}</a></td></tr>
                        @endif
                        @if($contract->quote)
                        <tr><th>來源報價</th><td><a href="{{ route('admin.quotes.show', $contract->quote) }}">{{ $contract->quote->quote_number }}</a></td></tr>
                        @endif
                        <tr><th>建立者</th><td>{{ $contract->creator->name }}</td></tr>
                        <tr><th>建立時間</th><td>{{ $contract->created_at->format('Y-m-d H:i') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 付款資訊 --}}
        <div class="card mb-3">
            <div class="card-header"><strong>付款資訊</strong></div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th width="100">付款條件</th><td>{{ $contract->payment_terms_label }}</td></tr>
                    <tr><th>付款方式</th><td>{{ $contract->payment_method ?? '-' }}</td></tr>
                    <tr><th>到期日</th><td>{{ $contract->due_date?->format('Y-m-d') ?? '-' }}</td></tr>
                    <tr>
                        <th>付款狀態</th>
                        <td>
                            <span class="badge bg-{{ $contract->payment_status_color }}">{{ $contract->payment_status_label }}</span>
                            @if($contract->is_overdue)
                                <span class="badge bg-danger">逾期</span>
                            @endif
                        </td>
                    </tr>
                </table>
                @if($contract->total > 0)
                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>已付 NT$ {{ number_format($contract->paid_amount) }}</span>
                        <span>總計 NT$ {{ number_format($contract->total) }}</span>
                    </div>
                    @php
                        $paymentPercent = $contract->total > 0 ? min(100, round(($contract->paid_amount / $contract->total) * 100)) : 0;
                    @endphp
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-{{ $contract->payment_status_color }}" style="width: {{ $paymentPercent }}%"></div>
                    </div>
                    <div class="text-end small text-muted mt-1">餘額 NT$ {{ number_format($contract->balance_due) }}</div>
                </div>
                @endif
                @if($contract->paid_at)
                <div class="small text-success">付清時間：{{ $contract->paid_at->format('Y-m-d H:i') }}</div>
                @endif
            </div>
        </div>

        {{-- 續約與保固 --}}
        <div class="card mb-3">
            <div class="card-header"><strong>續約與保固</strong></div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th width="100">自動續約</th><td>{!! $contract->auto_renew ? '<span class="badge bg-success">是</span>' : '<span class="badge bg-secondary">否</span>' !!}</td></tr>
                    @if($contract->auto_renew)
                    <tr><th>提前通知</th><td>{{ $contract->renewal_notice_days }} 天</td></tr>
                    @endif
                    <tr><th>保固期</th><td>{{ $contract->warranty_months ? $contract->warranty_months . ' 個月' : '-' }}</td></tr>
                    <tr><th>IP 歸屬</th><td>{{ $contract->ip_ownership_label }}</td></tr>
                </table>
            </div>
        </div>

        {{-- 簽署方資訊 --}}
        @if($contract->client_signer_name || $contract->company_signer_name)
        <div class="card mb-3">
            <div class="card-header"><strong>簽署方</strong></div>
            <div class="card-body">
                <table class="table table-sm">
                    @if($contract->client_signer_name)
                    <tr><th colspan="2" class="text-muted">甲方（客戶）</th></tr>
                    <tr><th width="80">姓名</th><td>{{ $contract->client_signer_name }}</td></tr>
                    @if($contract->client_signer_title)
                    <tr><th>職稱</th><td>{{ $contract->client_signer_title }}</td></tr>
                    @endif
                    @if($contract->client_signer_email)
                    <tr><th>Email</th><td>{{ $contract->client_signer_email }}</td></tr>
                    @endif
                    @endif
                    @if($contract->company_signer_name)
                    <tr><th colspan="2" class="text-muted">乙方（公司）</th></tr>
                    <tr><th>姓名</th><td>{{ $contract->company_signer_name }}</td></tr>
                    @endif
                    <tr><th>簽署方式</th><td>{{ $contract->execution_method_label }}</td></tr>
                    @if($contract->sent_at)
                    <tr><th>送出時間</th><td>{{ $contract->sent_at->format('Y-m-d H:i') }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
        @endif

        {{-- 刪除 --}}
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.contracts.destroy', $contract) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100" data-confirm-delete data-coreui-toggle="tooltip" title="刪除此合約">
                        <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg>
                        刪除合約
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- 相關發票 --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>相關發票</strong>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary" data-coreui-toggle="modal" data-coreui-target="#createInvoiceModal">
                <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg> 開立發票
            </button>
            <button type="button" class="btn btn-sm btn-success" data-coreui-toggle="modal" data-coreui-target="#createInvoicePayModal">開發票並收款</button>
        </div>
    </div>
    <div class="card-body">
        <div class="row text-center mb-3">
            <div class="col"><div class="small text-medium-emphasis">合約總額</div><div class="fw-semibold">NT$ {{ number_format($contract->total) }}</div></div>
            <div class="col"><div class="small text-medium-emphasis">已開發票</div><div class="fw-semibold">NT$ {{ number_format($contract->invoiced_amount) }}</div></div>
            <div class="col"><div class="small text-medium-emphasis">可開餘額</div><div class="fw-semibold {{ $contract->uninvoiced_amount < 0 ? 'text-danger' : '' }}">NT$ {{ number_format($contract->uninvoiced_amount) }}</div></div>
        </div>
        @if($contract->invoices->isEmpty())
            <p class="text-medium-emphasis mb-0 small">尚無發票</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>發票號</th><th class="text-end">金額</th><th>狀態</th><th></th></tr></thead>
                    <tbody>
                        @foreach($contract->invoices as $inv)
                        <tr>
                            <td>{{ $inv->invoice_number }}</td>
                            <td class="text-end">NT$ {{ number_format($inv->total) }}</td>
                            <td><span class="badge bg-{{ $inv->status_color }}">{{ $inv->status_label }}</span></td>
                            <td class="text-end"><a href="{{ route('admin.invoices.show', $inv) }}" class="btn btn-sm btn-light">檢視</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- 開立發票 Modal --}}
<div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.contracts.create-invoice', $contract) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">從合約開立發票</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">金額模式</label>
                        <select name="mode" class="form-select" id="createInvoiceMode" onchange="
                            document.getElementById('ciAmountWrap').classList.toggle('d-none', this.value !== 'custom');
                            document.getElementById('ciPercentWrap').classList.toggle('d-none', this.value !== 'percent');">
                            <option value="custom">自訂金額</option>
                            <option value="percent">按比例（合約總額的百分比）</option>
                            <option value="remaining">剩餘未開全額（NT$ {{ number_format($contract->uninvoiced_amount) }}）</option>
                            <option value="copy_items">複製合約所有項目</option>
                        </select>
                    </div>
                    <div class="mb-3" id="ciAmountWrap">
                        <label class="form-label">金額</label>
                        <div class="input-group">
                            <span class="input-group-text">NT$</span>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01">
                        </div>
                    </div>
                    <div class="mb-3 d-none" id="ciPercentWrap">
                        <label class="form-label">百分比</label>
                        <div class="input-group">
                            <input type="number" name="percent" class="form-control" step="0.01" min="0.01" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">發票項目描述（單行模式適用，選填）</label>
                        <input type="text" name="description" class="form-control" placeholder="留空則用合約標題">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">建立發票</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 開發票並收款 Modal --}}
<div class="modal fade" id="createInvoicePayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.contracts.create-invoice-and-pay', $contract) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">開發票並收款</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">收款金額 <span class="text-danger">*</span></label>
                        <div class="input-group"><span class="input-group-text">NT$</span>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">發票項目方式</label>
                        <select name="item_mode" class="form-select"
                                onchange="document.getElementById('ciapDescWrap').classList.toggle('d-none', this.value !== 'custom')">
                            <option value="summary">摘要(合約款項)</option>
                            <option value="custom">自訂描述</option>
                            <option value="copy">複製合約項目(等比縮放)</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="ciapDescWrap">
                        <label class="form-label">發票描述</label>
                        <input type="text" name="description" class="form-control" placeholder="留空則用摘要預設">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">付款方式</label>
                        <input type="text" name="payment_method" class="form-control" placeholder="例如:銀行轉帳">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">收款日期</label>
                        <input type="date" name="paid_on" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">備註</label>
                        <input type="text" name="note" class="form-control" placeholder="選填">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">收款憑證</label>
                        <input type="file" name="proof" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">選填,上傳一次即可(存於發票收款)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-success">開發票並收款</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .content-preview { line-height: 1.8; font-size: 1.05rem; color: #333; white-space: pre-line; }
</style>
@endpush
@endsection
