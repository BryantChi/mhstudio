<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 合併 ServicePlan 資料到 Service，然後刪除 service_plans 表
     * 此 migration 為不可逆操作，回滾需依賴資料庫備份
     */
    public function up(): void
    {
        // ===== 1. services 表新增欄位 =====
        Schema::table('services', function (Blueprint $table) {
            $table->string('type')->nullable()->after('slug');
            $table->string('subtitle')->nullable()->after('type');
            $table->decimal('price', 10, 2)->default(0)->after('price_range');
            $table->string('price_label')->nullable()->after('price');
            $table->string('billing_cycle')->nullable()->after('price_label');
            $table->integer('pages_min')->nullable()->after('billing_cycle');
            $table->integer('pages_max')->nullable()->after('pages_min');
            $table->string('design_method')->nullable()->after('pages_max');
            $table->integer('special_features_count')->nullable()->after('design_method');
            $table->integer('cms_modules_count')->nullable()->after('special_features_count');
            $table->integer('revisions')->nullable()->after('cms_modules_count');
            $table->integer('warranty_months')->nullable()->after('revisions');
            $table->integer('work_days_min')->nullable()->after('warranty_months');
            $table->integer('work_days_max')->nullable()->after('work_days_min');
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->boolean('show_on_homepage')->default(true)->after('is_featured');

            $table->index('type');
            $table->index(['type', 'is_active', 'order']);
        });

        // ===== 2. service_plan_items → service_items =====
        Schema::rename('service_plan_items', 'service_items');

        Schema::table('service_items', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('id')
                  ->constrained('services')->cascadeOnDelete();
        });

        // ===== 3. 資料遷移 =====
        // 3a. 有 service_plan_id 的 Service → 把 Plan 欄位合併進來
        $servicesWithPlan = DB::table('services')
            ->whereNotNull('service_plan_id')
            ->get();

        foreach ($servicesWithPlan as $service) {
            $plan = DB::table('service_plans')->where('id', $service->service_plan_id)->first();
            if ($plan) {
                DB::table('services')->where('id', $service->id)->update([
                    'type' => $plan->type,
                    'subtitle' => $plan->subtitle,
                    'price' => $plan->price,
                    'price_label' => $plan->price_label,
                    'billing_cycle' => $plan->billing_cycle,
                    'pages_min' => $plan->pages_min,
                    'pages_max' => $plan->pages_max,
                    'design_method' => $plan->design_method,
                    'special_features_count' => $plan->special_features_count,
                    'cms_modules_count' => $plan->cms_modules_count,
                    'revisions' => $plan->revisions,
                    'warranty_months' => $plan->warranty_months,
                    'work_days_min' => $plan->work_days_min,
                    'work_days_max' => $plan->work_days_max,
                    'is_featured' => $plan->is_featured,
                    'show_on_homepage' => true,
                ]);

                // 更新 service_items 的 service_id
                DB::table('service_items')
                    ->where('service_plan_id', $plan->id)
                    ->update(['service_id' => $service->id]);
            }
        }

        // 3b. 未被關聯的 ServicePlan → 建立新 Service
        $linkedPlanIds = DB::table('services')
            ->whereNotNull('service_plan_id')
            ->pluck('service_plan_id')
            ->toArray();

        $unlinkedPlans = DB::table('service_plans')
            ->whereNotIn('id', $linkedPlanIds)
            ->get();

        foreach ($unlinkedPlans as $plan) {
            $newServiceId = DB::table('services')->insertGetId([
                'title' => $plan->name,
                'slug' => $plan->slug,
                'icon' => $plan->icon,
                'excerpt' => $plan->description,
                'type' => $plan->type,
                'subtitle' => $plan->subtitle,
                'price' => $plan->price,
                'price_label' => $plan->price_label,
                'billing_cycle' => $plan->billing_cycle,
                'pages_min' => $plan->pages_min,
                'pages_max' => $plan->pages_max,
                'design_method' => $plan->design_method,
                'special_features_count' => $plan->special_features_count,
                'cms_modules_count' => $plan->cms_modules_count,
                'revisions' => $plan->revisions,
                'warranty_months' => $plan->warranty_months,
                'work_days_min' => $plan->work_days_min,
                'work_days_max' => $plan->work_days_max,
                'is_featured' => $plan->is_featured,
                'is_active' => $plan->is_active,
                'order' => $plan->order + 100, // 排在原有服務之後
                'show_on_homepage' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 更新 service_items 的 service_id
            DB::table('service_items')
                ->where('service_plan_id', $plan->id)
                ->update(['service_id' => $newServiceId]);
        }

        // 3c. 原有沒有 service_plan_id 的 Service → show_on_homepage = true
        DB::table('services')
            ->whereNull('service_plan_id')
            ->whereNull('type')
            ->update(['show_on_homepage' => true]);

        // ===== 4. 清理：移除舊欄位和舊表 =====
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['service_plan_id']);
            $table->dropColumn('service_plan_id');
        });

        Schema::table('service_items', function (Blueprint $table) {
            // FK 名稱保留原始表名 service_plan_items 的命名規則
            $table->dropForeign('service_plan_items_service_plan_id_foreign');
            $table->dropColumn('service_plan_id');
        });

        Schema::dropIfExists('service_plans');
    }

    /**
     * 此 migration 為不可逆操作
     */
    public function down(): void
    {
        throw new RuntimeException(
            '此 migration 為不可逆操作。請使用資料庫備份來回滾。'
        );
    }
};
