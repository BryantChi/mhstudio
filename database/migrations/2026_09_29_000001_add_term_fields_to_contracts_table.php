<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // 合約期間自「上線日」起算：簽約時只知道預計交件日，
            // start_date/end_date 先存預估值，轉為執行中（active）時再以實際上線日覆寫。
            $table->date('expected_delivery_date')->nullable()->after('end_date');
            $table->unsignedSmallInteger('term_months')->nullable()->after('expected_delivery_date');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['expected_delivery_date', 'term_months']);
        });
    }
};
