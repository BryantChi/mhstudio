@extends('layouts.admin')

@section('title', '報價請求詳情')

@php
    $breadcrumbs = [
        ['title' => '報價請求管理', 'url' => route('admin.quote-requests.index')],
        ['title' => $quoteRequest->request_number, 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="mb-0">
            {{ $quoteRequest->request_number }}
            <span class="badge bg-{{ $quoteRequest->status_color }} ms-2">{{ $quoteRequest->status_label }}</span>
        </h2>
        <p class="text-muted">建立時間：{{ $quoteRequest->created_at->format('Y-m-d H:i:s') }}</p>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="{{ route('admin.quote-requests.index') }}" class="btn btn-light">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-arrow-left"></use>
            </svg>
            返回列表
        </a>
    </div>
</div>

<div class="row">
    {{-- 左欄 --}}
    <div class="col-lg-8">
        {{-- 報價明細 --}}
        <div class="card">
            <div class="card-header">
                <strong>
                    <svg class="icon me-1">
                        <use xlink:href="/assets/icons/free.svg#cil-description"></use>
                    </svg>
                    報價明細
                </strong>
            </div>
            <div class="card-body">
                <table class="table mb-4">
                    <tbody>
                        <tr>
                            <th width="150">服務類型</th>
                            <td>{{ $quoteRequest->project_type ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>時程</th>
                            <td>{{ $quoteRequest->timeline ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>預算範圍</th>
                            <td>{{ $quoteRequest->budget ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>

                @if($quoteRequest->selected_features && count($quoteRequest->selected_features) > 0)
                <h6 class="fw-semibold mb-3">選擇的功能</h6>
                <div class="table-responsive">
                    <table class="table table-bordered mb-4">
                        <thead class="table-light">
                            <tr>
                                <th>功能名稱</th>
                                <th class="text-end">最低價格</th>
                                <th class="text-end">最高價格</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quoteRequest->selected_features as $feature)
                            <tr>
                                <td>{{ $feature['name'] ?? '-' }}</td>
                                <td class="text-end">NT$ {{ number_format($feature['price_min'] ?? 0) }}</td>
                                <td class="text-end">NT$ {{ number_format($feature['price_max'] ?? 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                <div class="bg-light rounded p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold fs-5">估算金額</span>
                        <span class="fw-bold fs-5 text-primary">
                            NT$ {{ number_format($quoteRequest->estimated_min) }} ~ NT$ {{ number_format($quoteRequest->estimated_max) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 客戶留言 --}}
        <div class="card mt-3">
            <div class="card-header">
                <strong>
                    <svg class="icon me-1">
                        <use xlink:href="/assets/icons/free.svg#cil-comment-square"></use>
                    </svg>
                    客戶留言
                </strong>
            </div>
            <div class="card-body">
                @if($quoteRequest->message)
                    <div style="white-space: pre-line;">{{ $quoteRequest->message }}</div>
                @else
                    <span class="text-muted">無</span>
                @endif
            </div>
        </div>

        {{-- 狀態管理 --}}
        <div class="card mt-3">
            <div class="card-header">
                <strong>
                    <svg class="icon me-1">
                        <use xlink:href="/assets/icons/free.svg#cil-settings"></use>
                    </svg>
                    狀態管理
                </strong>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.quote-requests.update-status', $quoteRequest) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="status" class="form-label">狀態</label>
                        <select class="form-select @error('status') is-invalid @enderror"
                                id="status"
                                name="status">
                            <option value="pending" {{ old('status', $quoteRequest->status) == 'pending' ? 'selected' : '' }}>待處理</option>
                            <option value="reviewing" {{ old('status', $quoteRequest->status) == 'reviewing' ? 'selected' : '' }}>審核中</option>
                            <option value="quoted" {{ old('status', $quoteRequest->status) == 'quoted' ? 'selected' : '' }}>已報價</option>
                            <option value="accepted" {{ old('status', $quoteRequest->status) == 'accepted' ? 'selected' : '' }}>已接受</option>
                            <option value="rejected" {{ old('status', $quoteRequest->status) == 'rejected' ? 'selected' : '' }}>已拒絕</option>
                            <option value="expired" {{ old('status', $quoteRequest->status) == 'expired' ? 'selected' : '' }}>已過期</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="admin_notes" class="form-label">管理員備註</label>
                        <textarea class="form-control @error('admin_notes') is-invalid @enderror"
                                  id="admin_notes"
                                  name="admin_notes"
                                  rows="4">{{ old('admin_notes', $quoteRequest->admin_notes) }}</textarea>
                        @error('admin_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-save"></use>
                        </svg>
                        儲存
                    </button>
                </form>
            </div>
        </div>

        {{-- 轉換為正式報價單 --}}
        @if(in_array($quoteRequest->status, ['pending', 'reviewing']))
        <div class="card mt-3 border-success">
            <div class="card-body">
                <form method="POST"
                      action="{{ route('admin.quote-requests.convert', $quoteRequest) }}"
                      onsubmit="return confirm('確定要將此報價請求轉為正式報價單嗎？系統將依據選擇的功能自動建立報價單項目。');">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-arrow-right"></use>
                        </svg>
                        轉為正式報價單
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- 已關聯的報價單連結 --}}
        @if($quoteRequest->quote_id && $quoteRequest->quote)
        <div class="card mt-3 border-primary">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <svg class="icon me-1 text-primary">
                        <use xlink:href="/assets/icons/free.svg#cil-file"></use>
                    </svg>
                    <strong>關聯報價單：</strong>{{ $quoteRequest->quote->quote_number }}
                </div>
                <a href="{{ route('admin.quotes.show', $quoteRequest->quote) }}" class="btn btn-primary btn-sm">
                    查看報價單
                    <svg class="icon ms-1">
                        <use xlink:href="/assets/icons/free.svg#cil-arrow-right"></use>
                    </svg>
                </a>
            </div>
        </div>
        @endif
    </div>

    {{-- 右欄 --}}
    <div class="col-lg-4">
        {{-- 客戶資訊 --}}
        <div class="card">
            <div class="card-header">
                <strong>
                    <svg class="icon me-1">
                        <use xlink:href="/assets/icons/free.svg#cil-user"></use>
                    </svg>
                    客戶資訊
                </strong>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <th width="80">姓名</th>
                            <td>{{ $quoteRequest->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>
                                <a href="mailto:{{ $quoteRequest->email }}">{{ $quoteRequest->email }}</a>
                            </td>
                        </tr>
                        <tr>
                            <th>電話</th>
                            <td>{{ $quoteRequest->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>公司</th>
                            <td>{{ $quoteRequest->company ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>

                @if($quoteRequest->client_id && $quoteRequest->client)
                <a href="{{ route('admin.clients.show', $quoteRequest->client) }}" class="btn btn-outline-primary btn-sm w-100">
                    <svg class="icon me-1">
                        <use xlink:href="/assets/icons/free.svg#cil-user"></use>
                    </svg>
                    查看客戶詳情
                </a>
                @endif
            </div>
        </div>

        {{-- Token 連結 --}}
        <div class="card mt-3">
            <div class="card-header">
                <strong>
                    <svg class="icon me-1">
                        <use xlink:href="/assets/icons/free.svg#cil-link"></use>
                    </svg>
                    Token 連結
                </strong>
            </div>
            <div class="card-body">
                <label class="form-label text-muted small">報價狀態查詢 URL</label>
                <div class="input-group">
                    <input type="text"
                           class="form-control form-control-sm"
                           id="tokenUrl"
                           value="{{ url('/quote-status/' . $quoteRequest->token) }}"
                           readonly>
                    <button class="btn btn-outline-secondary btn-sm"
                            type="button"
                            onclick="copyTokenUrl()"
                            data-coreui-toggle="tooltip"
                            title="複製連結">
                        <svg class="icon">
                            <use xlink:href="/assets/icons/free.svg#cil-copy"></use>
                        </svg>
                    </button>
                </div>
                <small class="text-muted mt-1 d-block">客戶可透過此連結查看報價請求狀態</small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyTokenUrl() {
    const input = document.getElementById('tokenUrl');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(function() {
        const btn = input.nextElementSibling;
        const originalTitle = btn.getAttribute('data-coreui-original-title') || '複製連結';
        btn.setAttribute('data-coreui-original-title', '已複製！');
        setTimeout(function() {
            btn.setAttribute('data-coreui-original-title', originalTitle);
        }, 2000);
    });
}
</script>
@endpush
@endsection
