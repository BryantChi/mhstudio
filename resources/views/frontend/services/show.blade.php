@extends('frontend.layouts.app')

@section('title', $service->title . ' | MH Studio 孟衡')

@section('content')
    @include('frontend.partials.navigation')

    {{-- ===== SERVICE HEADER ===== --}}
    <section class="page-header">
      <div class="page-header-bg-grid"></div>
      <div class="page-header-orb"></div>
      <div class="page-header-content">
        <a href="{{ route('home') }}#services" class="article-back-link">
          <svg viewBox="0 0 24 24" width="16" height="16">
            <line x1="19" y1="12" x2="5" y2="12" fill="none" stroke="currentColor" stroke-width="1.5"/>
            <polyline points="12 19 5 12 12 5" fill="none" stroke="currentColor" stroke-width="1.5"/>
          </svg>
          返回服務列表
        </a>
        @if($service->icon)
          <div class="service-detail-icon">
            @include('components.render-icon', ['icon' => $service->icon, 'size' => 32])
          </div>
        @endif
        <h1 class="page-header-title">{{ $service->title }}</h1>
        <div class="section-divider"></div>
        @if($service->excerpt)
          <p class="section-desc">{{ $service->excerpt }}</p>
        @endif
      </div>
    </section>

    {{-- ===== SERVICE CONTENT ===== --}}
    <section class="service-detail-section">
      <div class="service-detail-wrapper">
        {{-- Main Description --}}
        @if($service->content)
          <div class="service-detail-content animate-on-scroll">
            <div class="article-body prose">
              {!! $service->content !!}
            </div>
          </div>
        @endif

        {{-- Features --}}
        @if($service->features && count($service->features) > 0)
          <div class="service-features animate-on-scroll">
            <div class="section-label">FEATURES</div>
            <h2 class="service-features-title">服務特色</h2>
            <div class="section-divider" style="margin-left:0;"></div>
            <div class="service-features-grid">
              @foreach($service->features as $index => $feature)
                <div class="service-feature-item">
                  <div class="service-feature-number">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</div>
                  <div class="service-feature-text">{{ $feature }}</div>
                </div>
              @endforeach
            </div>
          </div>
        @endif

        {{-- Tech Tags --}}
        @if($service->tech_tags && count($service->tech_tags) > 0)
          <div class="service-tech-section animate-on-scroll">
            <div class="section-label">TECH STACK</div>
            <h2 class="service-features-title">使用技術</h2>
            <div class="section-divider" style="margin-left:0;"></div>
            <div class="service-tech-tags">
              @foreach($service->tech_tags as $tag)
                <span class="service-tech-tag">{{ $tag }}</span>
              @endforeach
            </div>
          </div>
        @endif

        {{-- 方案定價 —— 顯示具體方案內容 --}}
        @if($service->price > 0 || $service->price_label)
          <div class="service-price-section animate-on-scroll">
            <div class="service-price-card">
              <div class="service-price-icon">
                <svg viewBox="0 0 24 24" width="28" height="28">
                  <line x1="12" y1="1" x2="12" y2="23" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5"/>
                  <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5"/>
                </svg>
              </div>
              <div class="service-price-info">
                <div class="service-price-label">{{ $service->title }} 方案</div>
                <div class="service-price-value">{{ $service->formatted_price }}</div>
                @if($service->work_days_label)
                <div class="service-price-note">預估工期：{{ $service->work_days_label }} · {{ $service->warranty_months ? $service->warranty_months . ' 個月保固' : '' }}</div>
                @endif
              </div>
            </div>

            {{-- 方案包含項目 --}}
            @if($service->items->isNotEmpty())
            <div style="margin-top: 1.5rem;">
              <div class="section-label">INCLUDED</div>
              <h3 style="font-family:var(--font-display);font-size:16px;letter-spacing:1px;margin-bottom:1rem;color:var(--text-primary);">方案包含項目</h3>
              <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:8px;">
                @foreach($service->items as $item)
                <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-secondary);padding:6px 0;">
                  <svg viewBox="0 0 24 24" width="14" height="14" style="stroke:var(--accent-cyan);fill:none;flex-shrink:0;"><polyline points="20 6 9 17 4 12" stroke-width="2"/></svg>
                  <span style="{{ $item->type === 'highlighted' ? 'color:var(--text-primary);font-weight:600;' : '' }}">{{ $item->name }}</span>
                </div>
                @endforeach
              </div>
            </div>
            @endif
          </div>
        @else
          {{-- Fallback: 定價分類或手動價格 --}}
          @php
              $displayPrice = $service->price_range;
              if (!$displayPrice && $service->pricingCategory) {
                  $displayPrice = 'NT$ ' . number_format($service->pricingCategory->base_price_min)
                                . ' ~ NT$ ' . number_format($service->pricingCategory->base_price_max);
              }
          @endphp
          @if($displayPrice)
            <div class="service-price-section animate-on-scroll">
              <div class="service-price-card">
                <div class="service-price-icon">
                  <svg viewBox="0 0 24 24" width="28" height="28">
                    <line x1="12" y1="1" x2="12" y2="23" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5"/>
                    <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5"/>
                  </svg>
                </div>
                <div class="service-price-info">
                  <div class="service-price-label">預估價格範圍</div>
                  <div class="service-price-value">{{ $displayPrice }}</div>
                  <div class="service-price-note">實際報價依專案需求而定，歡迎諮詢</div>
                </div>
              </div>
            </div>
          @endif
        @endif

        {{-- FAQ --}}
        @if($service->faq && count($service->faq) > 0)
          <div class="service-faq-section animate-on-scroll">
            <div class="section-label">FAQ</div>
            <h2 class="service-features-title">常見問題</h2>
            <div class="section-divider" style="margin-left:0;"></div>
            <div class="faq-accordion" id="faqAccordion">
              @foreach($service->faq as $index => $item)
                <div class="faq-item">
                  <button class="faq-question" data-faq-index="{{ $index }}">
                    <span>{{ $item['q'] ?? $item['question'] ?? '' }}</span>
                    <svg class="faq-chevron" viewBox="0 0 24 24" width="20" height="20">
                      <polyline points="6 9 12 15 18 9" fill="none" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                  </button>
                  <div class="faq-answer" id="faqAnswer{{ $index }}">
                    <p>{{ $item['a'] ?? $item['answer'] ?? '' }}</p>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif

        {{-- CTA --}}
        <div class="service-cta animate-on-scroll">
          <div class="about-cta-wrapper">
            <h2 class="about-cta-title">對此服務有興趣？</h2>
            <p class="about-cta-desc">讓我們了解您的需求，為您量身打造最適合的解決方案。</p>
            <div class="hero-actions" style="animation:none;opacity:1;">
              @if($service->pricingCategory)
                <a href="{{ route('quote') }}?category={{ $service->pricingCategory->slug }}" class="btn-primary">免費取得報價</a>
              @else
                <a href="{{ route('quote') }}" class="btn-primary">免費取得報價</a>
              @endif
              <a href="{{ route('portfolio') }}" class="btn-secondary">瀏覽相關作品</a>
            </div>
            <p class="cta-promise">首次諮詢免費 · 24 小時內回覆</p>
          </div>
        </div>
      </div>
    </section>

    @include('frontend.partials.footer')
@endsection
