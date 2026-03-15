@extends('frontend.layouts.app')

@section('title', '報價狀態查詢 — ' . $quoteRequest->request_number)

@section('content')
    @include('frontend.partials.navigation')

    <section class="page-header">
      <div class="page-header-bg-grid"></div>
      <div class="page-header-orb"></div>
      <div class="page-header-content">
        <div class="section-label">報價狀態</div>
        <h1 class="page-header-title">報價請求查詢</h1>
        <div class="section-divider"></div>
        <p class="section-desc">追蹤您的報價請求進度</p>
      </div>
    </section>

    <section class="quote-status-section">
      <div class="quote-status-wrapper">
        {{-- Request Number & Date --}}
        <div class="quote-status-header">
          <div class="quote-status-number">{{ $quoteRequest->request_number }}</div>
          <div class="quote-status-date">提交於 {{ $quoteRequest->created_at->format('Y 年 m 月 d 日 H:i') }}</div>
        </div>

        {{-- Status Stepper --}}
        <div class="quote-status-stepper">
          @php
            $steps = [
              ['key' => 'pending', 'label' => '已收到'],
              ['key' => 'reviewing', 'label' => '審核中'],
              ['key' => 'quoted', 'label' => '已報價'],
              ['key' => 'accepted', 'label' => '已接受'],
            ];
            $statusOrder = ['pending' => 0, 'reviewing' => 1, 'quoted' => 2, 'accepted' => 3, 'rejected' => -1, 'expired' => -1];
            $currentIndex = $statusOrder[$quoteRequest->status] ?? 0;
          @endphp
          @foreach($steps as $index => $step)
            <div class="quote-status-step {{ $index < $currentIndex ? 'completed' : ($index === $currentIndex ? 'active' : '') }}">
              <div class="quote-status-step-dot">
                @if($index < $currentIndex)
                  <svg viewBox="0 0 24 24" width="16" height="16"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2.5"/></svg>
                @else
                  {{ $index + 1 }}
                @endif
              </div>
              <div class="quote-status-step-label">{{ $step['label'] }}</div>
            </div>
            @if(!$loop->last)
              <div class="quote-status-step-line {{ $index < $currentIndex ? 'completed' : '' }}"></div>
            @endif
          @endforeach
        </div>

        @if(in_array($quoteRequest->status, ['rejected', 'expired']))
          <div class="quote-status-alert quote-status-alert--{{ $quoteRequest->status === 'rejected' ? 'danger' : 'warning' }}">
            {{ $quoteRequest->status === 'rejected' ? '此報價請求已被拒絕。' : '此報價請求已過期。' }}
          </div>
        @endif

        {{-- Quote Summary --}}
        <div class="quote-status-card">
          <h3 class="quote-status-card-title">報價摘要</h3>
          <div class="quote-status-detail">
            <div class="quote-status-detail-row">
              <span class="quote-status-detail-label">服務類型</span>
              <span class="quote-status-detail-value">{{ $quoteRequest->project_type }}</span>
            </div>
            <div class="quote-status-detail-row">
              <span class="quote-status-detail-label">選擇的功能</span>
              <span class="quote-status-detail-value">
                @if($quoteRequest->selected_features && count($quoteRequest->selected_features) > 0)
                  @foreach($quoteRequest->selected_features as $feature)
                    <span class="quote-status-tag">{{ $feature['name'] ?? '—' }}</span>
                  @endforeach
                @else
                  未選擇
                @endif
              </span>
            </div>
            <div class="quote-status-detail-row">
              <span class="quote-status-detail-label">時程</span>
              <span class="quote-status-detail-value">{{ config('quote-pricing.timeline_labels.' . $quoteRequest->timeline, $quoteRequest->timeline) }}</span>
            </div>
            <div class="quote-status-detail-row">
              <span class="quote-status-detail-label">預算範圍</span>
              <span class="quote-status-detail-value">{{ config('quote-pricing.budget_labels.' . $quoteRequest->budget, $quoteRequest->budget) }}</span>
            </div>
          </div>
        </div>

        {{-- Estimated Price --}}
        <div class="quote-status-estimate">
          <div class="quote-status-estimate-label">預估金額</div>
          <div class="quote-status-estimate-price">NT$ {{ number_format($quoteRequest->estimated_min) }} ~ NT$ {{ number_format($quoteRequest->estimated_max) }}</div>
          <div class="quote-status-estimate-note">* 此為初步估算，實際報價可能依需求調整</div>
        </div>

        @if($quoteRequest->status === 'quoted' && $quoteRequest->quote)
          <div class="quote-status-card quote-status-card--highlight">
            <h3 class="quote-status-card-title">正式報價金額</h3>
            <div class="quote-status-estimate-price">NT$ {{ number_format($quoteRequest->quote->total) }}</div>
          </div>
        @endif

        {{-- Contact CTA --}}
        <div class="quote-status-cta">
          <p>有任何問題？歡迎隨時聯繫我們</p>
          <a href="/#contact" class="quote-btn-submit">
            <svg viewBox="0 0 24 24" width="16" height="16"><line x1="22" y1="2" x2="11" y2="13" fill="none" stroke="currentColor" stroke-width="2"/><polygon points="22 2 15 22 11 13 2 9 22 2" fill="none" stroke="currentColor" stroke-width="2"/></svg>
            聯繫我們
          </a>
        </div>
      </div>
    </section>

    @include('frontend.partials.footer')
@endsection
