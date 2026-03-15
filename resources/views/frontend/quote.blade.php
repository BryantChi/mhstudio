@extends('frontend.layouts.app')

@section('title', __('frontend.quote_page_title'))
@section('meta_description', __('frontend.quote_page_meta'))

@section('content')
    @include('frontend.partials.navigation')

    {{-- ===== PAGE HEADER ===== --}}
    <section class="page-header">
      <div class="page-header-bg-grid"></div>
      <div class="page-header-orb"></div>
      <div class="page-header-content">
        <div class="section-label">{{ __('frontend.quote_label') }}</div>
        <h1 class="page-header-title">{{ __('frontend.quote_title') }}</h1>
        <div class="section-divider"></div>
        <p class="section-desc">{{ __('frontend.quote_desc') }}</p>
      </div>
    </section>

    {{-- ===== SERVICE PLANS ===== --}}
    @if($servicePlans->isNotEmpty())
    <section class="pricing-plans-section" id="servicePlans">
      <div class="container">
        <div class="section-label">SERVICE PLANS</div>
        <h2 class="section-header-title">網站設計方案</h2>
        <div class="section-divider"></div>
        <p class="section-desc">選擇最適合您的方案，或使用下方計算器取得客製化報價</p>

        {{-- 方案卡片 --}}
        <div class="pricing-plans-grid">
          @foreach($servicePlans as $plan)
          <div class="pricing-plan-card {{ $plan->is_featured ? 'pricing-plan-card--featured' : '' }}">
            @if($plan->is_featured)
            <div class="pricing-plan-badge">推薦</div>
            @endif
            <div class="pricing-plan-header">
              <h3 class="pricing-plan-name">{{ $plan->title }}</h3>
              @if($plan->subtitle)
              <p class="pricing-plan-subtitle">{{ $plan->subtitle }}</p>
              @endif
            </div>
            <div class="pricing-plan-price">{{ $plan->formatted_price }}</div>
            @if($plan->billing_cycle)
            <div class="pricing-plan-cycle">{{ $plan->billing_cycle_label }}</div>
            @endif
            @if($plan->description)
            <p class="pricing-plan-desc">{{ $plan->description }}</p>
            @endif

            {{-- 方案規格 --}}
            @if($plan->type === 'website')
            <div class="pricing-plan-specs">
              @if($plan->pages_min || $plan->pages_max)
              <div class="pricing-plan-spec">
                <svg viewBox="0 0 24 24" width="14" height="14"><rect x="3" y="3" width="18" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                @if($plan->pages_min && $plan->pages_max && $plan->pages_min !== $plan->pages_max)
                {{ $plan->pages_min }}-{{ $plan->pages_max }} 頁
                @elseif($plan->pages_min)
                {{ $plan->pages_min }} 頁
                @else
                不限頁面
                @endif
              </div>
              @elseif($plan->slug === 'pro')
              <div class="pricing-plan-spec">
                <svg viewBox="0 0 24 24" width="14" height="14"><rect x="3" y="3" width="18" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                不限頁面
              </div>
              @endif
              @if($plan->warranty_months)
              <div class="pricing-plan-spec">
                <svg viewBox="0 0 24 24" width="14" height="14"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                {{ $plan->warranty_months }} 個月保固
              </div>
              @endif
              @if($plan->revisions !== null)
              <div class="pricing-plan-spec">
                <svg viewBox="0 0 24 24" width="14" height="14"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                {{ $plan->revisions }} 次修改
              </div>
              @else
              <div class="pricing-plan-spec">
                <svg viewBox="0 0 24 24" width="14" height="14"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                不限次數修改
              </div>
              @endif
              @if($plan->work_days_label)
              <div class="pricing-plan-spec">
                <svg viewBox="0 0 24 24" width="14" height="14"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.5"/><polyline points="12 6 12 12 16 14" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                {{ $plan->work_days_label }}
              </div>
              @endif
            </div>
            @endif

            {{-- Feature Checklist --}}
            @if($plan->items->isNotEmpty())
            <ul class="pricing-plan-features">
              @foreach($plan->items as $item)
              <li class="{{ $item->type === 'highlighted' ? 'highlighted' : '' }}">
                <svg viewBox="0 0 24 24" width="14" height="14"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                {{ $item->name }}
              </li>
              @endforeach
            </ul>
            @endif

            {{-- CTA --}}
            <a href="#quoteStepsContainer" class="pricing-plan-cta {{ $plan->is_featured ? 'pricing-plan-cta--featured' : '' }}" data-scroll-to-calculator>
              開始報價
              <svg viewBox="0 0 24 24" width="14" height="14"><polyline points="9 18 15 12 9 6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
            </a>
          </div>
          @endforeach
        </div>

        {{-- 額外費用：主機代管 + 維護服務 --}}
        @if($hostingPlans->isNotEmpty() || $maintenancePlans->isNotEmpty())
        <div class="extra-services-section">
          <h3 class="extra-services-title">額外費用</h3>
          <div class="extra-services-grid">
            @foreach($hostingPlans as $plan)
            <div class="extra-service-card">
              <div class="extra-service-header">
                <h4>{{ $plan->title }}</h4>
                <span class="extra-service-price">{{ $plan->formatted_price }}<small>/年</small></span>
              </div>
              @if($plan->items->isNotEmpty())
              <ul class="extra-service-features">
                @foreach($plan->items as $item)
                <li>{{ $item->name }}</li>
                @endforeach
              </ul>
              @endif
            </div>
            @endforeach
            @foreach($maintenancePlans as $plan)
            <div class="extra-service-card">
              <div class="extra-service-header">
                <h4>{{ $plan->title }}</h4>
                <span class="extra-service-price">{{ $plan->formatted_price }}<small>{{ $plan->billing_cycle === 'yearly' ? '/年' : '' }}</small></span>
              </div>
              @if($plan->description)
              <p class="extra-service-desc">{{ $plan->description }}</p>
              @endif
              @if($plan->items->isNotEmpty())
              <ul class="extra-service-features">
                @foreach($plan->items as $item)
                <li>{{ $item->name }}</li>
                @endforeach
              </ul>
              @endif
            </div>
            @endforeach
          </div>
        </div>
        @endif

        {{-- 加值服務 --}}
        @if($addonPlans->isNotEmpty())
        <div class="addon-services-section">
          <h3 class="extra-services-title">加值服務</h3>
          <div class="addon-services-grid">
            @foreach($addonPlans as $plan)
            <div class="addon-service-card">
              <h4>{{ $plan->title }}</h4>
              <span class="addon-service-price">{{ $plan->formatted_price }}</span>
              @if($plan->description)
              <p>{{ $plan->description }}</p>
              @endif
              @if($plan->items->isNotEmpty())
              <small class="addon-service-note">{{ $plan->items->first()->name }}</small>
              @endif
            </div>
            @endforeach
          </div>
        </div>
        @endif

        {{-- 注意事項 --}}
        <div class="pricing-notes-section">
          <h3 class="extra-services-title">注意事項</h3>
          <div class="pricing-notes-grid">
            <div class="pricing-note-item">
              <svg viewBox="0 0 24 24" width="20" height="20"><line x1="12" y1="1" x2="12" y2="23" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
              <div>
                <strong>付款方式</strong>
                <p>簽約 50% 訂金 / 驗收後 50% 尾款</p>
              </div>
            </div>
            <div class="pricing-note-item">
              <svg viewBox="0 0 24 24" width="20" height="20"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
              <div>
                <strong>保固範圍</strong>
                <p>Bug 修復、安全性更新、瀏覽器相容</p>
              </div>
            </div>
            <div class="pricing-note-item">
              <svg viewBox="0 0 24 24" width="20" height="20"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
              <div>
                <strong>修改定義</strong>
                <p>文字/圖片更換為一次，版面重排另計</p>
              </div>
            </div>
            <div class="pricing-note-item">
              <svg viewBox="0 0 24 24" width="20" height="20"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.5"/><polyline points="12 6 12 12 16 14" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
              <div>
                <strong>報價有效期</strong>
                <p>報價單有效期為 30 天</p>
              </div>
            </div>
          </div>
        </div>

        {{-- 分隔線 --}}
        <div class="pricing-divider">
          <div class="pricing-divider-line"></div>
          <span class="pricing-divider-text">需要客製化報價？使用下方計算器</span>
          <div class="pricing-divider-line"></div>
        </div>
      </div>
    </section>
    @endif

    {{-- ===== QUOTE CALCULATOR ===== --}}
    <section class="quote-section">
      <div class="quote-wrapper">

        {{-- Progress Bar --}}
        <div class="quote-progress">
          <div class="quote-progress-bar">
            <div class="quote-progress-fill" id="quoteProgressFill" style="width: 25%;"></div>
          </div>
          <div class="quote-progress-steps">
            <div class="quote-progress-step active" data-step="1">
              <span class="quote-progress-dot"></span>
              <span class="quote-progress-label">{{ __('frontend.quote_step1_label') }}</span>
            </div>
            <div class="quote-progress-step" data-step="2">
              <span class="quote-progress-dot"></span>
              <span class="quote-progress-label">{{ __('frontend.quote_step2_label') }}</span>
            </div>
            <div class="quote-progress-step" data-step="3">
              <span class="quote-progress-dot"></span>
              <span class="quote-progress-label">{{ __('frontend.quote_step3_label') }}</span>
            </div>
            <div class="quote-progress-step" data-step="4">
              <span class="quote-progress-dot"></span>
              <span class="quote-progress-label">{{ __('frontend.quote_step4_label') }}</span>
            </div>
          </div>
        </div>

        {{-- Step Container --}}
        <div class="quote-steps-container" id="quoteStepsContainer">

          {{-- STEP 1: Project Type (Dynamic) --}}
          <div class="quote-step active" id="quoteStep1">
            <div class="quote-step-header">
              <span class="quote-step-number">01</span>
              <h2 class="quote-step-title">{{ __('frontend.quote_step1_title') }}</h2>
              <p class="quote-step-desc">{{ __('frontend.quote_step1_desc') }}</p>
              <p class="step-description">選擇您的專案類型，讓我們了解基本需求</p>
            </div>
            <div class="quote-options-grid quote-options-grid--2x2">
              @foreach($categories as $cat)
              <label class="quote-option-card" data-value="{{ $cat->slug }}">
                <input type="radio" name="project_type" value="{{ $cat->slug }}">
                <div class="quote-option-inner">
                  <div class="quote-option-icon">
                    @switch($cat->slug)
                      @case('app')
                        <svg viewBox="0 0 24 24" width="32" height="32"><rect x="5" y="2" width="14" height="20" rx="2" fill="none" stroke="currentColor" stroke-width="1.5"/><line x1="12" y1="18" x2="12" y2="18.01" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                        @break
                      @case('web')
                        <svg viewBox="0 0 24 24" width="32" height="32"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.5"/><line x1="2" y1="12" x2="22" y2="12" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                        @break
                      @case('system')
                        <svg viewBox="0 0 24 24" width="32" height="32"><path d="M12 2L2 7l10 5 10-5-10-5z" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M2 17l10 5 10-5" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M2 12l10 5 10-5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                        @break
                      @case('ai')
                        <svg viewBox="0 0 24 24" width="32" height="32"><path d="M12 2a4 4 0 014 4v1a4 4 0 01-8 0V6a4 4 0 014-4z" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M6 10v2a6 6 0 0012 0v-2" fill="none" stroke="currentColor" stroke-width="1.5"/><line x1="12" y1="18" x2="12" y2="22" fill="none" stroke="currentColor" stroke-width="1.5"/><line x1="8" y1="22" x2="16" y2="22" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                        @break
                      @default
                        <svg viewBox="0 0 24 24" width="32" height="32"><path d="M12 19l7-7 3 3-7 7-3-3z" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M2 2l7.586 7.586" fill="none" stroke="currentColor" stroke-width="1.5"/><circle cx="11" cy="11" r="2" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                    @endswitch
                  </div>
                  <h3 class="quote-option-title">{{ $cat->name }}</h3>
                  <p class="quote-option-desc">{{ $cat->description ?: __('frontend.quote_type_' . $cat->slug . '_desc', ['default' => '']) }}</p>
                </div>
                <span class="quote-option-check">
                  <svg viewBox="0 0 24 24" width="18" height="18"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                </span>
              </label>
              @endforeach
            </div>
            <div class="quote-step-actions">
              <div></div>
              <button type="button" class="quote-btn-next" id="btnStep1Next" disabled>
                {{ __('frontend.quote_btn_next') }}
                <svg viewBox="0 0 24 24" width="16" height="16"><polyline points="9 18 15 12 9 6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
              </button>
            </div>
          </div>

          {{-- STEP 2: Features --}}
          <div class="quote-step" id="quoteStep2">
            <div class="quote-step-header">
              <span class="quote-step-number">02</span>
              <h2 class="quote-step-title">{{ __('frontend.quote_step2_title') }}</h2>
              <p class="quote-step-desc">{{ __('frontend.quote_step2_desc') }}</p>
              <p class="step-description">選擇需要的功能，有助於更精準的報價估算</p>
            </div>
            <div class="quote-options-grid quote-options-grid--features" id="quoteFeaturesGrid">
              {{-- Features will be populated by JS based on Step 1 selection --}}
            </div>
            <div class="quote-step-actions">
              <button type="button" class="quote-btn-prev" id="btnStep2Prev">
                <svg viewBox="0 0 24 24" width="16" height="16"><polyline points="15 18 9 12 15 6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                {{ __('frontend.quote_btn_prev') }}
              </button>
              <button type="button" class="quote-btn-next" id="btnStep2Next">
                {{ __('frontend.quote_btn_next') }}
                <svg viewBox="0 0 24 24" width="16" height="16"><polyline points="9 18 15 12 9 6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
              </button>
            </div>
          </div>

          {{-- STEP 3: Timeline & Budget --}}
          <div class="quote-step" id="quoteStep3">
            <div class="quote-step-header">
              <span class="quote-step-number">03</span>
              <h2 class="quote-step-title">{{ __('frontend.quote_step3_title') }}</h2>
              <p class="quote-step-desc">{{ __('frontend.quote_step3_desc') }}</p>
              <p class="step-description">了解您的預算範圍有助於我們提供最合適的方案</p>
            </div>

            <div class="quote-subsection">
              <h3 class="quote-subsection-title">
                <svg viewBox="0 0 24 24" width="18" height="18"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.5"/><polyline points="12 6 12 12 16 14" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                {{ __('frontend.quote_timeline_label') }}
              </h3>
              <div class="quote-options-grid quote-options-grid--row">
                <label class="quote-option-card quote-option-card--compact" data-value="1month">
                  <input type="radio" name="timeline" value="1month">
                  <div class="quote-option-inner">
                    <h3 class="quote-option-title">{{ __('frontend.quote_timeline_1month') }}</h3>
                    <p class="quote-option-desc">{{ __('frontend.quote_timeline_1month_desc') }}</p>
                  </div>
                  <span class="quote-option-check">
                    <svg viewBox="0 0 24 24" width="18" height="18"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                  </span>
                </label>
                <label class="quote-option-card quote-option-card--compact" data-value="1-3months">
                  <input type="radio" name="timeline" value="1-3months">
                  <div class="quote-option-inner">
                    <h3 class="quote-option-title">{{ __('frontend.quote_timeline_1_3months') }}</h3>
                    <p class="quote-option-desc">{{ __('frontend.quote_timeline_1_3months_desc') }}</p>
                  </div>
                  <span class="quote-option-check">
                    <svg viewBox="0 0 24 24" width="18" height="18"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                  </span>
                </label>
                <label class="quote-option-card quote-option-card--compact" data-value="3-6months">
                  <input type="radio" name="timeline" value="3-6months">
                  <div class="quote-option-inner">
                    <h3 class="quote-option-title">{{ __('frontend.quote_timeline_3_6months') }}</h3>
                    <p class="quote-option-desc">{{ __('frontend.quote_timeline_3_6months_desc') }}</p>
                  </div>
                  <span class="quote-option-check">
                    <svg viewBox="0 0 24 24" width="18" height="18"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                  </span>
                </label>
                <label class="quote-option-card quote-option-card--compact" data-value="flexible">
                  <input type="radio" name="timeline" value="flexible">
                  <div class="quote-option-inner">
                    <h3 class="quote-option-title">{{ __('frontend.quote_timeline_flexible') }}</h3>
                    <p class="quote-option-desc">{{ __('frontend.quote_timeline_flexible_desc') }}</p>
                  </div>
                  <span class="quote-option-check">
                    <svg viewBox="0 0 24 24" width="18" height="18"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                  </span>
                </label>
              </div>
            </div>

            <div class="quote-subsection">
              <h3 class="quote-subsection-title">
                <svg viewBox="0 0 24 24" width="18" height="18"><line x1="12" y1="1" x2="12" y2="23" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                {{ __('frontend.quote_budget_label') }}
              </h3>
              <div class="quote-options-grid quote-options-grid--row">
                <label class="quote-option-card quote-option-card--compact" data-value="under5">
                  <input type="radio" name="budget" value="under5">
                  <div class="quote-option-inner">
                    <h3 class="quote-option-title">{{ __('frontend.quote_budget_under5') }}</h3>
                    <p class="quote-option-desc">{{ __('frontend.quote_budget_under5_desc') }}</p>
                  </div>
                  <span class="quote-option-check">
                    <svg viewBox="0 0 24 24" width="18" height="18"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                  </span>
                </label>
                <label class="quote-option-card quote-option-card--compact" data-value="5-15">
                  <input type="radio" name="budget" value="5-15">
                  <div class="quote-option-inner">
                    <h3 class="quote-option-title">{{ __('frontend.quote_budget_5_15') }}</h3>
                    <p class="quote-option-desc">{{ __('frontend.quote_budget_5_15_desc') }}</p>
                  </div>
                  <span class="quote-option-check">
                    <svg viewBox="0 0 24 24" width="18" height="18"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                  </span>
                </label>
                <label class="quote-option-card quote-option-card--compact" data-value="15-30">
                  <input type="radio" name="budget" value="15-30">
                  <div class="quote-option-inner">
                    <h3 class="quote-option-title">{{ __('frontend.quote_budget_15_30') }}</h3>
                    <p class="quote-option-desc">{{ __('frontend.quote_budget_15_30_desc') }}</p>
                  </div>
                  <span class="quote-option-check">
                    <svg viewBox="0 0 24 24" width="18" height="18"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                  </span>
                </label>
                <label class="quote-option-card quote-option-card--compact" data-value="30plus">
                  <input type="radio" name="budget" value="30plus">
                  <div class="quote-option-inner">
                    <h3 class="quote-option-title">{{ __('frontend.quote_budget_30plus') }}</h3>
                    <p class="quote-option-desc">{{ __('frontend.quote_budget_30plus_desc') }}</p>
                  </div>
                  <span class="quote-option-check">
                    <svg viewBox="0 0 24 24" width="18" height="18"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                  </span>
                </label>
              </div>
            </div>

            <div class="quote-step-actions">
              <button type="button" class="quote-btn-prev" id="btnStep3Prev">
                <svg viewBox="0 0 24 24" width="16" height="16"><polyline points="15 18 9 12 15 6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                {{ __('frontend.quote_btn_prev') }}
              </button>
              <button type="button" class="quote-btn-next" id="btnStep3Next" disabled>
                {{ __('frontend.quote_btn_next') }}
                <svg viewBox="0 0 24 24" width="16" height="16"><polyline points="9 18 15 12 9 6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
              </button>
            </div>
          </div>

          {{-- STEP 4: Contact Info --}}
          <div class="quote-step" id="quoteStep4">
            <div class="quote-step-header">
              <span class="quote-step-number">04</span>
              <h2 class="quote-step-title">{{ __('frontend.quote_step4_title') }}</h2>
              <p class="quote-step-desc">{{ __('frontend.quote_step4_desc') }}</p>
              <p class="step-description">填寫聯繫方式，我們通常 24 小時內回覆</p>
            </div>

            <div class="quote-form-grid">
              <div class="quote-form-group">
                <label class="quote-form-label" for="quoteName">{{ __('frontend.quote_name_label') }} <span class="quote-required">{{ __('frontend.quote_required') }}</span></label>
                <input type="text" id="quoteName" class="quote-form-input" placeholder="{{ __('frontend.quote_name_placeholder') }}" required>
              </div>
              <div class="quote-form-group">
                <label class="quote-form-label" for="quoteEmail">{{ __('frontend.quote_email_label') }} <span class="quote-required">{{ __('frontend.quote_required') }}</span></label>
                <input type="email" id="quoteEmail" class="quote-form-input" placeholder="example@email.com" required>
              </div>
              <div class="quote-form-group">
                <label class="quote-form-label" for="quotePhone">電話 <span class="quote-optional">（選填）</span></label>
                <input type="tel" id="quotePhone" class="quote-form-input" placeholder="0912-345-678">
              </div>
              <div class="quote-form-group">
                <label class="quote-form-label" for="quoteCompany">{{ __('frontend.quote_company_label') }} <span class="quote-optional">{{ __('frontend.quote_company_optional') }}</span></label>
                <input type="text" id="quoteCompany" class="quote-form-input" placeholder="{{ __('frontend.quote_company_placeholder') }}">
              </div>
              <div class="quote-form-group quote-form-group--full">
                <label class="quote-form-label" for="quoteMessage">{{ __('frontend.quote_message_label') }} <span class="quote-optional">{{ __('frontend.quote_message_optional') }}</span></label>
                <textarea id="quoteMessage" class="quote-form-textarea" placeholder="{{ __('frontend.quote_message_placeholder') }}" rows="4"></textarea>
              </div>
            </div>

            <div class="quote-step-actions">
              <button type="button" class="quote-btn-prev" id="btnStep4Prev">
                <svg viewBox="0 0 24 24" width="16" height="16"><polyline points="15 18 9 12 15 6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                {{ __('frontend.quote_btn_prev') }}
              </button>
              <button type="button" class="quote-btn-submit" id="btnCalculate" disabled>
                <svg viewBox="0 0 24 24" width="16" height="16"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="22 4 12 14.01 9 11.01" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                {{ __('frontend.quote_btn_calculate') }}
              </button>
            </div>
          </div>

          {{-- RESULT --}}
          <div class="quote-step" id="quoteResult">
            <div class="quote-result">
              <div class="quote-result-header">
                <div class="quote-result-icon">
                  <svg viewBox="0 0 24 24" width="40" height="40"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" fill="none" stroke="currentColor" stroke-width="1.5"/><polyline points="22 4 12 14.01 9 11.01" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                </div>
                <h2 class="quote-result-title">{{ __('frontend.quote_result_title') }}</h2>
                <p class="quote-result-desc">{{ __('frontend.quote_result_desc') }}</p>
              </div>

              <div class="quote-estimate">
                <div class="quote-estimate-label">{{ __('frontend.quote_estimate_label') }}</div>
                <div class="quote-estimate-price" id="quoteEstimatePrice">NT$ 0 ~ NT$ 0</div>
                <div class="quote-estimate-note">{{ __('frontend.quote_estimate_note') }}</div>
              </div>

              <div class="quote-result-summary" id="quoteResultSummary">
                {{-- Summary will be populated by JS --}}
              </div>

              <div class="quote-result-actions">
                <form action="{{ route('quote-request.submit') }}" method="POST" id="quoteSubmitForm">
                  @csrf
                  <input type="hidden" name="name" id="quoteSubmitName">
                  <input type="hidden" name="email" id="quoteSubmitEmail">
                  <input type="hidden" name="phone" id="quoteSubmitPhone">
                  <input type="hidden" name="company" id="quoteSubmitCompany">
                  <input type="hidden" name="project_type" id="quoteSubmitProjectType">
                  <input type="hidden" name="selected_features" id="quoteSubmitFeatures">
                  <input type="hidden" name="timeline" id="quoteSubmitTimeline">
                  <input type="hidden" name="budget" id="quoteSubmitBudget">
                  <input type="hidden" name="estimated_min" id="quoteSubmitEstimatedMin">
                  <input type="hidden" name="estimated_max" id="quoteSubmitEstimatedMax">
                  <input type="hidden" name="message" id="quoteSubmitMessage">
                  <button type="submit" class="quote-btn-submit">
                    <svg viewBox="0 0 24 24" width="16" height="16"><line x1="22" y1="2" x2="11" y2="13" fill="none" stroke="currentColor" stroke-width="2"/><polygon points="22 2 15 22 11 13 2 9 22 2" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                    {{ __('frontend.quote_btn_submit') }}
                  </button>
                </form>
                <button type="button" class="quote-btn-reset" id="btnReset">
                  <svg viewBox="0 0 24 24" width="16" height="16"><polyline points="1 4 1 10 7 10" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                  {{ __('frontend.quote_btn_reset') }}
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    @include('frontend.partials.footer')

    {{-- Inject pricing data for JS --}}
    @php
        $pricingJson = $categories->map(function($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'base_price_min' => (float) $cat->base_price_min,
                'base_price_max' => (float) $cat->base_price_max,
                'features' => $cat->features->map(function($f) {
                    return [
                        'id' => $f->id,
                        'name' => $f->name,
                        'slug' => $f->slug,
                        'description' => $f->description,
                        'price_min' => (float) $f->price_min,
                        'price_max' => (float) $f->price_max,
                    ];
                }),
            ];
        });
        $quoteConfigJson = [
            'timeline_multipliers' => config('quote-pricing.timeline_multipliers'),
            'timeline_labels' => config('quote-pricing.timeline_labels'),
            'budget_labels' => config('quote-pricing.budget_labels'),
        ];
    @endphp
    <script>
      window.pricingData = @json($pricingJson);
      window.quoteConfig = @json($quoteConfigJson);
      window.preselectedCategory = @json($preselectedCategory ?? null);
    </script>
@endsection
