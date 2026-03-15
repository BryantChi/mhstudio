@extends('frontend.layouts.app')

@section('title', __('frontend.blog_page_title'))

@section('content')
    @include('frontend.partials.navigation')

    {{-- ===== PAGE HEADER ===== --}}
    <section class="page-header">
      <div class="page-header-bg-grid"></div>
      <div class="page-header-orb"></div>
      <div class="page-header-content">
        <div class="section-label">{{ __('frontend.blog_label') }}</div>
        <h1 class="page-header-title">{{ __('frontend.blog_title') }}</h1>
        <div class="section-divider"></div>
        <p class="section-desc">{{ __('frontend.blog_desc') }}</p>
      </div>
    </section>

    {{-- ===== BLOG CONTENT ===== --}}
    <section class="blog-section">
      {{-- Search & Filter --}}
      <div class="blog-filters animate-on-scroll">
        <form action="{{ route('blog') }}" method="GET" class="blog-search-form">
          <div class="blog-search-bar">
            <svg viewBox="0 0 24 24" class="blog-search-icon">
              <circle cx="11" cy="11" r="8" fill="none" stroke="currentColor" stroke-width="1.5"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65" fill="none" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <input type="text" name="search" placeholder="{{ __('frontend.blog_search_placeholder') }}" value="{{ request('search') }}">
            <button type="submit" class="blog-search-btn">{{ __('frontend.blog_search_btn') }}</button>
          </div>
        </form>

        <div class="blog-category-pills">
          <a href="{{ route('blog') }}" class="category-pill {{ !request('category_id') ? 'active' : '' }}">{{ __('frontend.blog_category_all') }}</a>
          @foreach($categories as $category)
            <a href="{{ route('blog', ['category_id' => $category->id]) }}" class="category-pill {{ request('category_id') == $category->id ? 'active' : '' }}">
              {{ $category->name }}
            </a>
          @endforeach
        </div>
      </div>

      {{-- Article Grid --}}
      @if($articles->isNotEmpty())
        <div class="blog-grid">
          @foreach($articles as $article)
            <a href="{{ route('blog.show', $article->slug) }}" class="blog-card animate-on-scroll">
              <div class="blog-card-image">
                @if($article->featured_image)
                  <img src="{{ $article->featured_image }}" alt="{{ $article->title }}" loading="lazy">
                @else
                  <div class="blog-card-image-placeholder">
                    <svg viewBox="0 0 24 24" width="48" height="48">
                      <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" fill="none" stroke="currentColor" stroke-width="1"/>
                      <polyline points="14 2 14 8 20 8" fill="none" stroke="currentColor" stroke-width="1"/>
                    </svg>
                  </div>
                @endif
                @if($article->category)
                  <span class="blog-card-badge">{{ $article->category->name }}</span>
                @endif
              </div>
              <div class="blog-card-body">
                <h3 class="blog-card-title">{{ $article->title }}</h3>
                <p class="blog-card-excerpt">{{ Str::limit($article->excerpt ?? strip_tags($article->content), 100) }}</p>
                <div class="blog-card-footer">
                  <span class="blog-card-date">
                    <svg viewBox="0 0 24 24" width="14" height="14">
                      <rect x="3" y="4" width="18" height="18" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5"/>
                      <line x1="16" y1="2" x2="16" y2="6" fill="none" stroke="currentColor" stroke-width="1.5"/>
                      <line x1="8" y1="2" x2="8" y2="6" fill="none" stroke="currentColor" stroke-width="1.5"/>
                      <line x1="3" y1="10" x2="21" y2="10" fill="none" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    {{ $article->published_at->format('Y.m.d') }}
                  </span>
                  <span class="blog-card-readmore">{{ __('frontend.blog_read_more') }} &rarr;</span>
                </div>
              </div>
            </a>
            @if($loop->iteration % 6 == 0)
            <div class="blog-inline-cta">
                <h4>需要技術顧問？</h4>
                <p>讓我們的專業團隊協助您的專案</p>
                <a href="/#contact" class="cta-button-small">免費諮詢</a>
            </div>
            @endif
          @endforeach
        </div>

        {{-- Pagination --}}
        <div class="blog-pagination">
          {{ $articles->links() }}
        </div>
      @else
        <div class="blog-empty animate-on-scroll">
          <svg viewBox="0 0 24 24" width="64" height="64">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" fill="none" stroke="var(--text-dim)" stroke-width="1"/>
            <polyline points="14 2 14 8 20 8" fill="none" stroke="var(--text-dim)" stroke-width="1"/>
          </svg>
          <h3>{{ __('frontend.blog_empty_title') }}</h3>
          <p>{{ __('frontend.blog_empty_desc') }}</p>
          @if(request('search') || request('category_id') || request('tag'))
            <a href="{{ route('blog') }}" class="btn-secondary" style="margin-top:20px;">{{ __('frontend.blog_clear_filter') }}</a>
          @endif
        </div>
      @endif

      {{-- Tags Cloud --}}
      @if($popularTags->isNotEmpty())
        <div class="blog-tags-section animate-on-scroll">
          <h3 class="blog-tags-title">
            <svg viewBox="0 0 24 24" width="18" height="18">
              <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" fill="none" stroke="currentColor" stroke-width="1.5"/>
              <line x1="7" y1="7" x2="7.01" y2="7" fill="none" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            {{ __('frontend.blog_popular_tags') }}
          </h3>
          <div class="blog-tags-cloud">
            @foreach($popularTags as $tag)
              <a href="{{ route('blog', ['tag' => $tag->slug]) }}" class="blog-tag {{ request('tag') == $tag->slug ? 'active' : '' }}">
                {{ $tag->name }}
                @if($tag->count > 0)
                  <span class="blog-tag-count">{{ $tag->count }}</span>
                @endif
              </a>
            @endforeach
          </div>
        </div>
      @endif
    </section>

    {{-- Bottom CTA --}}
    <section class="portfolio-cta-section">
        <div class="container text-center">
            <h3>需要技術顧問？</h3>
            <p>讓我們的專業團隊協助您的專案</p>
            <a href="/#contact" class="cta-button">免費諮詢</a>
        </div>
    </section>

    @include('frontend.partials.footer')
@endsection
