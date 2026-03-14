<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin 路由前綴
    |--------------------------------------------------------------------------
    */
    'prefix' => env('ADMIN_PREFIX', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | 分頁設定
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'per_page' => env('ADMIN_PER_PAGE', 15),
        'page_name' => 'page',
    ],

    /*
    |--------------------------------------------------------------------------
    | Session 設定
    |--------------------------------------------------------------------------
    */
    'session' => [
        'timeout' => env('ADMIN_SESSION_TIMEOUT', 120), // minutes
        'remember_me' => true,
        'remember_duration' => 60 * 24 * 30, // 30 days in minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | 安全設定
    |--------------------------------------------------------------------------
    */
    'security' => [
        'login_attempts' => 5,
        'lockout_duration' => 15, // minutes
        'password_min_length' => 8,
        'password_require_uppercase' => true,
        'password_require_numbers' => true,
        'password_require_special_chars' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | 檔案上傳設定
    |--------------------------------------------------------------------------
    */
    'upload' => [
        'max_size' => env('UPLOAD_MAX_SIZE', 10240), // KB
        'allowed_mimes' => env('UPLOAD_ALLOWED_MIMES', 'jpg,jpeg,png,gif,pdf,doc,docx'),
        'disk' => 'public',
        'image_quality' => 90,
        'thumbnail_sizes' => [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [800, 600],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard 設定
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'cache_duration' => 10, // minutes
        'stats' => [
            'show_users' => true,
            'show_articles' => true,
            'show_views' => true,
            'show_analytics' => true,
        ],
        'charts' => [
            'user_growth_months' => 12,
            'popular_articles_limit' => 5,
            'recent_activities_limit' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu 設定
    |--------------------------------------------------------------------------
    */
    'menu' => [
        [
            'title' => '儀表板',
            'icon' => 'cil-speedometer',
            'route' => 'admin.dashboard',
            'permission' => null,
        ],
        [
            'title' => '用戶管理',
            'icon' => 'cil-user',
            'permission' => 'view users',
            'children' => [
                [
                    'title' => '用戶列表',
                    'route' => 'admin.users.index',
                    'permission' => 'view users',
                ],
                [
                    'title' => '角色管理',
                    'route' => 'admin.roles.index',
                    'permission' => 'view roles',
                ],
                [
                    'title' => '權限管理',
                    'route' => 'admin.permissions.index',
                    'permission' => 'view permissions',
                ],
            ],
        ],
        [
            'title' => '內容管理',
            'icon' => 'cil-file',
            'permission' => 'view articles',
            'children' => [
                [
                    'title' => '文章列表',
                    'route' => 'admin.articles.index',
                    'permission' => 'view articles',
                ],
                [
                    'title' => '分類管理',
                    'route' => 'admin.categories.index',
                    'permission' => 'view categories',
                ],
                [
                    'title' => '媒體庫',
                    'route' => 'admin.media.index',
                    'permission' => 'view media',
                ],
            ],
        ],
        [
            'title' => 'SEO 優化',
            'icon' => 'cil-magnifying-glass',
            'permission' => 'manage seo',
            'children' => [
                [
                    'title' => 'Meta 設定',
                    'route' => 'admin.seo.meta',
                    'permission' => 'manage seo',
                ],
                [
                    'title' => 'Sitemap',
                    'route' => 'admin.seo.sitemap',
                    'permission' => 'manage seo',
                ],
            ],
        ],
        [
            'title' => '數據分析',
            'icon' => 'cil-chart-line',
            'permission' => 'view analytics',
            'children' => [
                [
                    'title' => '分析儀表板',
                    'route' => 'admin.analytics.dashboard',
                    'permission' => 'view analytics',
                ],
                [
                    'title' => '報表管理',
                    'route' => 'admin.analytics.reports',
                    'permission' => 'view analytics',
                ],
            ],
        ],
        [
            'title' => '定價管理',
            'icon' => 'cil-calculator',
            'route' => 'admin.pricing.index',
            'permission' => null,
        ],
        [
            'title' => '報價請求',
            'icon' => 'cil-envelope-letter',
            'route' => 'admin.quote-requests.index',
            'permission' => null,
            'badge' => 'pending_quote_requests',
        ],
        [
            'title' => '系統設定',
            'icon' => 'cil-settings',
            'permission' => 'manage settings',
            'children' => [
                [
                    'title' => '一般設定',
                    'route' => 'admin.settings.general',
                    'permission' => 'manage settings',
                ],
                [
                    'title' => '操作日誌',
                    'route' => 'admin.logs.index',
                    'permission' => 'view logs',
                ],
            ],
        ],
    ],
];
