<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', '儀表板') - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body>
    <div class="sidebar sidebar-dark sidebar-fixed" id="sidebar">
        @include('layouts.partials.sidebar')
    </div>

    <div class="wrapper d-flex flex-column min-vh-100 bg-light">
        @include('layouts.partials.header')

        <div class="body flex-grow-1 px-3">
            <div class="container-lg">
                @include('layouts.partials.breadcrumb')

                @include('layouts.partials.alerts')

                @yield('content')
            </div>
        </div>

        @include('layouts.partials.footer')
    </div>

    @stack('scripts')
</body>
</html>
