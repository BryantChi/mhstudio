<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes / Scheduled Tasks
|--------------------------------------------------------------------------
*/

// 每日自動標記逾期發票
Schedule::command('invoices:mark-overdue')->dailyAt('00:30');

// 每日清理 Telescope 記錄（保留 48 小時）
Schedule::command('telescope:prune --hours=48')->dailyAt('01:00');
