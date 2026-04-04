@extends('frontend.layouts.app')

@php
    $seo = $article->seoMeta;
    $seoTitle = $seo->meta_title ?? $article->meta_title ?? $article->title . ' | MH Studio 孟衡';
    $seoDescription = $seo->meta_description ?? $article->meta_description ?? $article->excerpt ?? '';
    $seoKeywords = $seo->meta_keywords ?? $article->meta_keywords ?? '';
    $seoImage = $seo->og_image ?? $article->featured_image ?? '';
@endphp

@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('meta_keywords', $seoKeywords)
@section('og_type', 'article')
@section('og_title', $seo->og_title ?? $article->title)
@section('og_description', $seo->og_description ?? $seoDescription)
@if($seoImage)
    @section('og_image', $seoImage)
@endif
@if($seo && $seo->canonical_url)
    @section('canonical', $seo->canonical_url)
@endif

@section('content')
    @include('frontend.partials.navigation')

    {{-- ===== ARTICLE HEADER ===== --}}
    <section class="article-header">
      <div class="page-header-bg-grid"></div>
      <div class="page-header-orb"></div>
      <div class="article-header-content">
        <a href="{{ route('blog') }}" class="article-back-link">
          <svg viewBox="0 0 24 24" width="16" height="16">
            <line x1="19" y1="12" x2="5" y2="12" fill="none" stroke="currentColor" stroke-width="1.5"/>
            <polyline points="12 19 5 12 12 5" fill="none" stroke="currentColor" stroke-width="1.5"/>
          </svg>
          返回部落格
        </a>
        @if($article->category)
          <span class="article-category-badge">{{ $article->category->name }}</span>
        @endif
        <h1 class="article-title">{{ $article->title }}</h1>
        <div class="article-meta">
          @if($article->author)
            <div class="article-meta-item">
              <svg viewBox="0 0 24 24" width="16" height="16">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" fill="none" stroke="currentColor" stroke-width="1.5"/>
                <circle cx="12" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="1.5"/>
              </svg>
              {{ $article->display_author_name }}
            </div>
          @endif
          <div class="article-meta-item">
            <svg viewBox="0 0 24 24" width="16" height="16">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5"/>
              <line x1="16" y1="2" x2="16" y2="6" fill="none" stroke="currentColor" stroke-width="1.5"/>
              <line x1="8" y1="2" x2="8" y2="6" fill="none" stroke="currentColor" stroke-width="1.5"/>
              <line x1="3" y1="10" x2="21" y2="10" fill="none" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            {{ $article->published_at->format('Y 年 m 月 d 日') }}
          </div>
          <div class="article-meta-item">
            <svg viewBox="0 0 24 24" width="16" height="16">
              <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.5"/>
              <polyline points="12 6 12 12 16 14" fill="none" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            @php
              $wordCount = mb_strlen(strip_tags($article->content));
              $readingTime = max(1, ceil($wordCount / 500));
            @endphp
            {{ $readingTime }} 分鐘閱讀
          </div>
          <div class="article-meta-item">
            <svg viewBox="0 0 24 24" width="16" height="16">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" fill="none" stroke="currentColor" stroke-width="1.5"/>
              <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            {{ number_format($article->views_count) }} 次瀏覽
          </div>
        </div>
      </div>
    </section>

    {{-- ===== ARTICLE CONTENT ===== --}}
    <section class="article-section">
      <div class="article-wrapper">
        <article class="article-content">
          @if($article->featured_image)
            <div class="article-featured-image">
              <img src="{{ $article->featured_image }}" alt="{{ $article->title }}">
            </div>
          @endif

          <div class="article-body prose">
            {!! $article->content !!}
          </div>

          {{-- Tags --}}
          @if($article->tags->isNotEmpty())
            <div class="article-tags">
              <svg viewBox="0 0 24 24" width="16" height="16">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" fill="none" stroke="currentColor" stroke-width="1.5"/>
                <line x1="7" y1="7" x2="7.01" y2="7" fill="none" stroke="currentColor" stroke-width="1.5"/>
              </svg>
              @foreach($article->tags as $tag)
                <a href="{{ route('blog', ['tag' => $tag->slug]) }}" class="article-tag">{{ $tag->name }}</a>
              @endforeach
            </div>
          @endif

          {{-- Share --}}
          <div class="article-share">
            <span class="article-share-label">分享文章</span>
            <div class="article-share-links">
              <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" rel="noopener" class="article-share-btn" aria-label="Share on Facebook">
                <svg viewBox="0 0 24 24" width="18" height="18"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
              </a>
              <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($article->title) }}" target="_blank" rel="noopener" class="article-share-btn" aria-label="Share on Twitter">
                <svg viewBox="0 0 24 24" width="18" height="18"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
              </a>
              <button class="article-share-btn" aria-label="Copy link" onclick="navigator.clipboard.writeText(window.location.href);this.innerHTML='<svg viewBox=\'0 0 24 24\' width=\'18\' height=\'18\'><polyline points=\'20 6 9 17 4 12\' fill=\'none\' stroke=\'var(--accent-cyan)\' stroke-width=\'2\'/></svg>';setTimeout(()=>{this.innerHTML='<svg viewBox=\'0 0 24 24\' width=\'18\' height=\'18\'><path d=\'M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\'/><path d=\'M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\'/></svg>'},2000)">
                <svg viewBox="0 0 24 24" width="18" height="18"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
              </button>
            </div>
          </div>
        </article>
      </div>
    </section>

    {{-- 作者資訊 --}}
    <section class="article-section">
      <div class="article-wrapper">
        <div class="author-card">
            <div class="author-info">
                <div class="author-avatar">{{ substr($article->display_author_name, 0, 1) }}</div>
                <div>
                    <strong>{{ $article->display_author_name }}</strong>
                    <p>專注於 Web 與 App 開發的技術團隊</p>
                </div>
            </div>
        </div>

        {{-- 服務導流 CTA --}}
        <div class="blog-cta-banner">
            <h4>需要專業的技術服務？</h4>
            <p>我們提供從設計到開發的一站式解決方案</p>
            <div class="blog-cta-buttons">
                <a href="/#services" class="cta-button">了解服務</a>
                <a href="/quote" class="cta-button cta-button-outline">索取報價</a>
            </div>
        </div>
      </div>
    </section>

    {{-- ===== RELATED ARTICLES ===== --}}
    @if($relatedArticles->isNotEmpty())
    <section class="related-articles">
      <div class="section-header animate-on-scroll">
        <div class="section-label">RELATED</div>
        <h2 class="section-title">相關文章</h2>
        <div class="section-divider"></div>
      </div>

      <div class="blog-grid" style="max-width:1200px;margin:0 auto;">
        @foreach($relatedArticles as $related)
          <a href="{{ route('blog.show', $related->slug) }}" class="blog-card animate-on-scroll">
            <div class="blog-card-image">
              @if($related->featured_image)
                <img src="{{ $related->featured_image }}" alt="{{ $related->title }}" loading="lazy">
              @else
                <div class="blog-card-image-placeholder">
                  <svg viewBox="0 0 24 24" width="48" height="48">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" fill="none" stroke="currentColor" stroke-width="1"/>
                    <polyline points="14 2 14 8 20 8" fill="none" stroke="currentColor" stroke-width="1"/>
                  </svg>
                </div>
              @endif
              @if($related->category)
                <span class="blog-card-badge">{{ $related->category->name }}</span>
              @endif
            </div>
            <div class="blog-card-body">
              <h3 class="blog-card-title">{{ $related->title }}</h3>
              <p class="blog-card-excerpt">{{ Str::limit($related->excerpt ?? strip_tags($related->content), 80) }}</p>
              <div class="blog-card-footer">
                <span class="blog-card-date">{{ $related->published_at->format('Y.m.d') }}</span>
                <span class="blog-card-readmore">閱讀更多 &rarr;</span>
              </div>
            </div>
          </a>
        @endforeach
      </div>
    </section>
    @endif

    @include('frontend.partials.footer')

@push('styles')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "{{ $article->title }}",
    "author": {
        "@type": "Person",
        "name": "{{ $article->display_author_name }}"
    },
    "datePublished": "{{ $article->created_at->toISOString() }}",
    "dateModified": "{{ $article->updated_at->toISOString() }}",
    "publisher": {
        "@type": "Organization",
        "name": "MH Studio"
    }
    @if($article->featured_image)
    ,"image": "{{ $article->featured_image }}"
    @endif
}
</script>
@endpush
@endsection
