@extends('layouts.admin')

@section('title', '系統設定')

@php
    $breadcrumbs = [
        ['title' => '系統設定', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">系統設定</h2>
        <p class="text-muted">管理網站的各項設定</p>
    </div>
</div>

<div class="row g-4">
    <!-- 一般設定 -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-primary bg-opacity-10">
                            <svg class="icon icon-xl text-primary">
                                <use xlink:href="/assets/icons/free.svg#cil-settings"></use>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="card-title mb-0">一般設定</h5>
                    </div>
                </div>
                <p class="card-text text-muted">
                    網站基本資訊、語言、時區、快取等基礎設定
                </p>
                <a href="{{ route('admin.settings.general') }}" class="btn btn-outline-primary w-100">
                    <svg class="icon me-2">
                        <use xlink:href="/assets/icons/free.svg#cil-arrow-right"></use>
                    </svg>
                    前往設定
                </a>
            </div>
        </div>
    </div>

    <!-- SEO 設定 -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-success bg-opacity-10">
                            <svg class="icon icon-xl text-success">
                                <use xlink:href="/assets/icons/free.svg#cil-search"></use>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="card-title mb-0">SEO 設定</h5>
                    </div>
                </div>
                <p class="card-text text-muted">
                    搜尋引擎優化、Meta 標籤、社群媒體、驗證碼
                </p>
                <a href="{{ route('admin.settings.seo') }}" class="btn btn-outline-success w-100">
                    <svg class="icon me-2">
                        <use xlink:href="/assets/icons/free.svg#cil-arrow-right"></use>
                    </svg>
                    前往設定
                </a>
            </div>
        </div>
    </div>

    <!-- 分析追蹤 -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-info bg-opacity-10">
                            <svg class="icon icon-xl text-info">
                                <use xlink:href="/assets/icons/free.svg#cil-chart-line"></use>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="card-title mb-0">分析追蹤</h5>
                    </div>
                </div>
                <p class="card-text text-muted">
                    Google Analytics、GTM、Facebook Pixel 等追蹤設定
                </p>
                <a href="{{ route('admin.settings.analytics') }}" class="btn btn-outline-info w-100">
                    <svg class="icon me-2">
                        <use xlink:href="/assets/icons/free.svg#cil-arrow-right"></use>
                    </svg>
                    前往設定
                </a>
            </div>
        </div>
    </div>

    <!-- 郵件設定 -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-warning bg-opacity-10">
                            <svg class="icon icon-xl text-warning">
                                <use xlink:href="/assets/icons/free.svg#cil-envelope-closed"></use>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="card-title mb-0">郵件設定</h5>
                    </div>
                </div>
                <p class="card-text text-muted">
                    SMTP 伺服器、郵件驅動、發件人資訊設定
                </p>
                <a href="{{ route('admin.settings.mail') }}" class="btn btn-outline-warning w-100">
                    <svg class="icon me-2">
                        <use xlink:href="/assets/icons/free.svg#cil-arrow-right"></use>
                    </svg>
                    前往設定
                </a>
            </div>
        </div>
    </div>

    <!-- SEO 管理 -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-danger bg-opacity-10">
                            <svg class="icon icon-xl text-danger">
                                <use xlink:href="/assets/icons/free.svg#cil-star"></use>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="card-title mb-0">SEO 管理</h5>
                    </div>
                </div>
                <p class="card-text text-muted">
                    Meta 標籤、Sitemap、Robots.txt、SEO 分析
                </p>
                <a href="{{ route('admin.seo.index') }}" class="btn btn-outline-danger w-100">
                    <svg class="icon me-2">
                        <use xlink:href="/assets/icons/free.svg#cil-arrow-right"></use>
                    </svg>
                    前往管理
                </a>
            </div>
        </div>
    </div>

    <!-- 清除快取 -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-secondary bg-opacity-10">
                            <svg class="icon icon-xl text-secondary">
                                <use xlink:href="/assets/icons/free.svg#cil-trash"></use>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="card-title mb-0">清除快取</h5>
                    </div>
                </div>
                <p class="card-text text-muted">
                    清除應用程式、設定、路由、視圖等快取
                </p>
                <form method="POST" action="{{ route('admin.settings.clear-cache') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100" onclick="return confirm('確定要清除所有快取嗎？')">
                        <svg class="icon me-2">
                            <use xlink:href="/assets/icons/free.svg#cil-reload"></use>
                        </svg>
                        清除快取
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 快速資訊 -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <strong>系統資訊</strong>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="border-start border-primary border-3 ps-3">
                            <div class="text-muted small">Laravel 版本</div>
                            <div class="fs-5 fw-semibold">{{ app()->version() }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-start border-success border-3 ps-3">
                            <div class="text-muted small">PHP 版本</div>
                            <div class="fs-5 fw-semibold">{{ PHP_VERSION }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-start border-info border-3 ps-3">
                            <div class="text-muted small">環境</div>
                            <div class="fs-5 fw-semibold">{{ app()->environment() }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-start border-warning border-3 ps-3">
                            <div class="text-muted small">除錯模式</div>
                            <div class="fs-5 fw-semibold">{{ config('app.debug') ? '開啟' : '關閉' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .avatar {
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
    }
    .avatar-lg {
        width: 4rem;
        height: 4rem;
    }
</style>
@endpush
@endsection
