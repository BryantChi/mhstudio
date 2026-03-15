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
        {{-- 狀態變更 --}}
        <div class="card mb-3">
            <div class="card-header"><strong>合約狀態</strong></div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-{{ $contract->status_color }} fs-6">{{ $contract->status_label }}</span>
                    <span class="text-muted mx-2">變更為：</span>
                    @foreach(['draft' => '草稿', 'sent' => '已送出', 'signed' => '已簽署', 'active' => '執行中', 'completed' => '已完成', 'cancelled' => '已取消'] as $val => $label)
                        @if($val !== $contract->status)
                        <form method="POST" action="{{ route('admin.contracts.update-status', $contract) }}" class="d-inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="{{ $val }}">
                            <button type="submit" class="btn btn-sm btn-outline-secondary"
                                    onclick="return confirm('確定要變更為「{{ $label }}」嗎？')">{{ $label }}</button>
                        </form>
                        @endif
                    @endforeach
                </div>
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
                    {!! nl2br(e($contract->content)) !!}
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

@push('styles')
<style>
    .content-preview { line-height: 1.8; font-size: 1.05rem; color: #333; }
</style>
@endpush
@endsection
