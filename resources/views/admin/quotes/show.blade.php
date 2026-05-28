@extends('layouts.admin')

@section('title', '報價單詳情')

@php
    $breadcrumbs = [
        ['title' => '報價單管理', 'url' => route('admin.quotes.index')],
        ['title' => '報價單詳情', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">{{ $quote->title }}</h2>
        <p class="text-muted">{{ $quote->quote_number }}</p>
    </div>
    <div class="col-md-6 text-md-end">
        @if($quote->status === 'accepted' && !$quote->invoice)
        <form method="POST" action="{{ route('admin.quotes.convert', $quote) }}" class="d-inline"
              onsubmit="return confirm('確定要將報價單「{{ $quote->quote_number }}」轉換為發票嗎？此操作將建立新發票並更新報價單狀態。');">
            @csrf
            <button type="submit" class="btn btn-success">
                <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-arrow-right"></use></svg> 轉為發票
            </button>
        </form>
        @endif
        @if($quote->status === 'accepted' && !$quote->contract)
        <button type="button" class="btn btn-outline-primary" data-coreui-toggle="modal" data-coreui-target="#convertContractModal">
            <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-description"></use></svg> 轉為合約
        </button>
        @endif
        <a href="{{ route('admin.quotes.pdf', $quote) }}" class="btn btn-outline-danger" data-coreui-toggle="tooltip" title="匯出 PDF">
            <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-file"></use></svg>
            PDF
        </a>
        <form method="POST" action="{{ route('admin.quotes.duplicate', $quote) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-secondary" data-coreui-toggle="tooltip" title="複製報價單">
                <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-copy"></use></svg>
                複製
            </button>
        </form>
        <a href="{{ route('admin.quotes.edit', $quote) }}" class="btn btn-primary">編輯</a>
        <a href="{{ route('admin.quotes.index') }}" class="btn btn-light">返回列表</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><strong>報價項目明細</strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr><th>#</th><th>項目說明</th><th class="text-center">數量</th><th class="text-center">單位</th><th class="text-end">單價</th><th class="text-end">小計</th></tr>
                        </thead>
                        <tbody>
                            @foreach($quote->items as $i => $item)
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
                            <tr><td colspan="5" class="text-end">小計</td><td class="text-end">NT$ {{ number_format($quote->subtotal) }}</td></tr>
                            @if($quote->discount > 0)
                            <tr><td colspan="5" class="text-end">折扣</td><td class="text-end text-danger">- NT$ {{ number_format($quote->discount) }}</td></tr>
                            @endif
                            <tr><td colspan="5" class="text-end">稅額 ({{ $quote->tax_rate }}%)</td><td class="text-end">NT$ {{ number_format($quote->tax_amount) }}</td></tr>
                            <tr class="fw-bold"><td colspan="5" class="text-end">總計</td><td class="text-end">NT$ {{ number_format($quote->total) }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        @if($quote->description)
        <div class="card mt-3">
            <div class="card-header"><strong>說明</strong></div>
            <div class="card-body" style="white-space: pre-line;">{{ $quote->description }}</div>
        </div>
        @endif

        @if($quote->notes)
        <div class="card mt-3">
            <div class="card-header"><strong>備註 / 條款</strong></div>
            <div class="card-body" style="white-space: pre-line;">{{ $quote->notes }}</div>
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
        {{-- 狀態變更 --}}
        @php $quoteStatusLabels = ['draft' => '草稿', 'sent' => '已送出', 'accepted' => '已接受', 'rejected' => '已拒絕', 'expired' => '已過期']; @endphp
        @if(count($quote->allowedNextStatuses()))
        <div class="card mb-3">
            <div class="card-header"><strong>狀態變更</strong></div>
            <div class="card-body d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-{{ $quote->status_color }} fs-6">{{ $quote->status_label }}</span>
                <span class="text-muted">→</span>
                @foreach($quote->allowedNextStatuses() as $next)
                <form method="POST" action="{{ route('admin.quotes.update-status', $quote) }}" class="d-inline">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="{{ $next }}">
                    <button type="submit" class="btn btn-sm btn-outline-secondary"
                            onclick="return confirm('確定變更為「{{ $quoteStatusLabels[$next] }}」嗎？')">{{ $quoteStatusLabels[$next] }}</button>
                </form>
                @endforeach
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header"><strong>報價資訊</strong></div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr><th width="100">報價編號</th><td>{{ $quote->quote_number }}</td></tr>
                        <tr><th>客戶</th><td><a href="{{ route('admin.clients.show', $quote->client) }}">{{ $quote->client->name }}</a></td></tr>
                        <tr><th>狀態</th><td><span class="badge bg-{{ $quote->status_color }}">{{ $quote->status_label }}</span></td></tr>
                        <tr><th>總金額</th><td class="fw-bold fs-5 text-primary">NT$ {{ number_format($quote->total) }}</td></tr>
                        <tr><th>有效期限</th><td>{{ $quote->valid_until?->format('Y-m-d') ?? '-' }}</td></tr>
                        @if($quote->project)
                        <tr><th>關聯專案</th><td><a href="{{ route('admin.projects.show', $quote->project) }}">{{ $quote->project->title }}</a></td></tr>
                        @endif
                        @if($quote->invoice)
                        <tr><th>關聯發票</th><td><a href="{{ route('admin.invoices.show', $quote->invoice) }}">{{ $quote->invoice->invoice_number }}</a></td></tr>
                        @endif
                        @if($quote->contract)
                        <tr><th>關聯合約</th><td><a href="{{ route('admin.contracts.show', $quote->contract) }}">{{ $quote->contract->contract_number }}</a></td></tr>
                        @endif
                        <tr><th>建立者</th><td>{{ $quote->creator->name }}</td></tr>
                        <tr><th>建立時間</th><td>{{ $quote->created_at->format('Y-m-d H:i') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.quotes.destroy', $quote) }}"
                      onsubmit="return confirm('確定要刪除此報價單嗎？');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg> 刪除報價單
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- 轉為合約 Modal --}}
@if($quote->status === 'accepted' && !$quote->contract)
<div class="modal fade" id="convertContractModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.quotes.convert-to-contract', $quote) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">轉為合約</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">將報價單「{{ $quote->quote_number }}」的項目與金額帶入新合約草稿，並套用所選合約範本的正文（合約類型依範本自動設定）。</p>
                    <div class="mb-2">
                        <label for="convertTemplateId" class="form-label">合約範本</label>
                        <select name="template_id" id="convertTemplateId" class="form-select">
                            @foreach($contractTemplates as $tpl)
                                <option value="{{ $tpl->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $tpl->name }}（{{ $tpl->type_label }}）</option>
                            @endforeach
                            <option value="">（不套用範本，使用預設說明）</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">建立合約</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
