@extends('layouts.admin')

@section('title', '客戶詳情')

@php
    $breadcrumbs = [
        ['title' => '客戶管理', 'url' => route('admin.clients.index')],
        ['title' => '客戶詳情', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">{{ $client->name }}</h2>
        <p class="text-muted">
            <span class="badge bg-{{ $client->status_color }}">{{ $client->status_label }}</span>
            <span class="badge bg-{{ $client->tier_color }}">{{ $client->tier_label }}</span>
        </p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.quotes.create', ['client_id' => $client->id]) }}" class="btn btn-info" data-coreui-toggle="tooltip" title="新增報價單">
            <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-calculator"></use></svg>
            報價
        </a>
        <a href="{{ route('admin.contracts.create', ['client_id' => $client->id]) }}" class="btn btn-info" data-coreui-toggle="tooltip" title="新增合約">
            <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-file"></use></svg>
            合約
        </a>
        <a href="{{ route('admin.invoices.create', ['client_id' => $client->id]) }}" class="btn btn-info" data-coreui-toggle="tooltip" title="新增發票">
            <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-description"></use></svg>
            發票
        </a>
        <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-primary" data-coreui-toggle="tooltip" title="編輯客戶">編輯</a>
        <a href="{{ route('admin.clients.index') }}" class="btn btn-light" data-coreui-toggle="tooltip" title="返回客戶列表">返回列表</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        {{-- 互動時間軸 --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>互動紀錄</strong>
                <span class="badge bg-secondary">{{ $client->interactions->count() }} 筆</span>
            </div>
            <div class="card-body">
                {{-- 新增互動表單 --}}
                <div class="border rounded p-3 bg-light mb-4">
                    <h6 class="mb-3">新增互動紀錄</h6>
                    <form method="POST" action="{{ route('admin.clients.interactions.store', $client) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">類型 <span class="text-danger">*</span></label>
                                <select class="form-select" name="type" required>
                                    <option value="note">備註</option>
                                    <option value="call">電話</option>
                                    <option value="email">郵件</option>
                                    <option value="meeting">會議</option>
                                    <option value="other">其他</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">主題 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="subject" required placeholder="互動主題">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">日期 <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="interaction_date"
                                       value="{{ now()->format('Y-m-d\TH:i') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">內容</label>
                                <textarea class="form-control" name="content" rows="2" placeholder="詳細內容（選填）"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg>
                                    新增紀錄
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- 互動列表 --}}
                @if($client->interactions->isNotEmpty())
                <div class="list-group">
                    @foreach($client->interactions as $interaction)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <svg class="icon me-2 text-{{ $interaction->type_color }}">
                                    <use xlink:href="/assets/icons/free.svg#{{ $interaction->type_icon }}"></use>
                                </svg>
                                <span class="badge bg-{{ $interaction->type_color }} me-2">{{ $interaction->type_label }}</span>
                                <strong>{{ $interaction->subject }}</strong>
                            </div>
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-2">
                                    {{ $interaction->interaction_date->format('Y-m-d H:i') }}
                                    &middot; {{ $interaction->user?->name }}
                                </small>
                                <form method="POST" action="{{ route('admin.clients.interactions.destroy', $interaction) }}"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm-delete data-coreui-toggle="tooltip" title="刪除紀錄">
                                        <svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @if($interaction->content)
                        <div class="ps-4" style="white-space: pre-line;">{{ $interaction->content }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted text-center">尚無互動紀錄</p>
                @endif
            </div>
        </div>

        {{-- 關聯合約 --}}
        <div class="card mt-3">
            <div class="card-header"><strong>合約</strong></div>
            @if($client->contracts->isNotEmpty())
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>編號</th>
                                <th>標題</th>
                                <th>類型</th>
                                <th>狀態</th>
                                <th>金額</th>
                                <th>到期日</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($client->contracts as $contract)
                            <tr class="{{ $contract->is_expiring_soon ? 'table-warning' : '' }}">
                                <td><a href="{{ route('admin.contracts.show', $contract) }}">{{ $contract->contract_number }}</a></td>
                                <td>{{ $contract->title }}</td>
                                <td>{{ $contract->type_label }}</td>
                                <td><span class="badge bg-{{ $contract->status_color }}">{{ $contract->status_label }}</span></td>
                                <td>{{ $contract->amount ? 'NT$ ' . number_format($contract->amount) : '-' }}</td>
                                <td>{{ $contract->end_date?->format('Y-m-d') ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="card-body">
                <div class="text-center py-4">
                    <p class="text-muted mb-2">尚無合約</p>
                    <a href="{{ route('admin.contracts.create', ['client_id' => $client->id]) }}" class="btn btn-sm btn-outline-primary">建立第一份合約</a>
                </div>
            </div>
            @endif
        </div>

        {{-- 關聯報價單 --}}
        <div class="card mt-3">
            <div class="card-header"><strong>報價單</strong></div>
            @if($client->quotes->isNotEmpty())
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>編號</th>
                                <th>標題</th>
                                <th>狀態</th>
                                <th>金額</th>
                                <th>有效期限</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($client->quotes as $quote)
                            <tr>
                                <td><a href="{{ route('admin.quotes.show', $quote) }}">{{ $quote->quote_number }}</a></td>
                                <td>{{ $quote->title }}</td>
                                <td><span class="badge bg-{{ $quote->status_color }}">{{ $quote->status_label }}</span></td>
                                <td>NT$ {{ number_format($quote->total) }}</td>
                                <td>{{ $quote->valid_until?->format('Y-m-d') ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="card-body">
                <div class="text-center py-4">
                    <p class="text-muted mb-2">尚無報價單</p>
                    <a href="{{ route('admin.quotes.create', ['client_id' => $client->id]) }}" class="btn btn-sm btn-outline-primary">建立第一份報價單</a>
                </div>
            </div>
            @endif
        </div>

        {{-- 報價請求 --}}
        <div class="card mt-3">
            <div class="card-header"><strong>報價請求</strong></div>
            @if($client->quoteRequests && $client->quoteRequests->isNotEmpty())
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>編號</th>
                                <th>服務類型</th>
                                <th>狀態</th>
                                <th>估算金額</th>
                                <th>日期</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($client->quoteRequests as $qr)
                            <tr>
                                <td><a href="{{ route('admin.quote-requests.show', $qr) }}">{{ $qr->request_number }}</a></td>
                                <td>{{ $qr->project_type }}</td>
                                <td><span class="badge bg-{{ $qr->status_color }}">{{ $qr->status_label }}</span></td>
                                <td>NT$ {{ number_format($qr->estimated_min) }} ~ {{ number_format($qr->estimated_max) }}</td>
                                <td>{{ $qr->created_at->format('Y-m-d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="card-body">
                <div class="text-center py-4">
                    <p class="text-muted mb-0">尚無報價請求</p>
                </div>
            </div>
            @endif
        </div>

        {{-- 關聯發票 --}}
        <div class="card mt-3">
            <div class="card-header"><strong>發票</strong></div>
            @if($client->invoices->isNotEmpty())
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>編號</th>
                                <th>標題</th>
                                <th>狀態</th>
                                <th>金額</th>
                                <th>到期日</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($client->invoices as $invoice)
                            <tr class="{{ $invoice->is_overdue ? 'table-danger' : '' }}">
                                <td><a href="{{ route('admin.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                                <td>{{ $invoice->title }}</td>
                                <td><span class="badge bg-{{ $invoice->status_color }}">{{ $invoice->status_label }}</span></td>
                                <td>NT$ {{ number_format($invoice->total) }}</td>
                                <td>{{ $invoice->due_date->format('Y-m-d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="card-body">
                <div class="text-center py-4">
                    <p class="text-muted mb-2">尚無發票</p>
                    <a href="{{ route('admin.invoices.create', ['client_id' => $client->id]) }}" class="btn btn-sm btn-outline-primary">建立第一份發票</a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><strong>基本資訊</strong></div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr><th width="100">公司</th><td>{{ $client->company ?? '-' }}</td></tr>
                        <tr><th>聯繫人</th><td>{{ $client->contact_person ?? '-' }}</td></tr>
                        <tr><th>Email</th><td>{{ $client->email ? '<a href="mailto:' . $client->email . '">' . $client->email . '</a>' : '-' }}</td></tr>
                        <tr><th>電話</th><td>{{ $client->phone ?? '-' }}</td></tr>
                        <tr><th>產業</th><td>{{ $client->industry ?? '-' }}</td></tr>
                        <tr><th>來源</th><td>{{ $client->source_label }}</td></tr>
                        <tr>
                            <th>網站</th>
                            <td>
                                @if($client->website)
                                    <a href="{{ $client->website }}" target="_blank" class="text-break">{{ $client->website }}</a>
                                @else - @endif
                            </td>
                        </tr>
                        <tr><th>地址</th><td>{{ $client->address ?? '-' }}</td></tr>
                        <tr>
                            <th>累計營收</th>
                            <td><strong class="text-success">NT$ {{ number_format($client->total_revenue) }}</strong></td>
                        </tr>
                        <tr><th>建立時間</th><td>{{ $client->created_at->format('Y-m-d H:i') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if($client->tags && count($client->tags) > 0)
        <div class="card mt-3">
            <div class="card-header"><strong>標籤</strong></div>
            <div class="card-body">
                @foreach($client->tags as $tag)
                    <span class="badge bg-info me-1 mb-1">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
        @endif

        @if($client->notes)
        <div class="card mt-3">
            <div class="card-header"><strong>備註</strong></div>
            <div class="card-body" style="white-space: pre-line;">{{ $client->notes }}</div>
        </div>
        @endif

        <div class="card mt-3">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.clients.destroy', $client) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100" data-confirm-delete>
                        <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-trash"></use></svg>
                        刪除客戶
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
