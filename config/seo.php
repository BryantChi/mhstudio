<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SEO 預設設定
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'title' => env('SEO_DEFAULT_TITLE', config('app.name')),
        'description' => env('SEO_DEFAULT_DESCRIPTION', ''),
        'keywords' => env('SEO_DEFAULT_KEYWORDS', ''),
        'og_image' => env('SEO_DEFAULT_OG_IMAGE', '/images/default-og.jpg'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap 設定
    |--------------------------------------------------------------------------
    */
    'sitemap' => [
        'enabled' => env('SEO_ENABLE_SITEMAP', true),
        'path' => public_path('sitemap.xml'),
        'change_frequency' => 'daily',
        'priority' => 0.8,
        'ping_google' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Robots.txt 設定
    |--------------------------------------------------------------------------
    */
    'robots' => [
        'enabled' => env('SEO_ENABLE_ROBOTS', true),
        'default_content' => "User-agent: *\nAllow: /\n\nSitemap: " . env('APP_URL') . "/sitemap.xml",
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Tags 長度限制
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'title' => [
            'min' => 30,
            'max' => 60,
            'recommended' => 55,
        ],
        'description' => [
            'min' => 70,
            'max' => 160,
            'recommended' => 155,
        ],
        'keywords' => [
            'max' => 255,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema.org 設定
    |--------------------------------------------------------------------------
    */
    'schema' => [
        'organization' => [
            'name' => config('app.name'),
            'logo' => env('APP_URL') . '/images/logo.png',
            'url' => env('APP_URL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 不需要索引的路徑
    |--------------------------------------------------------------------------
    */
    'noindex_paths' => [
        '/' . env('ADMIN_PREFIX', 'admin') . '/*',
        '/api/*',
        '/deploy/*',
    ],
];
