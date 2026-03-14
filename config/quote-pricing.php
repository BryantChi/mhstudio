<?php

return [
    // 時程乘數
    'timeline_multipliers' => [
        '1month'    => ['min' => 1.3, 'max' => 1.5],
        '1-3months' => ['min' => 1.0, 'max' => 1.0],
        '3-6months' => ['min' => 0.9, 'max' => 0.95],
        'flexible'  => ['min' => 0.85, 'max' => 0.9],
    ],
    // 時程標籤
    'timeline_labels' => [
        '1month' => '1 個月內',
        '1-3months' => '1 - 3 個月',
        '3-6months' => '3 - 6 個月',
        'flexible' => '彈性',
    ],
    // 預算標籤
    'budget_labels' => [
        'under5' => '5 萬以下',
        '5-15' => '5 - 15 萬',
        '15-30' => '15 - 30 萬',
        '30plus' => '30 萬以上',
    ],
    // 管理員通知 Email
    'admin_notification_email' => env('QUOTE_NOTIFICATION_EMAIL', null),
];
