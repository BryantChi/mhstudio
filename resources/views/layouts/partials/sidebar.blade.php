{{-- Logo 品牌區 --}}
<div class="sidebar-brand">
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand-full">
        <div class="brand-logo">
            <svg class="brand-icon" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="brand-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#3a8bfd"/>
                        <stop offset="100%" style="stop-color:#00d4ff"/>
                    </linearGradient>
                </defs>
                <circle cx="24" cy="24" r="22" fill="none" stroke="url(#brand-grad)" stroke-width="1.2" opacity="0.4"/>
                <circle cx="24" cy="24" r="18" fill="none" stroke="url(#brand-grad)" stroke-width="0.6" opacity="0.2"/>
                <text x="24" y="28" text-anchor="middle" font-family="sans-serif" font-size="14" font-weight="800" fill="url(#brand-grad)">MH</text>
            </svg>
        </div>
        <div class="brand-text">
            <span class="brand-name">{{ config('app.name') }}</span>
            <span class="brand-subtitle">Admin Panel</span>
        </div>
    </a>
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand-narrow">
        <svg class="brand-icon-narrow" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="brand-grad-n" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#3a8bfd"/>
                    <stop offset="100%" style="stop-color:#00d4ff"/>
                </linearGradient>
            </defs>
            <circle cx="24" cy="24" r="22" fill="none" stroke="url(#brand-grad-n)" stroke-width="1.2" opacity="0.4"/>
            <text x="24" y="28" text-anchor="middle" font-family="sans-serif" font-size="14" font-weight="800" fill="url(#brand-grad-n)">MH</text>
        </svg>
    </a>
</div>

