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
      @if($projects->isNotEmpty())
        @php
          $projectCategories = $projects->pluck('category')->filter()->unique()->values();
        @endphp

        {{-- Category filters --}}
        @if($projectCategories->isNotEmpty())
          <div class="portfolio-filters animate-on-scroll">
            <button class="category-pill active" data-filter="all">全部</button>
            @foreach($projectCategories as $cat)
              <button class="category-pill" data-filter="{{ $cat }}">{{ $cat }}</button>
            @endforeach
          </div>
        @endif

        <div class="portfolio-listing-grid">
          @foreach($projects as $project)
            <a href="{{ route('portfolio.show', $project->slug) }}" class="portfolio-listing-card animate-on-scroll" data-category="{{ $project->category ?? 'other' }}">
              <div class="portfolio-listing-thumb">
                @if($project->cover_image)
                  <img src="{{ $project->cover_image }}" alt="{{ $project->title }}" loading="lazy">
                @else
                  <div class="portfolio-listing-placeholder">
                    <span>{{ strtoupper($project->category ?? 'PROJECT') }}</span>
                  </div>
                @endif
                <div class="portfolio-listing-overlay">
                  <span class="portfolio-overlay-btn">查看詳情</span>
                </div>
              </div>
              <div class="portfolio-listing-info">
                <h3 class="portfolio-listing-title">{{ $project->title }}</h3>
                @if($project->client)
                  <div class="portfolio-listing-client">
                    <svg viewBox="0 0 24 24" width="14" height="14">
                      <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" fill="none" stroke="currentColor" stroke-width="1.5"/>
                      <circle cx="12" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    {{ $project->client }}
                  </div>
                @endif
                <p class="portfolio-listing-excerpt">{{ Str::limit($project->excerpt, 120) }}</p>
                @if($project->results)
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
            </a>
          @endforeach
        </div>
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
