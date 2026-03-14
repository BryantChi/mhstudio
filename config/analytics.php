<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Analytics 設定
    |--------------------------------------------------------------------------
    */
    'google' => [
        'view_id' => env('ANALYTICS_VIEW_ID'),
        'service_account_credentials_json' => env('GOOGLE_APPLICATION_CREDENTIALS'),
        'cache_lifetime_in_minutes' => 60 * 24, // 24 hours
        'tracking_id' => env('GA_TRACKING_ID'), // GA4 Measurement ID
    ],

    /*
    |--------------------------------------------------------------------------
    | 事件追蹤設定
    |--------------------------------------------------------------------------
    */
    'events' => [
        'enabled' => true,
        'track_page_views' => true,
        'track_clicks' => true,
        'track_form_submissions' => true,
        'track_downloads' => true,
        'track_external_links' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | 自定義事件類型
    |--------------------------------------------------------------------------
    */
    'custom_events' => [
        'button_click' => '按鈕點擊',
        'form_submit' => '表單提交',
        'download' => '檔案下載',
        'video_play' => '影片播放',
        'external_link' => '外部連結點擊',
        'search' => '站內搜尋',
    ],

    /*
    |--------------------------------------------------------------------------
    | 報表設定
    |--------------------------------------------------------------------------
    */
    'reports' => [
        'default_date_range' => 30, // days
        'max_results' => 1000,
        'cache_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard 設定
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'refresh_interval' => 60, // seconds
        'top_pages_limit' => 10,
        'top_referrers_limit' => 10,
    ],
];
