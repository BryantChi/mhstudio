@extends('layouts.admin')

@section('title', '電子報報告')

@php
    $breadcrumbs = [
        ['title' => '電子報管理', 'url' => route('admin.newsletters.index')],
        ['title' => '發送報告', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">發送報告</h2>
        <p class="text-muted">{{ $newsletter->subject }}</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.newsletters.preview', $newsletter) }}" class="btn btn-light" target="_blank">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-external-link"></use>
            </svg>
            預覽內容
        </a>
        <a href="{{ route('admin.newsletters.index') }}" class="btn btn-light">返回列表</a>
    </div>
</div>

{{-- 基本資訊 --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th width="120">主旨</th>
                        <td>{{ $newsletter->subject }}</td>
                    </tr>
                    <tr>
                        <th>狀態</th>
                        <td>
                            @switch($newsletter->status)
                                @case('draft')
                                    <span class="badge bg-secondary">草稿</span>
                                    @break
                                @case('sending')
                                    <span class="badge bg-info">發送中</span>
                                    @break
                                @case('sent')
                                    <span class="badge bg-success">已發送</span>
                                    @break
                                @case('failed')
                                    <span class="badge bg-danger">失敗</span>
                                    @break
                                @case('scheduled')
                                    <span class="badge bg-warning text-dark">排程中</span>
                                    @break
                            @endswitch
                        </td>
                    </tr>
                    <tr>
                        <th>建立者</th>
                        <td>{{ $newsletter->creator->name ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th width="120">建立時間</th>
                        <td>{{ $newsletter->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    <tr>
                        <th>發送時間</th>
                        <td>{{ $newsletter->sent_at ? $newsletter->sent_at->format('Y-m-d H:i') : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- 統計卡片 --}}
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="fs-4 fw-semibold">{{ number_format($stats['total']) }}</div>
                <div>總收件人數</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="fs-4 fw-semibold">
                    {{ number_format($stats['sent']) }}
                    @if($stats['total'] > 0)
                        <span class="fs-6">({{ round($stats['sent'] / $stats['total'] * 100, 1) }}%)</span>
                    @endif
                </div>
                <div>成功發送</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <div class="fs-4 fw-semibold">
                    {{ number_format($stats['failed']) }}
                    @if($stats['total'] > 0)
                        <span class="fs-6">({{ round($stats['failed'] / $stats['total'] * 100, 1) }}%)</span>
                    @endif
                </div>
                <div>發送失敗</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="fs-4 fw-semibold">
                    {{ number_format($stats['opened']) }}
                    @if($stats['sent'] > 0)
                        <span class="fs-6">({{ round($stats['opened'] / $stats['sent'] * 100, 1) }}%)</span>
                    @endif
                </div>
                <div>已開啟</div>
            </div>
        </div>
    </div>
</div>

{{-- 發送紀錄 --}}
<div class="card">
    <div class="card-header">
        <strong>發送紀錄</strong>
    </div>
    <div class="card-body p-0">
        @if($logs->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>收件人 Email</th>
                        <th>收件人姓名</th>
                        <th>狀態</th>
                        <th>發送時間</th>
                        <th>錯誤訊息</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->subscriber->email ?? '-' }}</td>
                        <td>{{ $log->subscriber->name ?? '-' }}</td>
                        <td>
                            @switch($log->status)
                                @case('pending')
                                    <span class="badge bg-secondary">待發送</span>
                                    @break
                                @case('sent')
                                    <span class="badge bg-success">已發送</span>
                                    @break
                                @case('failed')
                                    <span class="badge bg-danger">失敗</span>
                                    @break
                                @case('opened')
                                    <span class="badge bg-info">已開啟</span>
                                    @break
                            @endswitch
                        </td>
                        <td>{{ $log->sent_at ? $log->sent_at->format('Y-m-d H:i:s') : '-' }}</td>
                        <td>
                            @if($log->error_message)
                                <span class="text-danger small" data-coreui-toggle="tooltip" title="{{ $log->error_message }}">
                                    {{ \Illuminate\Support\Str::limit($log->error_message, 50) }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <div>尚無發送紀錄</div>
        </div>
        @endif
    </div>

    @if($logs->hasPages())
    <div class="card-footer">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
