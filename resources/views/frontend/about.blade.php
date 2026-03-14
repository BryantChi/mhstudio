@extends('frontend.layouts.app')

@section('title', __('frontend.about_page_title'))

@section('content')
    @include('frontend.partials.navigation')

    {{-- ===== PAGE HEADER ===== --}}
    <section class="page-header">
      <div class="page-header-bg-grid"></div>
      <div class="page-header-orb"></div>
      <div class="page-header-content">
        <div class="section-label">{{ __('frontend.about_label') }}</div>
        <h1 class="page-header-title">{{ __('frontend.about_title') }}</h1>
        <div class="section-divider"></div>
        <p class="section-desc">{!! __('frontend.about_slogan') !!}</p>
      </div>
    </section>

    {{-- ===== STORY ===== --}}
    <section class="about-story">
      <div class="about-story-wrapper">
        <div class="about-story-content animate-on-scroll">
          <div class="section-label">{{ __('frontend.about_story_label') }}</div>
          <h2 class="about-story-title">{{ __('frontend.about_story_title') }}</h2>
          <div class="section-divider" style="margin-left:0;"></div>
          <p>{{ __('frontend.about_story_p1') }}</p>
          <p>{{ __('frontend.about_story_p2') }}</p>
          <p>{{ __('frontend.about_story_p3') }}</p>
        </div>
        <div class="about-story-visual animate-on-scroll">
          <div class="about-code-block">
            <div class="about-code-header">
              <span class="about-code-dot" style="background:#ff5f56;"></span>
              <span class="about-code-dot" style="background:#ffbd2e;"></span>
              <span class="about-code-dot" style="background:#27c93f;"></span>
              <span class="about-code-filename">mh-studio.kt</span>
            </div>
            <div class="about-code-body">
              <code>
