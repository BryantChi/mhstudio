<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // 年度經常性費用（維護/主機），純參考供條款 {{yearly_fee}} 帶入，不計入 total/收款
            $table->decimal('yearly_fee', 12, 2)->nullable()->after('warranty_months');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('yearly_fee');
        });
    }
};
