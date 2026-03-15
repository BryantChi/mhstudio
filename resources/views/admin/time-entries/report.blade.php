@extends('layouts.admin')

@section('title', '工時報表')

@php
    $breadcrumbs = [
        ['title' => '工時追蹤', 'url' => route('admin.time-entries.index')],
        ['title' => '工時報表', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">工時報表</h2>
        <p class="text-muted">按專案或人員彙整工時統計</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('admin.time-entries.index') }}" class="btn btn-light">返回工時列表</a>
    </div>
</div>

{{-- 篩選 --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.time-entries.report') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">開始日期</label>
                <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">結束日期</label>
                <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">分組方式</label>
                <select class="form-select" name="group_by">
                    <option value="project" {{ $groupBy == 'project' ? 'selected' : '' }}>按專案</option>
                    <option value="user" {{ $groupBy == 'user' ? 'selected' : '' }}>按人員</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">人員</label>
                <select class="form-select" name="user_id">
                    <option value="">全部</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto d-flex align-items-end">
                <button type="submit" class="btn btn-primary">查詢</button>
            </div>
        </form>
    </div>
</div>

{{-- 總計 --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="fs-4 fw-semibold">{{ sprintf('%d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60) }}</div>
                <div>總工時</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="fs-4 fw-semibold">{{ sprintf('%d:%02d', intdiv($billableMinutes, 60), $billableMinutes % 60) }}</div>
                <div>可計費工時</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="fs-4 fw-semibold">NT$ {{ number_format($totalAmount) }}</div>
                <div>計費金額</div>
            </div>
        </div>
    </div>
</div>

{{-- 分組統計 --}}
<div class="card">
    <div class="card-header"><strong>{{ $groupBy === 'project' ? '按專案' : '按人員' }}統計</strong></div>
    <div class="card-body p-0">
        @if($grouped->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>{{ $groupBy === 'project' ? '專案' : '人員' }}</th>
                        <th>紀錄數</th>
                        <th>總工時</th>
                        <th>可計費工時</th>
                        <th>計費金額</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grouped as $item)
                    <tr>
                        <td><strong>{{ $item['name'] }}</strong></td>
                        <td>{{ $item['entries_count'] }}</td>
                        <td>{{ sprintf('%d:%02d', intdiv($item['total_minutes'], 60), $item['total_minutes'] % 60) }}</td>
                        <td>{{ sprintf('%d:%02d', intdiv($item['billable_minutes'], 60), $item['billable_minutes'] % 60) }}</td>
                        <td>NT$ {{ number_format($item['billable_amount']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state-icon">📊</div>
            <div>此期間無工時紀錄</div>
        </div>
        @endif
    </div>
</div>
@endsection
