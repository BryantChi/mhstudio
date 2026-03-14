<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', __('frontend.meta_description'))">
    <title>@yield('title', __('frontend.site_title'))</title>

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('og_title', __('frontend.site_title'))">
    <meta property="og:description" content="@yield('og_description', __('frontend.meta_description'))">
    @hasSection('og_image')
    <meta property="og:image" content="@yield('og_image')">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ app()->getLocale() }}">

    {{-- Canonical URL --}}
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Rajdhani:wght@300;400;500;600;700&family=Noto+Sans+TC:wght@300;400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/frontend/mh-studio.scss', 'resources/js/frontend/mh-studio.js'])
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "MH Studio",
        "url": "{{ config('app.url') }}",
        "description": "專注於 Web 與 App 開發的技術團隊",
        "sameAs": []
    }
    </script>
    @stack('styles')
</head>
<body>
    @yield('content')

    @stack('scripts')
</body>
</html>