<span class="code-keyword">class</span> <span class="code-class">MHStudio</span> {<br>
&nbsp;&nbsp;<span class="code-keyword">val</span> <span class="code-prop">founder</span> = <span class="code-string">"孟衡"</span><br>
&nbsp;&nbsp;<span class="code-keyword">val</span> <span class="code-prop">mission</span> = <span class="code-string">"Balance &bull; Precision &bull; Innovation"</span><br>
&nbsp;&nbsp;<span class="code-keyword">val</span> <span class="code-prop">skills</span> = <span class="code-keyword">listOf</span>(<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="code-string">"App Development"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="code-string">"Web Development"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="code-string">"System Architecture"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="code-string">"UI/UX Design"</span><br>
&nbsp;&nbsp;)<br>
<br>
&nbsp;&nbsp;<span class="code-keyword">fun</span> <span class="code-func">buildProduct</span>() =<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="code-prop">skills</span>.<span class="code-func">combine</span>(<span class="code-prop">passion</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.<span class="code-func">iterate</span>(<span class="code-prop">untilPerfect</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.<span class="code-func">deliver</span>(<span class="code-prop">withExcellence</span>)<br>
}
              </code>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- ===== TIMELINE ===== --}}
    <section class="about-timeline-section">
      <div class="section-header animate-on-scroll">
        <div class="section-label">{{ __('frontend.about_milestones_label') }}</div>
        <h2 class="section-title">{{ __('frontend.about_milestones_title') }}</h2>
        <div class="section-divider"></div>
      </div>

      <div class="process-timeline">
        <div class="process-step animate-on-scroll">
          <div class="process-number">
            <span class="timeline-year">2018</span>
          </div>
          <div class="process-content">
            <h3>{{ __('frontend.about_milestone_2018_title') }}</h3>
            <p>{{ __('frontend.about_milestone_2018_desc') }}</p>
          </div>
        </div>
        <div class="process-step animate-on-scroll">
          <div class="process-number">
            <span class="timeline-year">2020</span>
          </div>
          <div class="process-content">
            <h3>{{ __('frontend.about_milestone_2020_title') }}</h3>
            <p>{{ __('frontend.about_milestone_2020_desc') }}</p>
          </div>
        </div>
        <div class="process-step animate-on-scroll">
          <div class="process-number">
            <span class="timeline-year">2022</span>
          </div>
          <div class="process-content">
            <h3>{{ __('frontend.about_milestone_2022_title') }}</h3>
            <p>{{ __('frontend.about_milestone_2022_desc') }}</p>
          </div>
        </div>
        <div class="process-step animate-on-scroll">
          <div class="process-number">
            <span class="timeline-year">2024</span>
          </div>
          <div class="process-content">
            <h3>{{ __('frontend.about_milestone_2024_title') }}</h3>
            <p>{{ __('frontend.about_milestone_2024_desc') }}</p>
          </div>
        </div>
        <div class="process-step animate-on-scroll">
          <div class="process-number">
            <span class="timeline-year">NOW</span>
          </div>
          <div class="process-content">
            <h3>{{ __('frontend.about_milestone_now_title') }}</h3>
            <p>{{ __('frontend.about_milestone_now_desc') }}</p>
          </div>
        </div>
      </div>
    </section>

    {{-- ===== SKILLS ===== --}}
    <section class="about-skills-section">
      <div class="section-header animate-on-scroll">
        <div class="section-label">{{ __('frontend.about_skills_label') }}</div>
        <h2 class="section-title">{{ __('frontend.about_skills_title') }}</h2>
        <div class="section-divider"></div>
      </div>

      <div class="about-skills-grid">
        <div class="about-skill-group animate-on-scroll">
          <h3 class="about-skill-group-title">
            <svg viewBox="0 0 24 24" width="20" height="20"><rect x="5" y="2" width="14" height="20" rx="2" fill="none" stroke="currentColor" stroke-width="1.5"/><line x1="12" y1="18" x2="12" y2="18.01" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
            {{ __('frontend.about_skills_mobile') }}
          </h3>
          <div class="tech-grid" style="max-width:100%;">
            <div class="tech-item"><div class="tech-item-name">Kotlin</div><div class="tech-item-type">Android Native</div></div>
            <div class="tech-item"><div class="tech-item-name">Compose</div><div class="tech-item-type">Modern UI</div></div>
            <div class="tech-item"><div class="tech-item-name">Flutter</div><div class="tech-item-type">Cross-Platform</div></div>
            <div class="tech-item"><div class="tech-item-name">MVVM</div><div class="tech-item-type">Architecture</div></div>
            <div class="tech-item"><div class="tech-item-name">Hilt</div><div class="tech-item-type">DI Framework</div></div>
            <div class="tech-item"><div class="tech-item-name">Room</div><div class="tech-item-type">Local DB</div></div>
          </div>
        </div>

        <div class="about-skill-group animate-on-scroll">
          <h3 class="about-skill-group-title">
            <svg viewBox="0 0 24 24" width="20" height="20"><rect x="2" y="3" width="20" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.5"/><line x1="8" y1="21" x2="16" y2="21" fill="none" stroke="currentColor" stroke-width="1.5"/><line x1="12" y1="17" x2="12" y2="21" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
            {{ __('frontend.about_skills_web') }}
          </h3>
          <div class="tech-grid" style="max-width:100%;">
            <div class="tech-item"><div class="tech-item-name">Laravel</div><div class="tech-item-type">PHP Framework</div></div>
            <div class="tech-item"><div class="tech-item-name">Vue.js</div><div class="tech-item-type">Frontend</div></div>
            <div class="tech-item"><div class="tech-item-name">Tailwind</div><div class="tech-item-type">CSS Framework</div></div>
            <div class="tech-item"><div class="tech-item-name">MySQL</div><div class="tech-item-type">Database</div></div>
            <div class="tech-item"><div class="tech-item-name">Redis</div><div class="tech-item-type">Cache</div></div>
            <div class="tech-item"><div class="tech-item-name">SEO</div><div class="tech-item-type">Optimization</div></div>
          </div>
        </div>

        <div class="about-skill-group animate-on-scroll">
          <h3 class="about-skill-group-title">
            <svg viewBox="0 0 24 24" width="20" height="20"><path d="M12 2L2 7l10 5 10-5-10-5z" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M2 17l10 5 10-5" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M2 12l10 5 10-5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
            {{ __('frontend.about_skills_devops') }}
          </h3>
          <div class="tech-grid" style="max-width:100%;">
            <div class="tech-item"><div class="tech-item-name">Git</div><div class="tech-item-type">Version Control</div></div>
            <div class="tech-item"><div class="tech-item-name">CI/CD</div><div class="tech-item-type">Automation</div></div>
            <div class="tech-item"><div class="tech-item-name">Firebase</div><div class="tech-item-type">Cloud</div></div>
            <div class="tech-item"><div class="tech-item-name">Docker</div><div class="tech-item-type">Container</div></div>
            <div class="tech-item"><div class="tech-item-name">Figma</div><div class="tech-item-type">Design</div></div>
            <div class="tech-item"><div class="tech-item-name">REST API</div><div class="tech-item-type">Integration</div></div>
          </div>
        </div>
      </div>
    </section>

    {{-- ===== VALUES ===== --}}
    <section class="about-values-section">
      <div class="section-header animate-on-scroll">
        <div class="section-label">{{ __('frontend.about_values_label') }}</div>
        <h2 class="section-title">{{ __('frontend.about_values_title') }}</h2>
        <div class="section-divider"></div>
      </div>

      <div class="about-values-grid">
        <div class="about-value-card animate-on-scroll">
          <div class="about-value-icon">
            <svg viewBox="0 0 24 24" width="32" height="32">
              <path d="M12 2L2 7l10 5 10-5-10-5z" fill="none" stroke="var(--accent-cyan)" stroke-width="1.2"/>
              <path d="M2 17l10 5 10-5" fill="none" stroke="var(--accent-cyan)" stroke-width="1.2"/>
              <path d="M2 12l10 5 10-5" fill="none" stroke="var(--accent-cyan)" stroke-width="1.2"/>
            </svg>
          </div>
          <h3>{{ __('frontend.about_value_balance_title') }}</h3>
          <p>{{ __('frontend.about_value_balance_desc') }}</p>
        </div>

        <div class="about-value-card animate-on-scroll">
          <div class="about-value-icon">
            <svg viewBox="0 0 24 24" width="32" height="32">
              <circle cx="12" cy="12" r="10" fill="none" stroke="var(--accent-cyan)" stroke-width="1.2"/>
              <path d="M12 8v4l3 3" fill="none" stroke="var(--accent-cyan)" stroke-width="1.2"/>
            </svg>
          </div>
          <h3>{{ __('frontend.about_value_precision_title') }}</h3>
          <p>{{ __('frontend.about_value_precision_desc') }}</p>
        </div>

        <div class="about-value-card animate-on-scroll">
          <div class="about-value-icon">
            <svg viewBox="0 0 24 24" width="32" height="32">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="none" stroke="var(--accent-cyan)" stroke-width="1.2"/>
            </svg>
          </div>
          <h3>{{ __('frontend.about_value_innovation_title') }}</h3>
          <p>{{ __('frontend.about_value_innovation_desc') }}</p>
        </div>
      </div>
    </section>

    {{-- ===== CTA ===== --}}
    <section class="about-cta-section">
      <div class="about-cta-wrapper animate-on-scroll">
        <h2 class="about-cta-title">{{ __('frontend.about_cta_title') }}</h2>
        <p class="about-cta-desc">{{ __('frontend.about_cta_desc') }}</p>
        <div class="hero-actions" style="animation:none;opacity:1;">
          <a href="{{ route('quote') }}" class="btn-primary">免費取得報價</a>
          <a href="{{ route('portfolio') }}" class="btn-secondary">{{ __('frontend.about_cta_portfolio') }}</a>
        </div>
      </div>
    </section>

    @include('frontend.partials.footer')
@endsection
