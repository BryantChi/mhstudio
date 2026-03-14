{{-- ===== FLOATING ACTION BUTTON (FAB) ===== --}}
<div class="fab-container" id="fabContainer">
  {{-- Backdrop for mobile --}}
  <div class="fab-backdrop" id="fabBackdrop"></div>

  {{-- Expanded options --}}
  <div class="fab-options" id="fabOptions">
    <a href="{{ setting('social_line', '#') }}" class="fab-option" target="_blank" rel="noopener" aria-label="LINE">
      <svg viewBox="0 0 24 24" width="20" height="20"><path d="M12 2C6.48 2 2 5.58 2 10c0 2.13 1.07 4.04 2.76 5.47L4 20l3.53-2.12C8.87 18.28 10.4 18.5 12 18.5c5.52 0 10-3.58 10-8S17.52 2 12 2z"/></svg>
      <span>LINE</span>
    </a>
    <a href="mailto:{{ setting('contact_email', 'hello@mhstudio.dev') }}" class="fab-option" aria-label="Email">
      <svg viewBox="0 0 24 24" width="20" height="20"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      <span>Email</span>
    </a>
    <a href="{{ request()->routeIs('home') ? '#contact' : url('/#contact') }}" class="fab-option fab-option-consult" aria-label="{{ __('frontend.fab_quick_consult') }}">
      <svg viewBox="0 0 24 24" width="20" height="20"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
      <span>{{ __('frontend.fab_quick_consult') }}</span>
    </a>
  </div>

  {{-- Main FAB button --}}
  <button class="fab-main" id="fabToggle" aria-label="{{ __('frontend.fab_contact_us') }}">
    <svg class="fab-icon-chat" viewBox="0 0 24 24" width="24" height="24">
      <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
    </svg>
    <svg class="fab-icon-close" viewBox="0 0 24 24" width="24" height="24">
      <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
  </button>
</div>

{{-- ===== FOOTER ===== --}}
<footer class="footer">
  <div class="footer-nav">
    <a href="{{ route('home') }}#services">{{ __('frontend.nav_services') }}</a>
    <a href="{{ route('portfolio') }}">{{ __('frontend.nav_portfolio') }}</a>
    <a href="{{ route('blog') }}">{{ __('frontend.nav_blog') }}</a>
    <a href="{{ route('about') }}">{{ __('frontend.nav_about') }}</a>
    <a href="{{ route('quote') }}">{{ __('frontend.nav_quote') }}</a>
  </div>
  @php
      $projectCount = setting('stats_projects_completed', 50);
      $ontimeRate = setting('stats_ontime_delivery', 99);
  @endphp
  <p class="footer-trust">合作超過 {{ $projectCount }}+ 個專案 · {{ $ontimeRate }}% 準時交付</p>
  <div class="footer-left">
    <svg width="28" height="28" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
      <circle cx="24" cy="24" r="22" fill="none" stroke="#3a8bfd" stroke-width="1"/>
      <text x="24" y="28" text-anchor="middle" font-family="Orbitron" font-size="12" font-weight="800" fill="#00d4ff">MH</text>
    </svg>
    <span class="footer-logo-text">{{ setting('company_name', 'MH STUDIO') }} &copy; {{ date('Y') }}</span>
  </div>
  <div class="footer-social">
    <a href="{{ setting('social_github', '#') }}" aria-label="GitHub">
      <svg viewBox="0 0 24 24"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22"/></svg>
    </a>
    <a href="{{ setting('social_linkedin', '#') }}" aria-label="LinkedIn">
      <svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
    </a>
    <a href="{{ setting('social_line', '#') }}" aria-label="LINE">
      <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 5.58 2 10c0 2.13 1.07 4.04 2.76 5.47L4 20l3.53-2.12C8.87 18.28 10.4 18.5 12 18.5c5.52 0 10-3.58 10-8S17.52 2 12 2z"/></svg>
    </a>
  </div>
  <div class="footer-right">
    {!! __('frontend.footer_slogan') !!}
  </div>
</footer>
