@extends('frontend.layouts.app')

@section('title', $project->title . ' | 客戶專區 | MH Studio')

@section('content')
    @include('frontend.partials.navigation')

    {{-- ===== PAGE HEADER ===== --}}
    <section class="page-header">
      <div class="page-header-bg-grid"></div>
      <div class="page-header-orb"></div>
      <div class="page-header-content">
        <div class="section-label">PROJECT DETAILS</div>
        <h1 class="page-header-title">{{ $project->title }}</h1>
        <div class="section-divider"></div>
        <p class="section-desc">
          <span class="client-status-badge client-status-{{ $project->status }}">{{ $project->status_label }}</span>
          @if($project->client)
            <span style="margin-left: 12px; color: var(--text-secondary);">{{ $project->client }}</span>
          @endif
        </p>
      </div>
    </section>

    {{-- ===== PROJECT CONTENT ===== --}}
    <section class="client-project-detail">
      <div class="client-detail-container">
        {{-- Back Link --}}
        <a href="{{ route('client.dashboard') }}" class="client-back-link animate-on-scroll">
          <svg viewBox="0 0 24 24" width="16" height="16"><path d="M19 12H5M12 19l-7-7 7-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          返回客戶專區
        </a>

        {{-- Progress Overview --}}
        <div class="client-detail-card animate-on-scroll">
          <div class="client-detail-card-header">
            <h2 class="client-detail-card-title">
              <svg viewBox="0 0 24 24" width="20" height="20"><circle cx="12" cy="12" r="10" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5"/><path d="M12 6v6l4 2" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5" stroke-linecap="round"/></svg>
              專案進度
            </h2>
            <span class="client-progress-value-lg">{{ $project->progress_percentage }}%</span>
          </div>
          <div class="client-progress-bar client-progress-bar-lg">
            <div class="client-progress-fill" style="width: {{ $project->progress_percentage }}%"></div>
          </div>
        </div>

        {{-- Milestones --}}
        <div class="client-detail-card animate-on-scroll">
          <div class="client-detail-card-header">
            <h2 class="client-detail-card-title">
              <svg viewBox="0 0 24 24" width="20" height="20"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5"/><polyline points="22 4 12 14.01 9 11.01" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5"/></svg>
              里程碑
            </h2>
          </div>

          @if($project->milestones->isEmpty())
            <div class="client-empty-inline">尚未設定里程碑</div>
          @else
            <div class="client-milestone-timeline">
              @foreach($project->milestones as $milestone)
                <div class="client-milestone-item client-milestone-{{ $milestone->status }}">
                  <div class="client-milestone-indicator">
                    @if($milestone->status === 'completed')
                      <div class="client-milestone-icon completed">
                        <svg viewBox="0 0 24 24" width="16" height="16"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      </div>
                    @elseif($milestone->status === 'in_progress')
                      <div class="client-milestone-icon in-progress">
                        <svg viewBox="0 0 24 24" width="16" height="16"><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                      </div>
                    @else
                      <div class="client-milestone-icon pending">
                        <svg viewBox="0 0 24 24" width="16" height="16"><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                      </div>
                    @endif
                    @if(!$loop->last)
                      <div class="client-milestone-line"></div>
                    @endif
                  </div>
                  <div class="client-milestone-content">
                    <h4 class="client-milestone-title">{{ $milestone->title }}</h4>
                    @if($milestone->description)
                      <p class="client-milestone-desc">{{ $milestone->description }}</p>
                    @endif
                    <div class="client-milestone-meta">
                      <span class="client-milestone-status-badge client-ms-{{ $milestone->status }}">
                        {{ $milestone->status_label }}
                      </span>
                      @if($milestone->due_date)
                        <span class="client-milestone-due {{ $milestone->is_overdue ? 'overdue' : '' }}">
                          <svg viewBox="0 0 24 24" width="12" height="12"><rect x="3" y="4" width="18" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><line x1="16" y1="2" x2="16" y2="6" fill="none" stroke="currentColor" stroke-width="2"/><line x1="8" y1="2" x2="8" y2="6" fill="none" stroke="currentColor" stroke-width="2"/><line x1="3" y1="10" x2="21" y2="10" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                          {{ $milestone->due_date->format('Y-m-d') }}
                        </span>
                      @endif
                      @if($milestone->completed_at)
                        <span class="client-milestone-completed-date">
                          完成於 {{ $milestone->completed_at->format('Y-m-d') }}
                        </span>
                      @endif
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>

        {{-- Files --}}
        <div class="client-detail-card animate-on-scroll">
          <div class="client-detail-card-header">
            <h2 class="client-detail-card-title">
              <svg viewBox="0 0 24 24" width="20" height="20"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5"/><polyline points="14 2 14 8 20 8" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5"/></svg>
              專案檔案
            </h2>
          </div>

          @if($project->files->isEmpty())
            <div class="client-empty-inline">尚無檔案</div>
          @else
            <div class="client-files-list">
              @foreach($project->files as $file)
                <div class="client-file-item">
                  <div class="client-file-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" fill="none" stroke="var(--accent-blue)" stroke-width="1.5"/><polyline points="14 2 14 8 20 8" fill="none" stroke="var(--accent-blue)" stroke-width="1.5"/></svg>
                  </div>
                  <div class="client-file-info">
                    <div class="client-file-name">{{ $file->original_name }}</div>
                    <div class="client-file-meta">
                      <span>{{ $file->human_size }}</span>
                      <span>{{ $file->created_at->format('Y-m-d') }}</span>
                      @if($file->description)
                        <span>{{ $file->description }}</span>
                      @endif
                    </div>
                  </div>
                  <a href="{{ route('client.project.file.download', [$project, $file]) }}"
                     class="client-file-download"
                     title="下載檔案">
                    <svg viewBox="0 0 24 24" width="18" height="18"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="7 10 12 15 17 10" fill="none" stroke="currentColor" stroke-width="2"/><line x1="12" y1="15" x2="12" y2="3" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                  </a>
                </div>
              @endforeach
            </div>
          @endif
        </div>

        {{-- Comments --}}
        <div class="client-detail-card animate-on-scroll">
          <div class="client-detail-card-header">
            <h2 class="client-detail-card-title">
              <svg viewBox="0 0 24 24" width="20" height="20"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5"/></svg>
              溝通記錄
            </h2>
          </div>

          {{-- Comment Form --}}
          <form method="POST" action="{{ route('client.project.comment', $project) }}" class="client-comment-form">
            @csrf
            <div class="client-comment-input-wrapper">
              <textarea name="content"
                        class="client-comment-textarea"
                        placeholder="輸入您的訊息..."
                        rows="3"
                        maxlength="2000"
                        required>{{ old('content') }}</textarea>
              @error('content')
                <div class="client-form-error">{{ $message }}</div>
              @enderror
            </div>
            <button type="submit" class="client-comment-submit">
              <svg viewBox="0 0 24 24" width="16" height="16"><line x1="22" y1="2" x2="11" y2="13" fill="none" stroke="currentColor" stroke-width="2"/><polygon points="22 2 15 22 11 13 2 9 22 2" fill="none" stroke="currentColor" stroke-width="2"/></svg>
              送出留言
            </button>
          </form>

          @if(session('success'))
            <div class="client-alert-success">{{ session('success') }}</div>
          @endif

          {{-- Comments List --}}
          @if($project->comments->isEmpty())
            <div class="client-empty-inline" style="margin-top: 20px;">尚無留言</div>
          @else
            <div class="client-comments-list">
              @foreach($project->comments as $comment)
                <div class="client-comment-item {{ $comment->user_id === auth()->id() ? 'own' : 'other' }}">
                  <div class="client-comment-avatar">
                    <img src="{{ $comment->user->avatar }}" alt="{{ $comment->user->name }}">
                  </div>
                  <div class="client-comment-body">
                    <div class="client-comment-header">
                      <span class="client-comment-author">{{ $comment->user->name }}</span>
                      <span class="client-comment-time">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="client-comment-content">{{ $comment->content }}</div>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </section>

    @include('frontend.partials.footer')
