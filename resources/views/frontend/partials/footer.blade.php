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
    <a href="mailto:{{ setting('contact_email', 'bryantchi.work@gmail.com') }}" class="fab-option" aria-label="Email">
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
      $legalLinks = \App\Models\LegalPage::active()->ordered()->get(['title', 'slug']);
  @endphp
  @if($legalLinks->count() > 0)
  <div class="footer-legal">
    @foreach($legalLinks as $link)
    <a href="{{ route('legal.show', $link->slug) }}">{{ $link->title }}</a>
    @if(!$loop->last) <span class="footer-legal-sep">|</span> @endif
    @endforeach
  </div>
  @endif
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
    @if(setting('social_github_enabled', '1') == '1' && setting('social_github', '#') !== '#' && setting('social_github') !== '')
    <a href="{{ setting('social_github') }}" aria-label="GitHub" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22"/></svg>
    </a>
    @endif
    @if(setting('social_linkedin_enabled', '1') == '1' && setting('social_linkedin', '#') !== '#' && setting('social_linkedin') !== '')
    <a href="{{ setting('social_linkedin') }}" aria-label="LinkedIn" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
    </a>
    @endif
    @if(setting('social_line_enabled', '1') == '1' && setting('social_line', '#') !== '#' && setting('social_line') !== '')
    <a href="{{ setting('social_line') }}" aria-label="LINE" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 5.58 2 10c0 2.13 1.07 4.04 2.76 5.47L4 20l3.53-2.12C8.87 18.28 10.4 18.5 12 18.5c5.52 0 10-3.58 10-8S17.52 2 12 2z"/></svg>
    </a>
    @endif
    @if(setting('social_facebook_enabled', '0') == '1' && setting('social_facebook', '#') !== '#' && setting('social_facebook') !== '')
    <a href="{{ setting('social_facebook') }}" aria-label="Facebook" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
    </a>
    @endif
    @if(setting('social_twitter_enabled', '0') == '1' && setting('social_twitter', '#') !== '#' && setting('social_twitter') !== '')
    <a href="{{ setting('social_twitter') }}" aria-label="Twitter / X" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
    </a>
    @endif
    @if(setting('social_instagram_enabled', '0') == '1' && setting('social_instagram', '#') !== '#' && setting('social_instagram') !== '')
    <a href="{{ setting('social_instagram') }}" aria-label="Instagram" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
    </a>
    @endif
    @if(setting('social_youtube_enabled', '0') == '1' && setting('social_youtube', '#') !== '#' && setting('social_youtube') !== '')
    <a href="{{ setting('social_youtube') }}" aria-label="YouTube" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24"><path d="M22.54 6.42a2.78 2.78 0 00-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 2A29 29 0 001 11.75a29 29 0 00.46 5.33A2.78 2.78 0 003.4 19.1c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 001.94-2 29 29 0 00.46-5.25 29 29 0 00-.46-5.43z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/></svg>
    </a>
    @endif
  </div>
  <div class="footer-right">
    {!! __('frontend.footer_slogan') !!}
  </div>
</footer>
