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
        Schema::create('service_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['website', 'hosting', 'maintenance', 'addon']);
            $table->text('description')->nullable();
            $table->string('subtitle')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('price_label')->nullable();
            $table->string('billing_cycle')->nullable()->comment('once, yearly, monthly');
            $table->string('icon')->nullable();
            $table->integer('pages_min')->nullable();
            $table->integer('pages_max')->nullable();
            $table->string('design_method')->nullable();
            $table->integer('special_features_count')->nullable();
            $table->integer('cms_modules_count')->nullable();
            $table->integer('revisions')->nullable();
            $table->integer('warranty_months')->nullable();
            $table->integer('work_days_min')->nullable();
            $table->integer('work_days_max')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('type');
            $table->index('slug');
            $table->index(['type', 'is_active', 'order']);
        });

        Schema::create('service_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_plan_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['included', 'highlighted', 'optional'])->default('included');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_plan_items');
        Schema::dropIfExists('service_plans');
    }
};
