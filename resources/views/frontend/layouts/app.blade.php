<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', __('frontend.meta_description'))">
    <meta name="keywords" content="@yield('meta_keywords', '網頁設計,App開發,網站製作,系統開發,UI設計,UX設計,台中網頁設計,MH Studio,孟衡工作室')">
    <meta name="author" content="MH Studio 孟衡工作室">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">
    <title>@yield('title', __('frontend.site_title'))</title>

    {{-- Open Graph --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="MH Studio 孟衡">
    <meta property="og:title" content="@yield('og_title', __('frontend.site_title'))">
    <meta property="og:description" content="@yield('og_description', __('frontend.meta_description'))">
    @hasSection('og_image')
    <meta property="og:image" content="@yield('og_image')">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="zh_TW">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', __('frontend.site_title'))">
    <meta name="twitter:description" content="@yield('og_description', __('frontend.meta_description'))">
    @hasSection('og_image')
    <meta name="twitter:image" content="@yield('og_image')">
    @endif

    {{-- Canonical URL --}}
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Rajdhani:wght@300;400;500;600;700&family=Noto+Sans+TC:wght@300;400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/frontend/mh-studio.scss', 'resources/js/frontend/mh-studio.js'])

    {{-- Schema.org 結構化資料 --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "ProfessionalService",
        "name": "MH Studio 孟衡工作室",
        "alternateName": "孟衡工作室",
        "url": "{{ config('app.url') }}",
        "logo": "{{ config('app.url') }}/images/logo.png",
        "description": "提供客製化網頁設計、App 開發、系統架構與 UI/UX 設計服務的台中在地技術團隊。",
        "telephone": "{{ setting('company_phone', '0912-477-421') }}",
        "email": "{{ setting('company_email', 'bryantchi.work@gmail.com') }}",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "{{ setting('company_address', '台中市西屯區漢成街75號4E') }}",
            "addressLocality": "台中市",
            "addressRegion": "台中",
            "addressCountry": "TW"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": "24.1780",
            "longitude": "120.6465"
        },
        "priceRange": "$$",
        "openingHours": "Mo-Fr 09:00-18:00",
        "founder": {
            "@type": "Person",
            "name": "紀孟勳"
        },
        "knowsAbout": ["網頁設計", "App開發", "Laravel", "Android", "iOS", "UI/UX設計", "系統架構", "SEO優化"],
        "areaServed": {
            "@type": "Country",
            "name": "Taiwan"
        },
        "sameAs": [
            "{{ setting('social_github', '') }}",
            "{{ setting('social_linkedin', '') }}"
        ]
    }
    </script>
    @stack('head')
    @stack('styles')
</head>
<body>
    @yield('content')

    @stack('scripts')
</body>
</html>
