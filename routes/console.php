<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes / Scheduled Tasks
|--------------------------------------------------------------------------
*/

// 每日自動標記逾期發票
Schedule::command('invoices:mark-overdue')->dailyAt('00:30');
