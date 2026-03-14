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
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_name', 100);
            $table->string('event_category', 50)->nullable();
            $table->string('event_action', 50)->nullable();
            $table->string('event_label')->nullable();
            $table->integer('event_value')->nullable();
            $table->string('page_url')->nullable();
            $table->string('page_title')->nullable();
            $table->string('referrer')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('session_id')->nullable();
            $table->json('custom_dimensions')->nullable();
            $table->json('custom_metrics')->nullable();
            $table->timestamp('event_time')->useCurrent();
            $table->timestamps();

            // 索引
            $table->index('event_name');
            $table->index('event_category');
            $table->index('user_id');
            $table->index('session_id');
            $table->index('event_time');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
