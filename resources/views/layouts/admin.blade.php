<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', '儀表板') - {{ config('app.name') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ setting('site_favicon', '/favicon.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Fancybox 5 (圖片預覽) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5/dist/fancybox/fancybox.css">

    @stack('styles')
</head>
{{-- 記錄列表頁 URL（含頁數）供編輯後返回 --}}
@php
    $currentRoute = Route::currentRouteName() ?? '';
    if (request()->isMethod('get') && str_ends_with($currentRoute, '.index')) {
        $prefix = str_replace('.index', '', $currentRoute);
        session()->put("admin_list.{$prefix}", request()->fullUrl());
    }
@endphp
<body>
    <div class="sidebar sidebar-dark sidebar-fixed" id="sidebar">
        @include('layouts.partials.sidebar')
    </div>

    <div class="wrapper d-flex flex-column min-vh-100 bg-light">
        @include('layouts.partials.header')

        {{-- 模擬登入橫幅 --}}
        @if(session('impersonator_id'))
        <div class="impersonate-banner" style="background:linear-gradient(90deg,#e65100,#ff6d00);color:#fff;padding:8px 16px;text-align:center;font-size:0.875rem;font-weight:500;position:sticky;top:0;z-index:1050;display:flex;align-items:center;justify-content:center;gap:8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"/></svg>
            目前正以「<strong>{{ auth()->user()->name }}</strong>」身分操作
            （原始帳號：{{ session('impersonator_name') }}）
            <a href="{{ route('admin.impersonate.leave') }}" class="btn btn-sm btn-light ms-2" style="color:#e65100;font-weight:600;padding:2px 12px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" class="me-1"><path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/><path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/></svg>
                離開模擬
            </a>
        </div>
        @endif

        <div class="body flex-grow-1 px-3">
            <div class="container-lg">
                @include('layouts.partials.breadcrumb')

                @include('layouts.partials.alerts')

                @yield('content')
            </div>
        </div>

        @include('layouts.partials.footer')
    </div>

    <!-- Fancybox 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5/dist/fancybox/fancybox.umd.js"></script>
    <script>Fancybox.bind('[data-fancybox]', { animated: true, Images: { zoom: true } });</script>

    @stack('scripts')
</body>
</html>
