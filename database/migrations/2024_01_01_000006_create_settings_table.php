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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->default('general')->comment('設定群組');
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string')->comment('資料類型: string, integer, boolean, array, json');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false)->comment('是否公開');
            $table->boolean('is_editable')->default(true)->comment('是否可編輯');
            $table->timestamps();

            // 索引
            $table->index('group');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