<ul class="sidebar-nav" data-coreui="navigation" data-simplebar>
    {{-- 儀表板 --}}
    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.dashboard') }}" href="{{ route('admin.dashboard') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-speedometer"></use>
            </svg>
            儀表板
        </a>
    </li>

    {{-- 內容管理 --}}
    <li class="nav-title">內容管理</li>

    @can('view articles')
    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.articles') }}" href="{{ route('admin.articles.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-newspaper"></use>
            </svg>
            文章管理
            @php
                $draftCount = \App\Models\Article::where('status', 'draft')->count();
            @endphp
            @if($draftCount > 0)
            <span class="badge badge-sm bg-warning text-dark ms-auto">{{ $draftCount }}</span>
            @endif
        </a>
    </li>
    @endcan

    @can('view categories')
    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.categories') }}" href="{{ route('admin.categories.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-folder"></use>
            </svg>
            分類管理
        </a>
    </li>
    @endcan

    @can('view tags')
    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.tags') }}" href="{{ route('admin.tags.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-tags"></use>
            </svg>
            標籤管理
        </a>
    </li>
    @endcan

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.projects') }}" href="{{ route('admin.projects.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-image"></use>
            </svg>
            作品集管理
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.services') }}" href="{{ route('admin.services.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-briefcase"></use>
            </svg>
            服務管理
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.media') }}" href="{{ route('admin.media.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-image"></use>
            </svg>
            媒體庫
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.legal-pages') }}" href="{{ route('admin.legal-pages.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-shield-alt"></use>
            </svg>
            法律頁面
        </a>
    </li>

    {{-- 商業管理 --}}
    <li class="nav-title">商業管理</li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.clients') }}" href="{{ route('admin.clients.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-people"></use>
            </svg>
            客戶管理
            @php
                $activeClientCount = \App\Models\Client::where('status', 'active')->count();
            @endphp
            @if($activeClientCount > 0)
            <span class="badge badge-sm bg-success ms-auto">{{ $activeClientCount }}</span>
            @endif
        </a>
    </li>

    <li class="nav-group {{ active_route(['admin.contracts', 'admin.contract-templates']) }}">
        <a class="nav-link nav-group-toggle" href="#">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-description"></use>
            </svg>
            合約管理
        </a>
        <ul class="nav-group-items">
            <li class="nav-item">
                <a class="nav-link {{ active_route('admin.contracts') }}" href="{{ route('admin.contracts.index') }}">
                    <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                    合約列表
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ active_route('admin.contract-templates') }}" href="{{ route('admin.contract-templates.index') }}">
                    <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                    合約範本
                </a>
            </li>
        </ul>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.quotes') }}" href="{{ route('admin.quotes.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-calculator"></use>
            </svg>
            報價單
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.pricing') }}" href="{{ route('admin.pricing.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-money"></use>
            </svg>
            定價管理
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.quote-requests') }}" href="{{ route('admin.quote-requests.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-envelope-letter"></use>
            </svg>
            報價請求
            @php
                $pendingQuoteRequestCount = \App\Models\QuoteRequest::where('status', 'pending')->count();
            @endphp
            @if($pendingQuoteRequestCount > 0)
            <span class="badge badge-sm bg-warning text-dark ms-auto">{{ $pendingQuoteRequestCount }}</span>
            @endif
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.invoices') }}" href="{{ route('admin.invoices.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-dollar"></use>
            </svg>
            發票管理
            @php
                $overdueInvoiceCount = \App\Models\Invoice::overdue()->count();
            @endphp
            @if($overdueInvoiceCount > 0)
            <span class="badge badge-sm bg-danger ms-auto">{{ $overdueInvoiceCount }}</span>
            @endif
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.tasks') }}" href="{{ route('admin.tasks.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-task"></use>
            </svg>
            任務管理
            @php
                $pendingTaskCount = \App\Models\Task::where('status', '!=', 'completed')->count();
            @endphp
            @if($pendingTaskCount > 0)
            <span class="badge badge-sm bg-primary ms-auto">{{ $pendingTaskCount }}</span>
            @endif
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.time-entries') }}" href="{{ route('admin.time-entries.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-clock"></use>
            </svg>
            工時追蹤
            @php
                $runningTimerCount = \App\Models\TimeEntry::running()->count();
            @endphp
            @if($runningTimerCount > 0)
            <span class="badge badge-sm bg-success ms-auto">{{ $runningTimerCount }}</span>
            @endif
        </a>
    </li>

    {{-- 用戶管理 --}}
    @can('view users')
    <li class="nav-title">用戶管理</li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.users') }}" href="{{ route('admin.users.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-people"></use>
            </svg>
            用戶管理
        </a>
    </li>
    @endcan

    {{-- 客戶關係 --}}
    <li class="nav-title">客戶關係</li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.contact-messages') }}" href="{{ route('admin.contact-messages.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-envelope-closed"></use>
            </svg>
            聯繫訊息
            @php
                $unreadCount = \App\Models\ContactMessage::where('status', 'unread')->count();
            @endphp
            @if($unreadCount > 0)
            <span class="badge badge-sm bg-danger ms-auto">{{ $unreadCount }}</span>
            @endif
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.testimonials') }}" href="{{ route('admin.testimonials.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-star"></use>
            </svg>
            客戶評價
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.projects.clients') }}" href="{{ route('admin.projects.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-user"></use>
            </svg>
            客戶專案管理
        </a>
    </li>

    {{-- SEO 與分析 --}}
    @can('view seo')
    <li class="nav-title">SEO 與分析</li>

    <li class="nav-group {{ active_route(['admin.seo']) }}">
        <a class="nav-link nav-group-toggle" href="#">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-chart-line"></use>
            </svg>
            SEO 管理
        </a>
        <ul class="nav-group-items">
            <li class="nav-item">
                <a class="nav-link {{ active_route('admin.seo.index') }}" href="{{ route('admin.seo.index') }}">
                    <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                    總覽
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ active_route('admin.seo.meta') }}" href="{{ route('admin.seo.meta') }}">
                    <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                    Meta 管理
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ active_route('admin.seo.sitemap-settings') }}" href="{{ route('admin.seo.sitemap-settings') }}">
                    <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                    Sitemap 設定
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ active_route('admin.seo.robots-txt') }}" href="{{ route('admin.seo.robots-txt') }}">
                    <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                    Robots.txt
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ active_route('admin.seo.analyze') }}" href="{{ route('admin.seo.analyze') }}">
                    <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                    SEO 分析
                    @php
                        $articlesWithoutSeo = \App\Models\Article::doesntHave('seoMeta')->count();
                    @endphp
                    @if($articlesWithoutSeo > 0)
                    <span class="badge badge-sm bg-warning text-dark ms-auto">{{ $articlesWithoutSeo }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.analytics') }}" href="{{ route('admin.analytics.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-chart"></use>
            </svg>
            流量分析
        </a>
    </li>
    @endcan

    {{-- 行銷工具 --}}
    <li class="nav-title">行銷工具</li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.subscribers') }}" href="{{ route('admin.subscribers.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-envelope-letter"></use>
            </svg>
            電子報訂閱
            @php
                $activeSubscriberCount = \App\Models\Subscriber::where('status', 'active')->count();
            @endphp
            @if($activeSubscriberCount > 0)
            <span class="badge badge-sm bg-info ms-auto">{{ $activeSubscriberCount }}</span>
            @endif
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.newsletters') }}" href="{{ route('admin.newsletters.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-send"></use>
            </svg>
            電子報管理
            @php
                $draftNewsletterCount = \App\Models\Newsletter::where('status', 'draft')->count();
            @endphp
            @if($draftNewsletterCount > 0)
            <span class="badge badge-sm bg-secondary ms-auto">{{ $draftNewsletterCount }}</span>
            @endif
        </a>
    </li>

    {{-- 系統設定 --}}
    @can('view settings')
    <li class="nav-title">系統設定</li>

    <li class="nav-group {{ active_route(['admin.settings']) }}">
        <a class="nav-link nav-group-toggle" href="#">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-settings"></use>
            </svg>
            系統設定
        </a>
        <ul class="nav-group-items">
            <li class="nav-item">
                <a class="nav-link {{ active_route('admin.settings.general') }}" href="{{ route('admin.settings.general') }}">
                    <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                    一般設定
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ active_route('admin.settings.seo') }}" href="{{ route('admin.settings.seo') }}">
                    <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                    SEO 設定
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ active_route('admin.settings.analytics') }}" href="{{ route('admin.settings.analytics') }}">
                    <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                    分析設定
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ active_route('admin.settings.mail') }}" href="{{ route('admin.settings.mail') }}">
                    <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                    郵件設定
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ active_route('admin.settings.frontend') }}" href="{{ route('admin.settings.frontend') }}">
                    <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                    前台設定
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ active_route('admin.settings.company') }}" href="{{ route('admin.settings.company') }}">
                    <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                    公司資訊
                </a>
            </li>
        </ul>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.system-info') }}" href="{{ route('admin.system-info') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-info"></use>
            </svg>
            系統資訊
        </a>
    </li>

    @if(auth()->user()?->isSuperAdmin())
    <li class="nav-item">
        <a class="nav-link {{ active_route('admin.deploy') }}" href="{{ route('admin.deploy.index') }}">
            <svg class="nav-icon">
                <use xlink:href="/assets/icons/free.svg#cil-cloud-upload"></use>
            </svg>
            部署工具
        </a>
    </li>
    @endif
    @endcan
</ul>

<div class="sidebar-footer">
    <button class="sidebar-toggler" type="button"></button>
</div>
