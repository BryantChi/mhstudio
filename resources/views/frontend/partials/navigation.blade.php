{{-- ===== NAVIGATION ===== --}}
@php
    $isHome = request()->routeIs('home');
    $currentLocale = app()->getLocale();
@endphp
<nav class="nav" id="nav">
  <a href="{{ route('home') }}" class="nav-logo">
    <svg class="nav-logo-icon" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="nav-grad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" style="stop-color:#3a8bfd"/>
          <stop offset="100%" style="stop-color:#00d4ff"/>
        </linearGradient>
      </defs>
      <circle cx="24" cy="24" r="22" fill="none" stroke="url(#nav-grad)" stroke-width="1.2" opacity="0.4"/>
      <circle cx="24" cy="24" r="18" fill="none" stroke="url(#nav-grad)" stroke-width="0.6" opacity="0.2"/>
      <text x="24" y="28" text-anchor="middle" font-family="Orbitron" font-size="14" font-weight="800" fill="url(#nav-grad)">MH</text>
    </svg>
    <div>
      <div class="nav-logo-text">MH STUDIO</div>
      <div class="nav-logo-sub">孟 衡</div>
    </div>
  </a>
  <ul class="nav-links">
    <li><a href="{{ $isHome ? '#services' : route('home') . '#services' }}">{{ __('frontend.nav_services') }}</a></li>
    <li><a href="{{ route('portfolio') }}">{{ __('frontend.nav_portfolio') }}</a></li>
    <li><a href="{{ route('blog') }}">{{ __('frontend.nav_blog') }}</a></li>
    <li><a href="{{ route('about') }}">{{ __('frontend.nav_about') }}</a></li>
    <li><a href="{{ route('quote') }}">{{ __('frontend.nav_quote') }}</a></li>
    <li><a href="{{ $isHome ? '#contact' : route('home') . '#contact' }}">{{ __('frontend.nav_contact') }}</a></li>
    @auth
      @if(auth()->user()->clientProjects()->exists())
      <li><a href="{{ route('client.dashboard') }}">客戶專區</a></li>
      @endif
    @endauth
  </ul>
  <div class="nav-right-group">
    {{-- Language Switcher --}}
    <div class="lang-switcher">
      <button class="lang-switcher-btn" id="langSwitcherBtn" aria-label="Switch language">
        <svg class="lang-switcher-icon" viewBox="0 0 24 24" width="16" height="16">
          <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.5"/>
          <line x1="2" y1="12" x2="22" y2="12" fill="none" stroke="currentColor" stroke-width="1.5"/>
          <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z" fill="none" stroke="currentColor" stroke-width="1.5"/>
        </svg>
        <span class="lang-switcher-label">{{ $currentLocale === 'zh_TW' ? '中文' : 'EN' }}</span>
      </button>
      <div class="lang-switcher-dropdown" id="langSwitcherDropdown">
        <a href="{{ route('language.switch', 'zh_TW') }}" class="lang-option {{ $currentLocale === 'zh_TW' ? 'active' : '' }}">
          <span>中文</span>
          @if($currentLocale === 'zh_TW')
            <svg viewBox="0 0 24 24" width="14" height="14"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2"/></svg>
          @endif
        </a>
        <a href="{{ route('language.switch', 'en') }}" class="lang-option {{ $currentLocale === 'en' ? 'active' : '' }}">
          <span>English</span>
          @if($currentLocale === 'en')
            <svg viewBox="0 0 24 24" width="14" height="14"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2"/></svg>
          @endif
        </a>
      </div>
    </div>
    <a href="{{ route('quote') }}" class="nav-cta nav-cta-pulse">{{ __('frontend.nav_cta') }}</a>
  </div>
  <button class="nav-mobile-toggle" id="mobileToggle">
    <span></span><span></span><span></span>
  </button>
</nav>

<div class="mobile-menu" id="mobileMenu">
  <a href="{{ $isHome ? '#services' : route('home') . '#services' }}">{{ __('frontend.nav_mobile_services') }}</a>
  <a href="{{ route('portfolio') }}">{{ __('frontend.nav_mobile_portfolio') }}</a>
  <a href="{{ route('blog') }}">{{ __('frontend.nav_mobile_blog') }}</a>
  <a href="{{ route('about') }}">{{ __('frontend.nav_mobile_about') }}</a>
  <a href="{{ route('quote') }}">{{ __('frontend.nav_mobile_quote') }}</a>
  <a href="{{ $isHome ? '#contact' : route('home') . '#contact' }}">{{ __('frontend.nav_mobile_contact') }}</a>
  @auth
    @if(auth()->user()->clientProjects()->exists())
    <a href="{{ route('client.dashboard') }}">客戶專區</a>
    @endif
  @endauth
  <a href="/#contact" class="mobile-contact-btn">聯繫我們</a>
  {{-- Mobile Language Switcher --}}
  <div class="mobile-lang-switcher">
    <a href="{{ route('language.switch', 'zh_TW') }}" class="mobile-lang-option {{ $currentLocale === 'zh_TW' ? 'active' : '' }}">中文</a>
    <span class="mobile-lang-divider">/</span>
    <a href="{{ route('language.switch', 'en') }}" class="mobile-lang-option {{ $currentLocale === 'en' ? 'active' : '' }}">EN</a>
  </div>
</div>
