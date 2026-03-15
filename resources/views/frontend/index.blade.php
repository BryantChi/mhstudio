@extends('frontend.layouts.app')

@section('content')
    @include('frontend.partials.navigation')

    {{-- ===== HERO ===== --}}
    <section class="hero">
      <div class="hero-bg-grid"></div>
      <div class="hero-particles" id="particles"></div>
      <div class="hero-orb hero-orb-1"></div>
      <div class="hero-orb hero-orb-2"></div>
      <div class="hero-ring"></div>
      <div class="hero-ring hero-ring-2"></div>

      <div class="hero-content">
        {{-- MH LOGO --}}
        <svg class="hero-logo" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="logo-grad-1" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" style="stop-color:#3a8bfd"/>
              <stop offset="50%" style="stop-color:#00d4ff"/>
              <stop offset="100%" style="stop-color:#3a8bfd"/>
            </linearGradient>
            <linearGradient id="logo-grad-2" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" style="stop-color:#6bb5ff"/>
              <stop offset="100%" style="stop-color:#00d4ff"/>
            </linearGradient>
            <filter id="glow">
              <feGaussianBlur stdDeviation="3" result="blur"/>
              <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
            </filter>
            <filter id="glow-strong">
              <feGaussianBlur stdDeviation="6" result="blur"/>
              <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
            </filter>
          </defs>
          {{-- Outer rings --}}
          <circle cx="100" cy="100" r="92" fill="none" stroke="url(#logo-grad-1)" stroke-width="0.8" opacity="0.3">
            <animateTransform attributeName="transform" type="rotate" values="0 100 100;360 100 100" dur="40s" repeatCount="indefinite"/>
          </circle>
          <circle cx="100" cy="100" r="85" fill="none" stroke="url(#logo-grad-2)" stroke-width="0.5" opacity="0.2" stroke-dasharray="8 12">
            <animateTransform attributeName="transform" type="rotate" values="360 100 100;0 100 100" dur="30s" repeatCount="indefinite"/>
          </circle>
          {{-- Orbital dots --}}
          <circle cx="100" cy="8" r="3" fill="#00d4ff" filter="url(#glow-strong)" opacity="0.8">
            <animateTransform attributeName="transform" type="rotate" values="0 100 100;360 100 100" dur="40s" repeatCount="indefinite"/>
          </circle>
          <circle cx="15" cy="100" r="2" fill="#3a8bfd" filter="url(#glow)" opacity="0.6">
            <animateTransform attributeName="transform" type="rotate" values="360 100 100;0 100 100" dur="30s" repeatCount="indefinite"/>
          </circle>
          {{-- Inner circle glow --}}
          <circle cx="100" cy="100" r="70" fill="none" stroke="url(#logo-grad-1)" stroke-width="1" opacity="0.15"/>
          {{-- MH Text --}}
          <text x="100" y="112" text-anchor="middle" font-family="Orbitron" font-size="52" font-weight="900" fill="url(#logo-grad-1)" filter="url(#glow)" letter-spacing="2">MH</text>
          {{-- Decorative lines --}}
          <line x1="45" y1="128" x2="80" y2="128" stroke="url(#logo-grad-2)" stroke-width="1" opacity="0.5"/>
          <line x1="120" y1="128" x2="155" y2="128" stroke="url(#logo-grad-2)" stroke-width="1" opacity="0.5"/>
          <circle cx="42" cy="128" r="2" fill="#00d4ff" opacity="0.6"/>
          <circle cx="158" cy="128" r="2" fill="#00d4ff" opacity="0.6"/>
        </svg>

        <div class="hero-badge">
          <span class="hero-badge-dot"></span>
          {{ __('frontend.hero_badge') }}
        </div>
        <h1 class="hero-title">{{ setting('hero_title', 'MH STUDIO') }}</h1>
        <p class="hero-title-cn">{{ setting('hero_subtitle', '孟 衡 工 作 室') }}</p>
        <p class="hero-tagline">{!! setting('hero_tagline', 'Balance <span>&bull;</span> Precision <span>&bull;</span> Innovation') !!}</p>
        <p class="hero-desc">
          {!! nl2br(e(setting('hero_description', '專注 App 開發與網頁設計，以精準技術與創新思維，為您打造超越期待的數位產品體驗。'))) !!}
        </p>
        <div class="hero-actions">
          <a href="#contact" class="btn-primary">{{ __('frontend.hero_cta_consult') }}</a>
          <a href="#portfolio" class="btn-secondary">{{ __('frontend.hero_cta_portfolio') }}</a>
        </div>
      </div>

      <div class="hero-scroll-indicator">
        <div class="hero-scroll-line"></div>
        <span class="hero-scroll-text">{{ __('frontend.hero_scroll') }}</span>
      </div>
    </section>

    {{-- ===== STATS ===== --}}
    <div class="stats-bar">
      <div class="stat-item animate-on-scroll">
        <div class="stat-number" data-target="{{ setting('stats_years_experience', 7) }}">0</div>
        <div class="stat-label">{{ __('frontend.stats_years') }}</div>
      </div>
      <div class="stat-item animate-on-scroll">
        <div class="stat-number" data-target="{{ setting('stats_projects_completed', 50) }}">0</div>
        <div class="stat-label">{{ __('frontend.stats_projects') }}</div>
      </div>
      <div class="stat-item animate-on-scroll">
        <div class="stat-number" data-target="{{ setting('stats_happy_clients', 30) }}">0</div>
        <div class="stat-label">{{ __('frontend.stats_clients') }}</div>
      </div>
      <div class="stat-item animate-on-scroll">
        <div class="stat-number" data-target="{{ setting('stats_ontime_delivery', 99) }}">0</div>
        <div class="stat-label">{{ __('frontend.stats_ontime') }}</div>
      </div>
    </div>

    {{-- 信賴夥伴 --}}
    @if(isset($trustedClients) && $trustedClients->count() > 0)
    <section class="trusted-clients-section">
        <div class="container">
            <p class="trusted-label">Trusted By</p>
            <div class="trusted-logos">
                @foreach($trustedClients as $trustedClient)
                <div class="trusted-logo-item" title="{{ $trustedClient->name }}">
                    <img src="{{ $trustedClient->avatar }}" alt="{{ $trustedClient->name }}" loading="lazy">
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ===== SERVICES ===== --}}
    <section id="services">
      <div class="section-header animate-on-scroll">
        <div class="section-label">{{ __('frontend.services_label') }}</div>
        <h2 class="section-title">{{ __('frontend.services_title') }}</h2>
        <div class="section-divider"></div>
        <p class="section-desc">{{ __('frontend.services_desc') }}</p>
      </div>

      <div class="services-grid">
        @if($services->isNotEmpty())
          @foreach($services as $service)
            <a href="{{ route('services.show', $service->slug) }}" class="service-card animate-on-scroll" style="text-decoration:none;color:inherit;">
              <div class="service-icon">
                @if($service->icon)
                  @include('components.render-icon', ['icon' => $service->icon, 'size' => 28])
                @else
                  <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                @endif
              </div>
              <h3>{{ $service->title }}</h3>
              <p>{{ $service->excerpt }}</p>
              @if($service->tech_tags)
                <div class="service-tags">
                  @foreach($service->tech_tags as $tag)
                    <span class="service-tag">{{ strtoupper($tag) }}</span>
                  @endforeach
                </div>
              @endif
            </a>
          @endforeach
        @else
          {{-- Static fallback --}}
          <div class="service-card animate-on-scroll">
            <div class="service-icon">
              <svg viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12" y2="18.01"/></svg>
            </div>
            <h3>{{ __('frontend.services_fallback_app_title') }}</h3>
            <p>{{ __('frontend.services_fallback_app_desc') }}</p>
            <div class="service-tags">
              <span class="service-tag">KOTLIN</span>
              <span class="service-tag">FLUTTER</span>
              <span class="service-tag">COMPOSE</span>
            </div>
          </div>

          <div class="service-card animate-on-scroll">
            <div class="service-icon">
              <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <h3>{{ __('frontend.services_fallback_web_title') }}</h3>
            <p>{{ __('frontend.services_fallback_web_desc') }}</p>
            <div class="service-tags">
              <span class="service-tag">HTML/CSS</span>
              <span class="service-tag">LARAVEL</span>
              <span class="service-tag">SEO</span>
            </div>
          </div>

          <div class="service-card animate-on-scroll">
            <div class="service-icon">
              <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
            </div>
            <h3>{{ __('frontend.services_fallback_arch_title') }}</h3>
            <p>{{ __('frontend.services_fallback_arch_desc') }}</p>
            <div class="service-tags">
              <span class="service-tag">MVVM</span>
              <span class="service-tag">API</span>
              <span class="service-tag">CI/CD</span>
            </div>
          </div>

          <div class="service-card animate-on-scroll">
            <div class="service-icon">
              <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
            </div>
            <h3>{{ __('frontend.services_fallback_design_title') }}</h3>
            <p>{{ __('frontend.services_fallback_design_desc') }}</p>
            <div class="service-tags">
              <span class="service-tag">FIGMA</span>
              <span class="service-tag">PROTOTYPE</span>
              <span class="service-tag">DESIGN</span>
            </div>
          </div>
        @endif
      </div>
    </section>

    {{-- ===== PORTFOLIO ===== --}}
    <section id="portfolio">
      <div class="section-header animate-on-scroll">
        <div class="section-label">{{ __('frontend.portfolio_label') }}</div>
        <h2 class="section-title">{{ __('frontend.portfolio_title') }}</h2>
        <div class="section-divider"></div>
        <p class="section-desc">{{ __('frontend.portfolio_desc') }}</p>
      </div>

      <div class="portfolio-grid">
        @if($featuredProjects->isNotEmpty())
          @foreach($featuredProjects as $project)
            <a href="{{ route('portfolio.show', $project->slug) }}" class="portfolio-card animate-on-scroll" style="text-decoration:none;color:inherit;">
              <div class="portfolio-thumb">
                @if($project->cover_image)
                  <div class="portfolio-thumb-bg" style="background: url('{{ $project->cover_image }}') center/cover; position:relative;">
                    <div style="position:absolute;inset:0;background:linear-gradient(135deg, rgba(10,17,40,0.7), rgba(22,32,64,0.7));display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:48px;font-weight:800;color:rgba(0,212,255,0.08);letter-spacing:8px;">{{ strtoupper($project->category ?? 'PROJECT') }}</div>
                  </div>
                @else
                  <div class="portfolio-thumb-bg" style="background: linear-gradient(135deg, #0a1628, #162040);">{{ strtoupper($project->category ?? 'PROJECT') }}</div>
                @endif
                <div class="portfolio-overlay"><span class="portfolio-overlay-btn">{{ __('frontend.portfolio_view_detail') }}</span></div>
              </div>
              <div class="portfolio-info">
                <h3>{{ $project->title }}</h3>
                <p>{{ $project->excerpt }}</p>
                @if($project->tech_stack)
                  <div class="portfolio-tech">
                    @foreach($project->tech_stack as $tech)
                      <span>{{ $tech }}</span>
                    @endforeach
                  </div>
                @endif
              </div>
            </a>
          @endforeach
        @else
          {{-- Static fallback --}}
          <div class="portfolio-card animate-on-scroll">
            <div class="portfolio-thumb">
              <div class="portfolio-thumb-bg" style="background: linear-gradient(135deg, #0a1628, #162040);">FINTECH</div>
              <div class="portfolio-overlay"><button class="portfolio-overlay-btn">{{ __('frontend.portfolio_view_detail') }}</button></div>
            </div>
            <div class="portfolio-info">
              <h3>{{ __('frontend.portfolio_fallback_fintech_title') }}</h3>
              <p>{{ __('frontend.portfolio_fallback_fintech_desc') }}</p>
              <div class="portfolio-tech">
                <span>Kotlin</span><span>Jetpack Compose</span><span>MVVM</span>
              </div>
            </div>
          </div>

          <div class="portfolio-card animate-on-scroll">
            <div class="portfolio-thumb">
              <div class="portfolio-thumb-bg" style="background: linear-gradient(135deg, #0f1a2e, #1a2845);">HEALTH</div>
              <div class="portfolio-overlay"><button class="portfolio-overlay-btn">{{ __('frontend.portfolio_view_detail') }}</button></div>
            </div>
            <div class="portfolio-info">
              <h3>{{ __('frontend.portfolio_fallback_health_title') }}</h3>
              <p>{{ __('frontend.portfolio_fallback_health_desc') }}</p>
              <div class="portfolio-tech">
                <span>Android</span><span>BLE</span><span>JNI</span>
              </div>
            </div>
          </div>

          <div class="portfolio-card animate-on-scroll">
            <div class="portfolio-thumb">
              <div class="portfolio-thumb-bg" style="background: linear-gradient(135deg, #0b1525, #152038);">BRAND</div>
              <div class="portfolio-overlay"><button class="portfolio-overlay-btn">{{ __('frontend.portfolio_view_detail') }}</button></div>
            </div>
            <div class="portfolio-info">
              <h3>{{ __('frontend.portfolio_fallback_brand_title') }}</h3>
              <p>{{ __('frontend.portfolio_fallback_brand_desc') }}</p>
              <div class="portfolio-tech">
                <span>HTML/CSS</span><span>Laravel</span><span>SEO</span>
              </div>
            </div>
          </div>
        @endif
      </div>

      <div style="text-align:center;margin-top:48px;" class="animate-on-scroll">
        <a href="{{ route('portfolio') }}" class="btn-secondary">{{ __('frontend.portfolio_view_all') }}</a>
      </div>
    </section>

    {{-- ===== PROCESS ===== --}}
    <section id="process">
      <div class="section-header animate-on-scroll">
        <div class="section-label">{{ __('frontend.process_label') }}</div>
        <h2 class="section-title">{{ __('frontend.process_title') }}</h2>
        <div class="section-divider"></div>
        <p class="section-desc">{{ __('frontend.process_desc') }}</p>
      </div>

      <div class="process-timeline">
        <div class="process-step animate-on-scroll">
          <div class="process-number">01</div>
          <div class="process-content">
            <h3>{{ __('frontend.process_step1_title') }}</h3>
            <p>{{ __('frontend.process_step1_desc') }}</p>
          </div>
        </div>
        <div class="process-step animate-on-scroll">
          <div class="process-number">02</div>
          <div class="process-content">
            <h3>{{ __('frontend.process_step2_title') }}</h3>
            <p>{{ __('frontend.process_step2_desc') }}</p>
          </div>
        </div>
        <div class="process-step animate-on-scroll">
          <div class="process-number">03</div>
          <div class="process-content">
            <h3>{{ __('frontend.process_step3_title') }}</h3>
            <p>{{ __('frontend.process_step3_desc') }}</p>
          </div>
        </div>
        <div class="process-step animate-on-scroll">
          <div class="process-number">04</div>
          <div class="process-content">
            <h3>{{ __('frontend.process_step4_title') }}</h3>
            <p>{{ __('frontend.process_step4_desc') }}</p>
          </div>
        </div>
      </div>
    </section>

    {{-- ===== TECH STACK ===== --}}
    <section id="tech">
      <div class="section-header animate-on-scroll">
        <div class="section-label">{{ __('frontend.tech_label') }}</div>
        <h2 class="section-title">{{ __('frontend.tech_title') }}</h2>
        <div class="section-divider"></div>
      </div>

      <div class="tech-grid">
        @php
          $techStack = setting('tech_stack');
        @endphp
        @if(is_array($techStack) && count($techStack) > 0)
          @foreach($techStack as $tech)
            <div class="tech-item animate-on-scroll"><div class="tech-item-name">{{ $tech['name'] }}</div><div class="tech-item-type">{{ $tech['type'] }}</div></div>
          @endforeach
        @else
          <div class="tech-item animate-on-scroll"><div class="tech-item-name">Kotlin</div><div class="tech-item-type">Android</div></div>
          <div class="tech-item animate-on-scroll"><div class="tech-item-name">Flutter</div><div class="tech-item-type">Cross-Platform</div></div>
          <div class="tech-item animate-on-scroll"><div class="tech-item-name">Compose</div><div class="tech-item-type">UI Framework</div></div>
          <div class="tech-item animate-on-scroll"><div class="tech-item-name">Laravel</div><div class="tech-item-type">Backend</div></div>
          <div class="tech-item animate-on-scroll"><div class="tech-item-name">MVVM</div><div class="tech-item-type">Architecture</div></div>
          <div class="tech-item animate-on-scroll"><div class="tech-item-name">Git</div><div class="tech-item-type">Version Control</div></div>
          <div class="tech-item animate-on-scroll"><div class="tech-item-name">BLE</div><div class="tech-item-type">IoT Protocol</div></div>
          <div class="tech-item animate-on-scroll"><div class="tech-item-name">Firebase</div><div class="tech-item-type">Cloud</div></div>
          <div class="tech-item animate-on-scroll"><div class="tech-item-name">REST API</div><div class="tech-item-type">Integration</div></div>
          <div class="tech-item animate-on-scroll"><div class="tech-item-name">CI/CD</div><div class="tech-item-type">DevOps</div></div>
          <div class="tech-item animate-on-scroll"><div class="tech-item-name">SEO</div><div class="tech-item-type">Marketing</div></div>
          <div class="tech-item animate-on-scroll"><div class="tech-item-name">Figma</div><div class="tech-item-type">Design</div></div>
        @endif
      </div>
    </section>

    {{-- ===== TESTIMONIALS ===== --}}
    @if($testimonials->isNotEmpty())
    <section id="testimonials">
      <div class="section-header animate-on-scroll">
        <div class="section-label">{{ __('frontend.testimonials_label') }}</div>
        <h2 class="section-title">{{ __('frontend.testimonials_title') }}</h2>
        <div class="section-divider"></div>
        <p class="section-desc">{{ __('frontend.testimonials_desc') }}</p>
      </div>

      <div class="testimonials-grid">
        @foreach($testimonials as $testimonial)
          <div class="testimonial-card animate-on-scroll">
            <div class="testimonial-stars">
              @for($i = 1; $i <= 5; $i++)
                <svg class="testimonial-star {{ $i <= $testimonial->rating ? 'active' : '' }}" viewBox="0 0 24 24">
                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
              @endfor
            </div>
            <div class="testimonial-content">
              <p>"{{ $testimonial->content }}"</p>
            </div>
            <div class="testimonial-author">
              <div class="testimonial-avatar">
                @if($testimonial->avatar)
                  <img src="{{ $testimonial->avatar }}" alt="{{ $testimonial->client_name }}">
                @else
                  <span>{{ mb_substr($testimonial->client_name, 0, 1) }}</span>
                @endif
              </div>
              <div class="testimonial-info">
                <div class="testimonial-name">{{ $testimonial->client_name }}</div>
                <div class="testimonial-role">
                  @if($testimonial->position){{ $testimonial->position }}@endif
                  @if($testimonial->position && $testimonial->company) / @endif
                  @if($testimonial->company){{ $testimonial->company }}@endif
                </div>
              </div>
              @if($testimonial->project_type)
                <div class="testimonial-project-type">{{ $testimonial->project_type }}</div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </section>
    @endif

    {{-- ===== BLOG PREVIEW ===== --}}
    @if($latestArticles->isNotEmpty())
    <section id="blog-preview">
      <div class="section-header animate-on-scroll">
        <div class="section-label">{{ __('frontend.blog_preview_label') }}</div>
        <h2 class="section-title">{{ __('frontend.blog_preview_title') }}</h2>
        <div class="section-divider"></div>
        <p class="section-desc">{{ __('frontend.blog_preview_desc') }}</p>
      </div>

      <div class="blog-preview-grid">
        @foreach($latestArticles as $article)
          <a href="{{ route('blog.show', $article->slug) }}" class="blog-preview-card animate-on-scroll">
            <div class="blog-preview-image">
              @if($article->featured_image)
                <img src="{{ $article->featured_image }}" alt="{{ $article->title }}">
              @else
                <div class="blog-preview-placeholder">
                  <svg viewBox="0 0 24 24" width="40" height="40">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" fill="none" stroke="currentColor" stroke-width="1"/>
                    <polyline points="14 2 14 8 20 8" fill="none" stroke="currentColor" stroke-width="1"/>
                    <line x1="16" y1="13" x2="8" y2="13" fill="none" stroke="currentColor" stroke-width="1"/>
                    <line x1="16" y1="17" x2="8" y2="17" fill="none" stroke="currentColor" stroke-width="1"/>
                  </svg>
                </div>
              @endif
            </div>
            <div class="blog-preview-body">
              @if($article->category)
                <span class="blog-preview-category">{{ $article->category->name }}</span>
              @endif
              <h3 class="blog-preview-title">{{ $article->title }}</h3>
              <p class="blog-preview-excerpt">{{ Str::limit($article->excerpt ?? strip_tags($article->content), 80) }}</p>
              <div class="blog-preview-meta">
                <span>{{ $article->published_at->format('Y.m.d') }}</span>
                <span class="blog-preview-readmore">{{ __('frontend.blog_read_more') }} &rarr;</span>
              </div>
            </div>
          </a>
        @endforeach
      </div>

      <div style="text-align:center;margin-top:48px;" class="animate-on-scroll">
        <a href="{{ route('blog') }}" class="btn-secondary">{{ __('frontend.blog_view_all') }}</a>
      </div>
    </section>
    @endif

    {{-- ===== SOCIAL EMBED ===== --}}
    @if(setting('social_embed_enabled', false))
    <section id="social-embed" class="social-embed">
      <div class="section-header animate-on-scroll">
        <div class="section-label">SOCIAL</div>
        <h2 class="section-title">Follow Us</h2>
        <div class="section-divider"></div>
        <p class="section-desc">關注我們的社群媒體，掌握最新動態與作品分享</p>
      </div>

      <div class="social-embed-grid">
        @if(setting('social_youtube_embed'))
          @php
              $youtubeUrl = setting('social_youtube_embed');
              $youtubeId = '';
              if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $youtubeUrl, $matches)) {
                  $youtubeId = $matches[1];
              }
          @endphp
          @if($youtubeId)
          <div class="social-embed-item animate-on-scroll">
            <div class="social-embed-label">
              <svg viewBox="0 0 24 24" width="18" height="18"><path d="M22.54 6.42a2.78 2.78 0 00-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 2A29 29 0 001 11.75a29 29 0 00.46 5.33A2.78 2.78 0 003.4 19.1c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 001.94-2 29 29 0 00.46-5.25 29 29 0 00-.46-5.43z" fill="none" stroke="currentColor" stroke-width="1.5"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
              YouTube
            </div>
            <div class="social-embed-frame">
              <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}" title="YouTube video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
            </div>
          </div>
          @endif
        @endif

        @if(setting('social_instagram_embed'))
          <div class="social-embed-item animate-on-scroll">
            <div class="social-embed-label">
              <svg viewBox="0 0 24 24" width="18" height="18"><rect x="2" y="2" width="20" height="20" rx="5" ry="5" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z" fill="none" stroke="currentColor" stroke-width="1.5"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke="currentColor" stroke-width="1.5"/></svg>
              Instagram
            </div>
            <div class="social-embed-frame social-embed-frame--ig">
              <blockquote class="instagram-media" data-instgrm-permalink="{{ setting('social_instagram_embed') }}" data-instgrm-version="14" style="background:#000; border:0; border-radius:3px; box-shadow:none; margin:0; max-width:100%; min-width:100%; padding:0; width:100%;"></blockquote>
            </div>
          </div>
        @endif
      </div>
    </section>
    @if(setting('social_instagram_embed'))
    <script async src="//www.instagram.com/embed.js"></script>
    @endif
    @endif

    {{-- ===== CONTACT ===== --}}
    <section id="contact">
      <div class="section-header animate-on-scroll">
        <div class="section-label">{{ __('frontend.contact_label') }}</div>
        <h2 class="section-title">{{ __('frontend.contact_title') }}</h2>
        <div class="section-divider"></div>
        <p class="section-desc">{{ __('frontend.contact_desc') }}</p>
      </div>

      <div class="contact-wrapper">
        <div class="contact-info animate-on-scroll">
          <h3>{{ __('frontend.contact_start_project') }}</h3>
          <p>{{ __('frontend.contact_start_project_desc') }}</p>
          <div class="contact-detail">
            <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <span>{{ setting('contact_email', 'bryantchi.work@gmail.com') }}</span>
          </div>
          <div class="contact-detail">
            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span>{{ setting('contact_location', '台中市，台灣') }}</span>
          </div>
          <div class="contact-detail">
            <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
            <span>{{ __('frontend.contact_free_consult') }}</span>
          </div>
          @if(setting('social_line_enabled', '1') == '1' && setting('line_id'))
          @php
              $lineUrl = setting('social_line', '#');
              $hasLineUrl = $lineUrl && $lineUrl !== '#';
              $lineHref = $hasLineUrl ? $lineUrl : 'https://line.me/ti/p/' . urlencode(setting('line_id'));
          @endphp
          <div class="contact-detail">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 5.58 2 10c0 2.13 1.07 4.04 2.76 5.47L4 20l3.53-2.12C8.87 18.28 10.4 18.5 12 18.5c5.52 0 10-3.58 10-8S17.52 2 12 2z" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
            <a href="{{ $lineHref }}" target="_blank" rel="noopener" style="color: var(--accent-cyan); text-decoration: none;">LINE 官方帳號 {{ setting('line_id') }}</a>
          </div>
          @if(setting('line_qrcode_url'))
          <div class="contact-line-qr animate-on-scroll" style="margin-top: 20px; text-align: center;">
            <a href="{{ $lineHref }}" target="_blank" rel="noopener" aria-label="LINE QR Code">
              <img src="{{ setting('line_qrcode_url') }}" alt="LINE 官方帳號 {{ setting('line_id') }} QR Code" loading="lazy" class="line-qr-img">
            </a>
            <p class="line-qr-text">掃碼加入 LINE 諮詢</p>
          </div>
          @endif
          @endif
        </div>

        <form action="{{ route('contact.submit') }}" method="POST" class="contact-form animate-on-scroll">
          @csrf
          <p class="contact-promise">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
            免費諮詢，24 小時內回覆
          </p>
          @if(session('success'))
            <div style="padding: 12px 16px; background: rgba(0, 212, 255, 0.1); border: 1px solid var(--accent-cyan); color: var(--accent-cyan); font-size: 14px;">
              {{ session('success') }}
            </div>
          @endif
          @if($errors->any())
            <div style="padding: 12px 16px; background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.5); color: #dc3545; font-size: 13px; margin-bottom: 8px;">
              @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
              @endforeach
            </div>
          @endif
          <div class="form-group">
            <input type="text" name="name" placeholder="{{ __('frontend.contact_name_placeholder') }}" value="{{ old('name') }}" required>
          </div>
          <div class="form-group">
            <input type="email" name="email" placeholder="{{ __('frontend.contact_email_placeholder') }}" value="{{ old('email') }}" required>
          </div>
          <div class="form-group">
            <select name="project_type" required class="form-input">
                <option value="">專案類型 *</option>
                <option value="app">App 開發</option>
                <option value="web">網頁設計</option>
                <option value="system">系統架構</option>
                <option value="uiux">UI/UX 設計</option>
                <option value="other">其他</option>
            </select>
          </div>
          <div class="form-group">
            <select name="budget" class="form-input">
                <option value="">預算範圍（選填）</option>
                <option value="under_100k">10 萬以下</option>
                <option value="100k_300k">10-30 萬</option>
                <option value="300k_500k">30-50 萬</option>
                <option value="over_500k">50 萬以上</option>
                <option value="discuss">需要討論</option>
            </select>
          </div>
          <div class="form-group">
            <select name="timeline" class="form-input">
                <option value="">期望時程（選填）</option>
                <option value="1month">1 個月內</option>
                <option value="1_3months">1-3 個月</option>
                <option value="3_6months">3-6 個月</option>
                <option value="flexible">彈性</option>
            </select>
          </div>
          <div class="form-group">
            <input type="text" name="line_id" placeholder="LINE ID（選填，方便即時聯繫）" value="{{ old('line_id') }}">
          </div>
          <div class="form-group">
            <textarea name="message" placeholder="{{ __('frontend.contact_message_placeholder') }}">{{ old('message') }}</textarea>
          </div>
          <button type="submit" class="btn-submit">{{ __('frontend.contact_submit') }}</button>
        </form>
      </div>
    </section>

    {{-- ===== NEWSLETTER ===== --}}
    <section id="newsletter" class="newsletter-section">
      <div class="newsletter-wrapper animate-on-scroll">
        <div class="newsletter-content">
          <div class="section-label">{{ __('frontend.newsletter_label') }}</div>
          <h3 class="newsletter-title">{{ __('frontend.newsletter_title') }}</h3>
          <p class="newsletter-desc">{{ __('frontend.newsletter_desc') }}</p>
        </div>
        <form class="newsletter-form" id="newsletterForm" action="{{ route('subscribe') }}" method="POST">
          @csrf
          <div class="newsletter-input-group">
            <input type="email" name="email" placeholder="{{ __('frontend.newsletter_placeholder') }}" required>
            <button type="submit" class="btn-primary newsletter-btn">{{ __('frontend.newsletter_btn') }}</button>
          </div>
          <div class="newsletter-message" id="newsletterMessage" style="display:none;"></div>
          @if(session('subscribe_success'))
            <div class="newsletter-message" style="color: var(--accent-cyan); font-size: 14px; margin-top: 8px;">
              {{ session('subscribe_success') }}
            </div>
          @endif
        </form>
      </div>
    </section>

    @include('frontend.partials.footer')
@endsection
