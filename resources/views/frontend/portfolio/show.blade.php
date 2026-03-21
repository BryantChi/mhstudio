@extends('frontend.layouts.app')

@section('title', $project->title . ' | MH Studio 孟衡')
@if($project->exclude_from_search || $project->visibility !== 'public' || ($isSharedView ?? false))
@section('meta_robots', 'noindex, nofollow')
@endif

@php
    $galleryImages = $project->images ?? collect();
    $hasGallery = $galleryImages->isNotEmpty();
    $coverUrl = $project->cover_image;
@endphp

@section('content')
    @include('frontend.partials.navigation')

    {{-- ===== PROJECT HEADER ===== --}}
    <section class="page-header">
      <div class="page-header-bg-grid"></div>
      <div class="page-header-orb"></div>
      <div class="page-header-content">
        <a href="{{ route('portfolio') }}" class="article-back-link">
          <svg viewBox="0 0 24 24" width="16" height="16">
            <line x1="19" y1="12" x2="5" y2="12" fill="none" stroke="currentColor" stroke-width="1.5"/>
            <polyline points="12 19 5 12 12 5" fill="none" stroke="currentColor" stroke-width="1.5"/>
          </svg>
          返回作品集
        </a>
        @if($project->category)
          <span class="article-category-badge">{{ $project->category }}</span>
        @endif
        <h1 class="page-header-title">{{ $project->title }}</h1>
        <div class="section-divider"></div>
      </div>
    </section>

    {{-- ===== PROJECT DETAIL ===== --}}
    <section class="project-detail-section">
      <div class="project-detail-wrapper">
        {{-- Main Content --}}
        <div class="project-detail-content">
          @if($hasGallery)
            {{-- 大圖：第一張圖片（Fancybox） --}}
            <div class="project-cover-image">
              <a href="{{ $galleryImages->first()->image_url }}"
                 data-fancybox="project-gallery"
                 data-caption="{{ $galleryImages->first()->caption ?? '' }}">
                <img src="{{ $galleryImages->first()->image_url }}"
                     alt="{{ $galleryImages->first()->alt_text ?? $project->title }}"
                     class="project-gallery-main-img">
              </a>
              @if($galleryImages->first()->caption)
                <div class="project-gallery-main-caption">{{ $galleryImages->first()->caption }}</div>
              @endif
            </div>

            {{-- 縮圖 Grid（第 2 張起） --}}
            @if($galleryImages->count() > 1)
            <div class="project-gallery-grid">
              @foreach($galleryImages->slice(1) as $image)
                <a href="{{ $image->image_url }}"
                   data-fancybox="project-gallery"
                   data-caption="{{ $image->caption ?? '' }}"
                   class="project-gallery-thumb">
                  <img src="{{ $image->image_url }}"
                       alt="{{ $image->alt_text ?? $project->title }}"
                       loading="lazy">
                  @if($image->caption)
                    <div class="project-gallery-caption">{{ $image->caption }}</div>
                  @endif
                </a>
              @endforeach
            </div>
            @endif
          @elseif($coverUrl)
            {{-- 單張封面（也可點擊放大） --}}
            <div class="project-cover-image">
              <a href="{{ $coverUrl }}"
                 data-fancybox="project-gallery"
                 data-caption="{{ $project->title }}">
                <img src="{{ $coverUrl }}"
                     alt="{{ $project->title }}"
                     class="project-gallery-main-img">
              </a>
            </div>
          @endif

          <div class="article-body prose">
            {!! $project->content !!}
          </div>
        </div>

        {{-- Sidebar --}}
        <aside class="project-sidebar">
          <div class="project-sidebar-card">
            <h3 class="project-sidebar-title">專案資訊</h3>

            @if($project->client)
              <div class="project-sidebar-item">
                <div class="project-sidebar-label">客戶</div>
                <div class="project-sidebar-value">{{ $project->client }}</div>
              </div>
            @endif

            @if($project->category)
              <div class="project-sidebar-item">
                <div class="project-sidebar-label">類別</div>
                <div class="project-sidebar-value">{{ $project->category }}</div>
              </div>
            @endif

            @if($project->completed_at)
              <div class="project-sidebar-item">
                <div class="project-sidebar-label">完成日期</div>
                <div class="project-sidebar-value">{{ $project->completed_at->format('Y 年 m 月') }}</div>
              </div>
            @endif

            @if($project->tech_stack)
              <div class="project-sidebar-item">
                <div class="project-sidebar-label">技術棧</div>
                <div class="project-sidebar-techs">
                  @foreach($project->tech_stack as $tech)
                    <span class="project-tech-badge">{{ $tech }}</span>
                  @endforeach
                </div>
              </div>
            @endif

            @if($project->results)
            <div class="project-results">
                <h4>專案成果</h4>
                <p>{{ $project->results }}</p>
            </div>
            @endif

            @if($project->url || $project->github_url)
              <div class="project-sidebar-links">
                @if($project->url)
                  <a href="{{ $project->url }}" target="_blank" rel="noopener" class="btn-primary" style="width:100%;text-align:center;display:block;">
                    <svg viewBox="0 0 24 24" width="14" height="14" style="display:inline;vertical-align:middle;margin-right:6px;">
                      <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" fill="none" stroke="currentColor" stroke-width="1.5"/>
                      <polyline points="15 3 21 3 21 9" fill="none" stroke="currentColor" stroke-width="1.5"/>
                      <line x1="10" y1="14" x2="21" y2="3" fill="none" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    瀏覽網站
                  </a>
                @endif
                @if($project->github_url)
                  <a href="{{ $project->github_url }}" target="_blank" rel="noopener" class="btn-secondary" style="width:100%;text-align:center;display:block;">
                    <svg viewBox="0 0 24 24" width="14" height="14" style="display:inline;vertical-align:middle;margin-right:6px;">
                      <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22" fill="none" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    GitHub
                  </a>
                @endif
              </div>
            @endif
          </div>
        </aside>
      </div>
    </section>

    @if(isset($relatedProjects) && $relatedProjects->count() > 0)
    <section class="related-projects-section">
        <div class="related-projects-wrapper">
            <div class="related-projects-header">
                <div class="section-label">MORE WORK</div>
                <h3>相關作品</h3>
            </div>
            <div class="related-projects-grid">
                @foreach($relatedProjects as $relatedProject)
                <a href="{{ route('portfolio.show', $relatedProject->slug) }}" class="related-project-card">
                    <div class="related-project-thumb">
                        @if($relatedProject->cover_image)
                          <img src="{{ $relatedProject->cover_image }}" alt="{{ $relatedProject->title }}" loading="lazy">
                        @else
                          <div class="related-project-placeholder">
                            <span>{{ strtoupper(Str::limit($relatedProject->title, 2, '')) }}</span>
                          </div>
                        @endif
                    </div>
                    <div class="related-project-info">
                        @if($relatedProject->category)
                          <span class="related-project-category">{{ $relatedProject->category }}</span>
                        @endif
                        <h4 class="related-project-title">{{ $relatedProject->title }}</h4>
                        <p class="related-project-excerpt">{{ Str::limit($relatedProject->excerpt, 80) }}</p>
                        @if($relatedProject->tech_stack)
                          <div class="related-project-techs">
                            @foreach(array_slice($relatedProject->tech_stack, 0, 3) as $tech)
                              <span>{{ $tech }}</span>
                            @endforeach
                          </div>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- CTA Section --}}
    <section class="portfolio-cta-section">
        <div class="container text-center">
            <h3>想要類似的專案？</h3>
            <p>讓我們為您打造同樣出色的解決方案</p>
            @php
                $quoteCategoryMap = [
                    'app' => 'app', 'mobile' => 'app',
                    'web' => 'web', 'website' => 'web',
                    'system' => 'system', 'backend' => 'system',
                    'ai' => 'ai',
                ];
                $catKey = strtolower($project->category ?? '');
                $quoteSlug = $quoteCategoryMap[$catKey] ?? null;
            @endphp
            <a href="{{ route('quote') }}{{ $quoteSlug ? '?category='.$quoteSlug : '' }}" class="cta-button">免費取得報價</a>
        </div>
    </section>

    @include('frontend.partials.footer')
@endsection
