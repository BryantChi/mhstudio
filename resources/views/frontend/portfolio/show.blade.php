@extends('frontend.layouts.app')

@section('title', $project->title . ' | MH Studio 孟衡')

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
          @php
              $galleryImages = $project->images ?? collect();
              $hasGallery = $galleryImages->isNotEmpty();
              $coverUrl = $project->cover_image;
          @endphp

          @if($hasGallery)
            {{-- 大圖：第一張圖片 --}}
            <div class="project-cover-image">
              <img src="{{ $galleryImages->first()->image_url }}"
                   alt="{{ $galleryImages->first()->alt_text ?? $project->title }}"
                   class="project-gallery-main-img"
                   data-gallery-index="0"
                   style="cursor: pointer;">
              @if($galleryImages->first()->caption)
                <div class="project-gallery-main-caption">{{ $galleryImages->first()->caption }}</div>
              @endif
            </div>

            {{-- 縮圖 Grid（第 2 張起） --}}
            @if($galleryImages->count() > 1)
            <div class="project-gallery-grid">
              @foreach($galleryImages->slice(1) as $idx => $image)
                <div class="project-gallery-thumb" data-gallery-index="{{ $idx + 1 }}">
                  <img src="{{ $image->image_url }}"
                       alt="{{ $image->alt_text ?? $project->title }}"
                       loading="lazy">
                  @if($image->caption)
                    <div class="project-gallery-caption">{{ $image->caption }}</div>
                  @endif
                </div>
              @endforeach
            </div>
            @endif
          @elseif($coverUrl)
            <div class="project-cover-image">
              <img src="{{ $coverUrl }}" alt="{{ $project->title }}">
            </div>
          @endif

          <div class="article-body prose">
            {!! $project->content !!}
          </div>
        </div>

        {{-- Lightbox Overlay --}}
        @if($hasGallery)
        @php
            $lightboxData = $galleryImages->map(fn($img) => [
                'url' => $img->image_url,
                'alt' => $img->alt_text ?? $project->title,
                'caption' => $img->caption ?? '',
            ])->values()->all();
        @endphp
        <div class="project-lightbox" id="projectLightbox">
          <button class="project-lightbox-close" aria-label="關閉">&times;</button>
          <button class="project-lightbox-prev" aria-label="上一張">&#10094;</button>
          <button class="project-lightbox-next" aria-label="下一張">&#10095;</button>
          <div class="project-lightbox-img-wrapper">
            <img src="" alt="" id="lightboxImg">
          </div>
          <div class="project-lightbox-info">
            <div class="project-lightbox-caption" id="lightboxCaption"></div>
            <div class="project-lightbox-counter" id="lightboxCounter"></div>
          </div>
        </div>
        @endif

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
    <section class="related-projects">
        <div class="container">
            <h3>相關作品</h3>
            <div class="portfolio-grid">
                @foreach($relatedProjects as $relatedProject)
                <a href="{{ route('portfolio.show', $relatedProject->slug) }}" class="portfolio-card">
                    @if($relatedProject->cover_image)
                    <div class="portfolio-image">
                        <img src="{{ $relatedProject->cover_image }}" alt="{{ $relatedProject->title }}" loading="lazy">
                    </div>
                    @endif
                    <div class="portfolio-info">
                        <h4>{{ $relatedProject->title }}</h4>
                        <p>{{ Str::limit($relatedProject->excerpt, 80) }}</p>
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

    @if(isset($hasGallery) && $hasGallery)
    @push('scripts')
    <script>
    (function() {
        const images = @json($lightboxData);
        if (!images || images.length === 0) return;

        const lightbox = document.getElementById('projectLightbox');
        const lightboxImg = document.getElementById('lightboxImg');
        const lightboxCaption = document.getElementById('lightboxCaption');
        const lightboxCounter = document.getElementById('lightboxCounter');
        let currentIndex = 0;

        function openLightbox(index) {
            currentIndex = index;
            updateLightbox();
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }

        function updateLightbox() {
            const img = images[currentIndex];
            lightboxImg.src = img.url;
            lightboxImg.alt = img.alt;
            lightboxCaption.textContent = img.caption || '';
            lightboxCounter.textContent = (currentIndex + 1) + ' / ' + images.length;
        }

        function prevImage() {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            updateLightbox();
        }

        function nextImage() {
            currentIndex = (currentIndex + 1) % images.length;
            updateLightbox();
        }

        // Click handlers for gallery images
        document.querySelectorAll('[data-gallery-index]').forEach(function(el) {
            el.addEventListener('click', function() {
                openLightbox(parseInt(this.dataset.galleryIndex));
            });
        });

        // Lightbox controls
        lightbox.querySelector('.project-lightbox-close').addEventListener('click', closeLightbox);
        lightbox.querySelector('.project-lightbox-prev').addEventListener('click', prevImage);
        lightbox.querySelector('.project-lightbox-next').addEventListener('click', nextImage);

        // Close on backdrop click
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox || e.target.classList.contains('project-lightbox-img-wrapper')) {
                closeLightbox();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (!lightbox.classList.contains('active')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') prevImage();
            if (e.key === 'ArrowRight') nextImage();
        });
    })();
    </script>
    @endpush
    @endif
@endsection
