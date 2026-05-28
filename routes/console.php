<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes / Scheduled Tasks
|--------------------------------------------------------------------------
*/

// 每日自動標記逾期發票
Schedule::command('invoices:mark-overdue')->dailyAt('00:30');

// 每日自動標記過期報價單
Schedule::command('quotes:mark-expired')->dailyAt('00:35');

// 每日清理 Telescope 記錄（保留 48 小時，僅在 Telescope 已安裝時執行）
if (class_exists(\Laravel\Telescope\Telescope::class)) {
    Schedule::command('telescope:prune --hours=48')->dailyAt('01:00');
}
