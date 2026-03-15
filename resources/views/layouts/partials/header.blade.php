<header class="header header-sticky">
    <div class="container-fluid">
        {{-- 左側區域 --}}
        <div class="header-left">
            <button class="header-toggler" type="button" id="header-sidebar-toggle">
                <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" style="width:28px;height:28px;">
                    <defs>
                        <linearGradient id="hdr-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#3a8bfd"/>
                            <stop offset="100%" style="stop-color:#00d4ff"/>
                        </linearGradient>
                    </defs>
                    <circle cx="24" cy="24" r="22" fill="none" stroke="url(#hdr-grad)" stroke-width="1.2" opacity="0.4"/>
                    <text x="24" y="28" text-anchor="middle" font-family="sans-serif" font-size="14" font-weight="800" fill="url(#hdr-grad)">MH</text>
                </svg>
            </button>
        </div>

        {{-- 中間區域 - 搜尋 --}}
        <div class="header-center">
            <form class="header-search">
                <div class="search-wrapper">
                    <svg class="search-icon">
                        <use xlink:href="/assets/icons/free.svg#cil-magnifying-glass"></use>
                    </svg>
                    <input type="search" class="search-input" placeholder="搜尋文章、分類、標籤..." aria-label="搜尋">
                </div>
            </form>
        </div>

        {{-- 右側區域 --}}
        <div class="header-right">
            {{-- 快速新增 --}}
            @can('create articles')
            <a href="{{ route('admin.articles.create') }}" class="header-action-btn" data-coreui-toggle="tooltip" title="新增文章">
                <svg class="icon">
                    <use xlink:href="/assets/icons/free.svg#cil-plus"></use>
                </svg>
            </a>
            @endcan

            {{-- 前往網站 --}}
            <a href="{{ url('/') }}" target="_blank" class="header-action-btn" data-coreui-toggle="tooltip" title="前往網站">
                <svg class="icon">
                    <use xlink:href="/assets/icons/free.svg#cil-external-link"></use>
                </svg>
            </a>

            {{-- 使用者選單 --}}
            <div class="header-user dropdown">
                <button class="user-trigger" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
                    <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="user-avatar">
                    <div class="user-info">
                        <span class="user-name">{{ auth()->user()->name }}</span>
                        <span class="user-role">{{ auth()->user()->roles->first()?->name ?? '使用者' }}</span>
                    </div>
                    <svg class="user-chevron">
                        <use xlink:href="/assets/icons/free.svg#cil-chevron-bottom"></use>
                    </svg>
                </button>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header">
                        <div class="dropdown-header-name">{{ auth()->user()->name }}</div>
                        <div class="dropdown-header-email">{{ auth()->user()->email }}</div>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    @can('edit users')
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.users.edit', auth()->id()) }}">
                            <svg class="icon me-2">
                                <use xlink:href="/assets/icons/free.svg#cil-user"></use>
                            </svg>
                            個人資料
                        </a>
                    </li>
                    @endcan

                    @can('view settings')
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                            <svg class="icon me-2">
                                <use xlink:href="/assets/icons/free.svg#cil-settings"></use>
                            </svg>
                            系統設定
                        </a>
                    </li>
                    @endcan

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item dropdown-item-danger">
                                <svg class="icon me-2">
                                    <use xlink:href="/assets/icons/free.svg#cil-account-logout"></use>
                                </svg>
                                登出
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
