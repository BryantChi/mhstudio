<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 有期間月數又已執行中／已完成的合約，必定走過舊版「轉執行中＋填上線日」流程，start_date 即上線日。
        // 沒有月數的是功能上線前的合約，start_date 為手填、不保證等於上線日，寧可留空。
        // 用 query builder 而非 Eloquent：不觸發 activity log，避免灌出一批假的異動紀錄。
        DB::table('contracts')
            ->whereIn('status', ['active', 'completed'])
            ->whereNotNull('term_months')
            ->whereNotNull('start_date')
            ->whereNull('go_live_date')
            ->update(['go_live_date' => DB::raw('start_date')]);
    }

    public function down(): void
    {
        // 回填的值無法與之後手動記錄的區分，交由前一支 migration 的 down() 連欄位一併移除
    }
};
