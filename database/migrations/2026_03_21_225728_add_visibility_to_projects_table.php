<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // public: 列表+詳情+搜尋引擎 | unlisted: 僅有連結可看 | hidden: 完全隱藏
            $table->string('visibility', 20)->default('public')->after('is_featured');
            // noindex: 搜尋引擎不收錄（即使 visibility=public）
            $table->boolean('exclude_from_search')->default(false)->after('visibility');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['visibility', 'exclude_from_search']);
        });
    }
};
