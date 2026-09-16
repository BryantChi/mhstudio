<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // 暫定客戶：於報價單/合約/發票表單直接建立、資料尚未補齊者。
            // 與 status 是兩個正交概念（status 講關係階段，本欄講資料完整度），故不合併。
            $table->boolean('is_provisional')->default(false)->after('status');
            $table->index('is_provisional');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['is_provisional']);
            $table->dropColumn('is_provisional');
        });
    }
};
