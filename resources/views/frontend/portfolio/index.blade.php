@extends('frontend.layouts.app')

@section('title', '所有作品 | MH Studio 孟衡')

@section('content')
    @include('frontend.partials.navigation')

    {{-- ===== PAGE HEADER ===== --}}
    <section class="page-header">
      <div class="page-header-bg-grid"></div>
      <div class="page-header-orb"></div>
      <div class="page-header-content">
        <div class="section-label">OUR WORK</div>
        <h1 class="page-header-title">所有作品</h1>
        <div class="section-divider"></div>
        <p class="section-desc">每一個作品都是技術與設計的完美結合</p>
      </div>
    </section>

    {{-- ===== PORTFOLIO LISTING ===== --}}
    <section class="portfolio-section">
      {{-- Category filters（後端篩選） --}}
      @if($categories->isNotEmpty())
        <div class="portfolio-filters animate-on-scroll">
          <a href="{{ route('portfolio') }}" class="category-pill {{ !request('category') ? 'active' : '' }}">全部</a>
          @foreach($categories as $cat)
            <a href="{{ route('portfolio', ['category' => $cat]) }}" class="category-pill {{ request('category') == $cat ? 'active' : '' }}">{{ $cat }}</a>
          @endforeach
        </div>
      @endif

      @if($projects->isNotEmpty())
        <div class="portfolio-listing-grid">
          @foreach($projects as $project)
            @php
              $isShowcase = $project->visibility === 'showcase';
              $displayMode = $project->display_mode ?? 'normal';
              $isConfidential = $displayMode !== 'normal';
              $abstractColor = $project->abstract_color ?? '#00d4ff';
            @endphp
            <{{ $isShowcase ? 'div' : 'a' }}
              {!! $isShowcase ? '' : 'href="' . route('portfolio.show', $project->slug) . '"' !!}
              class="portfolio-listing-card animate-on-scroll {{ $isShowcase ? 'portfolio-showcase-only' : '' }}">
              <div class="portfolio-listing-thumb {{ $displayMode === 'blurred' ? 'portfolio-thumb--blurred' : '' }} {{ $displayMode === 'abstract' ? 'portfolio-thumb--abstract' : '' }}"
                @if($displayMode === 'abstract')
                  style="--abstract-color: {{ $abstractColor }}; --abstract-gradient: linear-gradient(135deg, {{ $abstractColor }}33, {{ $abstractColor }}11, var(--bg-card));"
                @endif
              >
                @if($displayMode === 'abstract')
                  {{-- Abstract mode: gradient background only, no image --}}
                  <div class="portfolio-abstract-icon">
                    <svg viewBox="0 0 24 24" width="32" height="32"><rect x="3" y="3" width="7" height="7" rx="1" fill="currentColor" opacity="0.3"/><rect x="14" y="3" width="7" height="7" rx="1" fill="currentColor" opacity="0.2"/><rect x="3" y="14" width="7" height="7" rx="1" fill="currentColor" opacity="0.2"/><rect x="14" y="14" width="7" height="7" rx="1" fill="currentColor" opacity="0.3"/></svg>
                  </div>
                @elseif($project->cover_image)
                  <img src="{{ $project->cover_image }}" alt="{{ $project->title }}" loading="lazy">
                @else
                  <div class="portfolio-listing-placeholder">
                    <span>{{ strtoupper($project->category ?? 'PROJECT') }}</span>
                  </div>
                @endif

                @if($isConfidential)
                  <div class="portfolio-confidential-overlay">
                    <span class="confidential-badge">
                      <svg viewBox="0 0 24 24" width="14" height="14"><rect x="3" y="11" width="18" height="11" rx="2" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M7 11V7a5 5 0 0110 0v4" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                      {{ $project->confidential_label_text }}
                    </span>
                  </div>
                @endif

                @if($project->is_featured && !$isConfidential)
                  <span class="portfolio-featured-badge">
                    <svg viewBox="0 0 24 24" width="12" height="12"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="currentColor" stroke="none"/></svg>
                    精選
                  </span>
                @endif
                @unless($isShowcase)
                <div class="portfolio-listing-overlay {{ $isConfidential ? 'portfolio-listing-overlay--confidential' : '' }}">
                  <span class="portfolio-overlay-btn">查看詳情</span>
                </div>
                @endunless
              </div>
              <div class="portfolio-listing-info">
                <h3 class="portfolio-listing-title">{{ $project->title }}</h3>
                @if($project->effective_client)
                  <div class="portfolio-listing-client">
                    <svg viewBox="0 0 24 24" width="14" height="14">
                      <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" fill="none" stroke="currentColor" stroke-width="1.5"/>
                      <circle cx="12" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    {{ $project->effective_client }}
                  </div>
                @endif
                <p class="portfolio-listing-excerpt">{{ Str::limit($project->excerpt, 120) }}</p>
                @if($project->results && !$project->hide_results)
                <small class="portfolio-results">{{ $project->results }}</small>
                @endif
                @if($project->tech_stack)
                  <div class="portfolio-tech">
                    @foreach(array_slice($project->tech_stack, 0, 5) as $tech)
                      <span>{{ $tech }}</span>
                    @endforeach
                    @if(count($project->tech_stack) > 5)
                      <span>+{{ count($project->tech_stack) - 5 }}</span>
                    @endif
                  </div>
                @endif
              </div>
            </{{ $isShowcase ? 'div' : 'a' }}>
          @endforeach
        </div>

        {{-- Pagination --}}
        @if($projects->hasPages())
        <div class="fe-pagination-wrap">
          {{ $projects->links('frontend.partials.pagination') }}
        </div>
        @endif
      @else
        <div class="blog-empty animate-on-scroll">
          <svg viewBox="0 0 24 24" width="64" height="64">
            <rect x="2" y="3" width="20" height="14" rx="2" fill="none" stroke="var(--text-dim)" stroke-width="1"/>
            <line x1="8" y1="21" x2="16" y2="21" fill="none" stroke="var(--text-dim)" stroke-width="1"/>
            <line x1="12" y1="17" x2="12" y2="21" fill="none" stroke="var(--text-dim)" stroke-width="1"/>
          </svg>
          <h3>即將推出</h3>
          <p>我們正在整理精選作品，敬請期待。</p>
        </div>
      @endif
    </section>

    {{-- Bottom CTA --}}
    <section class="portfolio-cta-section">
        <div class="container text-center">
            <h3>還沒看到合適的？</h3>
            <p>每個專案都是獨一無二的，直接告訴我們您的需求</p>
            <a href="/#contact" class="cta-button">聯繫我們</a>
        </div>
    </section>

    @include('frontend.partials.footer')
@endsection
