@extends('layouts.admin')

@section('title', '系統資訊')

@php
    $breadcrumbs = [
        ['title' => '系統資訊', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <h2 class="mb-0">系統資訊</h2>
        <p class="text-muted">查看系統運行環境和版本資訊</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <strong>應用程式資訊</strong>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <td class="fw-semibold">應用程式名稱</td>
                            <td>{{ config('app.name') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Laravel 版本</td>
                            <td>{{ $info['laravel_version'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">PHP 版本</td>
                            <td>{{ $info['php_version'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">環境</td>
                            <td>
                                <span class="badge bg-{{ app()->environment('production') ? 'danger' : 'success' }}">
                                    {{ app()->environment() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">除錯模式</td>
                            <td>
                                <span class="badge bg-{{ config('app.debug') ? 'warning' : 'success' }}">
                                    {{ config('app.debug') ? '已啟用' : '已停用' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <strong>伺服器資訊</strong>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <td class="fw-semibold">伺服器軟體</td>
                            <td>{{ $info['server'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">資料庫驅動</td>
                            <td>{{ $info['database'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">快取驅動</td>
                            <td>{{ $info['cache_driver'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">佇列驅動</td>
                            <td>{{ $info['queue_driver'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">時區</td>
                            <td>{{ $info['timezone'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">語言</td>
                            <td>{{ $info['locale'] ?? 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>已安裝的套件</strong>
                <span class="badge bg-primary">{{ count($packages) }} 個套件</span>
            </div>
            <div class="card-body">
                @if(count($packages) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>套件名稱</th>
                                <th>版本</th>
                                <th>描述</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($packages as $package)
                            <tr>
                                <td class="fw-semibold">
                                    <code>{{ $package['name'] }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $package['version'] }}</span>
                                </td>
                                <td class="text-muted">{{ $package['description'] ?: '無描述' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <svg class="icon icon-xxl mb-3 opacity-25">
                        <use xlink:href="/assets/icons/free.svg#cil-inbox"></use>
                    </svg>
                    <p>未找到已安裝的套件資訊</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
