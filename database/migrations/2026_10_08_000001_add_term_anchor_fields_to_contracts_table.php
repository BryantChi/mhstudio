<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // 期間起算點：go_live（上線日）／delivery（實際交件日）／custom（以 start_date 為起點）。
            // 預設 go_live，與既有合約「自上線日起算」的行為一致。
            $table->string('term_anchor', 20)->default('go_live')->after('term_months');
            // 上線、交件改為獨立的里程碑日期，不再綁「執行中」狀態；只有被選為起算點的那個會觸發起訖日重算
            $table->date('go_live_date')->nullable()->after('term_anchor');
            $table->date('actual_delivery_date')->nullable()->after('go_live_date');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['term_anchor', 'go_live_date', 'actual_delivery_date']);
        });
    }
};
