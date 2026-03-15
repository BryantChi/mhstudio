@extends('frontend.layouts.app')

@section('title', '客戶專區 | MH Studio')

@section('content')
    @include('frontend.partials.navigation')

    {{-- ===== PAGE HEADER ===== --}}
    <section class="page-header">
      <div class="page-header-bg-grid"></div>
      <div class="page-header-orb"></div>
      <div class="page-header-content">
        <div class="section-label">CLIENT PORTAL</div>
        <h1 class="page-header-title">客戶專區</h1>
        <div class="section-divider"></div>
        <p class="section-desc">查看您的專案進度、檔案與溝通記錄</p>
      </div>
    </section>

    {{-- ===== PROJECTS ===== --}}
    <section class="client-portal-section">
      <div class="client-portal-container">
        @if($projects->isEmpty())
          <div class="client-empty-state animate-on-scroll">
            <div class="client-empty-icon">
              <svg viewBox="0 0 64 64" width="64" height="64">
                <rect x="8" y="8" width="48" height="48" rx="8" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5" opacity="0.5"/>
                <path d="M24 28h16M24 36h10" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </div>
            <h3>目前沒有專案</h3>
            <p>您尚未被指派任何專案。如有疑問，請聯繫我們。</p>
            <a href="{{ route('home') }}#contact" class="btn-primary">聯繫我們</a>
          </div>
        @else
          <div class="client-projects-grid">
            @foreach($projects as $project)
              <a href="{{ route('client.project.show', $project) }}" class="client-project-card animate-on-scroll">
                <div class="client-project-card-header">
                  <h3 class="client-project-title">{{ $project->title }}</h3>
                  <span class="client-status-badge client-status-{{ $project->status }}">
                    {{ $project->status_label }}
                  </span>
                </div>

                @if($project->excerpt)
                  <p class="client-project-excerpt">{{ Str::limit($project->excerpt, 100) }}</p>
                @endif

                {{-- 進度條 --}}
                <div class="client-progress-section">
                  <div class="client-progress-header">
                    <span class="client-progress-label">專案進度</span>
                    <span class="client-progress-value">{{ $project->progress_percentage }}%</span>
                  </div>
                  <div class="client-progress-bar">
                    <div class="client-progress-fill" style="width: {{ $project->progress_percentage }}%"></div>
                  </div>
                </div>

                {{-- 統計資訊 --}}
                <div class="client-project-stats">
                  <div class="client-stat">
                    <svg viewBox="0 0 24 24" width="16" height="16"><path d="M9 11l3 3L22 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                    <span>{{ $project->milestones->where('status', 'completed')->count() }}/{{ $project->milestones->count() }} 里程碑</span>
                  </div>
                  <div class="client-stat">
                    <svg viewBox="0 0 24 24" width="16" height="16"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="14 2 14 8 20 8" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                    <span>{{ $project->files->count() }} 個檔案</span>
                  </div>
                </div>

                {{-- 最後更新 --}}
                <div class="client-project-footer">
                  <span class="client-project-date">最後更新：{{ $project->updated_at->diffForHumans() }}</span>
                  <span class="client-project-arrow">
                    <svg viewBox="0 0 24 24" width="18" height="18"><path d="M5 12h14M12 5l7 7-7 7" fill="none" stroke="var(--accent-cyan)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </span>
                </div>
              </a>
            @endforeach
          </div>
        @endif
      </div>
    </section>

    @include('frontend.partials.footer')
@endsection

@push('styles')
<style>
/* ===== CLIENT PORTAL STYLES ===== */
.client-portal-section {
  padding: clamp(60px, 10vw, 120px) clamp(20px, 5vw, 60px);
}

.client-portal-container {
  max-width: 1200px;
  margin: 0 auto;
}

/* Empty State */
.client-empty-state {
  text-align: center;
  padding: 80px 20px;
}

.client-empty-icon {
  margin-bottom: 24px;
  opacity: 0.6;
}

.client-empty-state h3 {
  font-family: var(--font-display);
  font-size: 24px;
  color: var(--text-primary);
  margin-bottom: 12px;
}

.client-empty-state p {
  color: var(--text-secondary);
  margin-bottom: 32px;
  font-size: 16px;
}

/* Projects Grid */
.client-projects-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 24px;
}

/* Project Card */
.client-project-card {
  display: block;
  text-decoration: none;
  color: inherit;
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: 16px;
  padding: 28px;
  transition: all 0.3s ease;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.client-project-card:hover {
  border-color: var(--border-glow);
  box-shadow: 0 8px 32px rgba(0, 212, 255, 0.1);
  transform: translateY(-4px);
  color: inherit;
  text-decoration: none;
}

.client-project-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
  gap: 12px;
}

.client-project-title {
  font-family: var(--font-display);
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
  line-height: 1.4;
}

.client-status-badge {
  font-family: var(--font-display);
  font-size: 10px;
  letter-spacing: 1px;
  text-transform: uppercase;
  padding: 4px 10px;
  border-radius: 20px;
  white-space: nowrap;
  flex-shrink: 0;
}

.client-status-published {
  background: rgba(0, 212, 255, 0.15);
  color: var(--accent-cyan);
  box-shadow: 0 0 8px rgba(0, 212, 255, 0.2);
}

.client-status-draft {
  background: rgba(138, 155, 194, 0.15);
  color: var(--text-secondary);
}

.client-project-excerpt {
  color: var(--text-secondary);
  font-size: 14px;
  line-height: 1.6;
  margin-bottom: 20px;
}

/* Progress */
.client-progress-section {
  margin-bottom: 20px;
}

.client-progress-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.client-progress-label {
  font-size: 12px;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 1px;
  font-family: var(--font-display);
}

.client-progress-value {
  font-size: 14px;
  font-weight: 600;
  color: var(--accent-cyan);
  font-family: var(--font-display);
}

.client-progress-bar {
  height: 6px;
  background: rgba(58, 139, 253, 0.15);
  border-radius: 3px;
  overflow: hidden;
}

.client-progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--accent-blue), var(--accent-cyan));
  border-radius: 3px;
  transition: width 0.6s ease;
  position: relative;
}

.client-progress-fill::after {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 20px;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3));
  border-radius: 0 3px 3px 0;
}

/* Stats */
.client-project-stats {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.client-stat {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: var(--text-secondary);
}

.client-stat svg {
  opacity: 0.6;
}

/* Footer */
.client-project-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 16px;
  border-top: 1px solid var(--border-subtle);
}

.client-project-date {
  font-size: 12px;
  color: var(--text-dim);
}

.client-project-arrow {
  opacity: 0;
  transform: translateX(-8px);
  transition: all 0.3s ease;
}

.client-project-card:hover .client-project-arrow {
  opacity: 1;
  transform: translateX(0);
}

/* Responsive */
@media (max-width: 480px) {
  .client-projects-grid {
    grid-template-columns: 1fr;
  }
}
</style>
@endpush