@endsection

@push('styles')
<style>
/* ===== CLIENT PROJECT DETAIL STYLES ===== */
.client-project-detail {
  padding: clamp(40px, 8vw, 80px) clamp(20px, 5vw, 60px);
}

.client-detail-container {
  max-width: 900px;
  margin: 0 auto;
}

/* Back Link */
.client-back-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--text-secondary);
  text-decoration: none;
  font-size: 14px;
  margin-bottom: 32px;
  transition: color 0.3s ease;
}

.client-back-link:hover {
  color: var(--accent-cyan);
}

/* Detail Card */
.client-detail-card {
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: 16px;
  padding: 28px;
  margin-bottom: 24px;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.client-detail-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.client-detail-card-title {
  font-family: var(--font-display);
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Progress */
.client-progress-value-lg {
  font-family: var(--font-display);
  font-size: 28px;
  font-weight: 700;
  color: var(--accent-cyan);
}

.client-progress-bar-lg {
  height: 10px;
  border-radius: 5px;
}

/* Status Badges (reuse from dashboard) */
.client-status-badge {
  font-family: var(--font-display);
  font-size: 10px;
  letter-spacing: 1px;
  text-transform: uppercase;
  padding: 4px 10px;
  border-radius: 20px;
  white-space: nowrap;
  display: inline-block;
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

/* Progress Bar (shared) */
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

/* Empty Inline */
.client-empty-inline {
  text-align: center;
  padding: 32px 20px;
  color: var(--text-dim);
  font-size: 14px;
}

/* ===== MILESTONE TIMELINE ===== */
.client-milestone-timeline {
  position: relative;
}

.client-milestone-item {
  display: flex;
  gap: 20px;
  position: relative;
}

.client-milestone-indicator {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex-shrink: 0;
  width: 32px;
}

.client-milestone-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  position: relative;
  z-index: 1;
}

.client-milestone-icon.completed {
  background: rgba(39, 201, 63, 0.2);
  color: #27c93f;
  box-shadow: 0 0 12px rgba(39, 201, 63, 0.3);
}

.client-milestone-icon.in-progress {
  background: rgba(0, 212, 255, 0.2);
  color: var(--accent-cyan);
  box-shadow: 0 0 12px rgba(0, 212, 255, 0.3);
  animation: pulse-glow 2s infinite;
}

.client-milestone-icon.pending {
  background: rgba(138, 155, 194, 0.15);
  color: var(--text-dim);
}

.client-milestone-line {
  width: 2px;
  flex-grow: 1;
  min-height: 20px;
  background: linear-gradient(180deg, var(--border-subtle), transparent);
  margin: 4px 0;
}

.client-milestone-content {
  padding-bottom: 28px;
  flex: 1;
}

.client-milestone-title {
  font-family: var(--font-display);
  font-size: 15px;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 6px;
  line-height: 32px;
}

.client-milestone-completed .client-milestone-title {
  color: var(--text-secondary);
}

.client-milestone-desc {
  color: var(--text-secondary);
  font-size: 14px;
  line-height: 1.6;
  margin: 0 0 10px;
}

.client-milestone-meta {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}

.client-milestone-status-badge {
  font-family: var(--font-display);
  font-size: 10px;
  letter-spacing: 1px;
  text-transform: uppercase;
  padding: 3px 8px;
  border-radius: 12px;
}

.client-ms-completed {
  background: rgba(39, 201, 63, 0.15);
  color: #27c93f;
}

.client-ms-in_progress {
  background: rgba(0, 212, 255, 0.15);
  color: var(--accent-cyan);
}

.client-ms-pending {
  background: rgba(138, 155, 194, 0.1);
  color: var(--text-dim);
}

.client-milestone-due {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: var(--text-secondary);
}

.client-milestone-due.overdue {
  color: #ff5f56;
}

.client-milestone-completed-date {
  font-size: 12px;
  color: #27c93f;
}

/* ===== FILES ===== */
.client-files-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.client-file-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 14px 16px;
  background: rgba(6, 11, 24, 0.4);
  border: 1px solid var(--border-subtle);
  border-radius: 10px;
  transition: border-color 0.3s ease;
}

.client-file-item:hover {
  border-color: var(--border-glow);
}

.client-file-icon {
  flex-shrink: 0;
  opacity: 0.7;
}

.client-file-info {
  flex: 1;
  min-width: 0;
}

.client-file-name {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.client-file-meta {
  display: flex;
  gap: 12px;
  font-size: 12px;
  color: var(--text-dim);
  margin-top: 2px;
}

.client-file-download {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  color: var(--accent-cyan);
  background: rgba(0, 212, 255, 0.1);
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.client-file-download:hover {
  background: rgba(0, 212, 255, 0.2);
  box-shadow: 0 0 12px rgba(0, 212, 255, 0.2);
}

/* ===== COMMENTS ===== */
.client-comment-form {
  margin-bottom: 24px;
}

.client-comment-textarea {
  width: 100%;
  background: rgba(6, 11, 24, 0.6);
  border: 1px solid var(--border-subtle);
  border-radius: 10px;
  padding: 14px 16px;
  color: var(--text-primary);
  font-family: var(--font-body);
  font-size: 14px;
  resize: vertical;
  transition: border-color 0.3s ease;
  min-height: 80px;
}

.client-comment-textarea:focus {
  outline: none;
  border-color: var(--accent-cyan);
  box-shadow: 0 0 0 2px rgba(0, 212, 255, 0.1);
}

.client-comment-textarea::placeholder {
  color: var(--text-dim);
}

.client-form-error {
  color: #ff5f56;
  font-size: 12px;
  margin-top: 4px;
}

.client-comment-submit {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-top: 12px;
  padding: 10px 24px;
  background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan));
  color: white;
  border: none;
  border-radius: 8px;
  font-family: var(--font-display);
  font-size: 13px;
  letter-spacing: 1px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.client-comment-submit:hover {
  box-shadow: 0 4px 20px rgba(0, 212, 255, 0.3);
  transform: translateY(-1px);
}

.client-alert-success {
  padding: 12px 16px;
  background: rgba(39, 201, 63, 0.1);
  border: 1px solid rgba(39, 201, 63, 0.3);
  border-radius: 8px;
  color: #27c93f;
  font-size: 14px;
  margin-top: 12px;
}

.client-comments-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid var(--border-subtle);
}

.client-comment-item {
  display: flex;
  gap: 12px;
}

.client-comment-avatar {
  flex-shrink: 0;
}

.client-comment-avatar img {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 1px solid var(--border-subtle);
}

.client-comment-body {
  flex: 1;
  min-width: 0;
}

.client-comment-header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-bottom: 4px;
}

.client-comment-author {
  font-size: 13px;
  font-weight: 600;
  color: var(--accent-cyan);
}

.client-comment-item.own .client-comment-author {
  color: var(--accent-blue);
}

.client-comment-time {
  font-size: 11px;
  color: var(--text-dim);
}

.client-comment-content {
  font-size: 14px;
  line-height: 1.7;
  color: var(--text-secondary);
  white-space: pre-line;
}

/* Responsive */
@media (max-width: 640px) {
  .client-detail-card {
    padding: 20px;
  }

  .client-milestone-item {
    gap: 14px;
  }

  .client-file-meta {
    flex-direction: column;
    gap: 2px;
  }
}
</style>
@endpush
