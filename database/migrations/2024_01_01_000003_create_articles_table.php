<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();

            $table->enum('status', ['draft', 'published', 'scheduled', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();

            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);

            // SEO 欄位（可選：也可以用關聯表）
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            // 進階設定
            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_comments')->default(true);
            $table->unsignedInteger('order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'status']);
            $table->index('is_featured');
            $table->fullText(['title', 'content']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
