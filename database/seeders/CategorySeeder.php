<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 頂層分類
        $tech = Category::create([
            'name' => '技術',
            'slug' => 'tech',
            'description' => '技術相關文章',
            'status' => 'active',
            'order' => 1,
            'color' => '#3b82f6',
            'icon' => 'cil-code',
        ]);

        $news = Category::create([
            'name' => '新聞',
            'slug' => 'news',
            'description' => '最新新聞資訊',
            'status' => 'active',
            'order' => 2,
            'color' => '#10b981',
            'icon' => 'cil-newspaper',
        ]);

        $lifestyle = Category::create([
            'name' => '生活',
            'slug' => 'lifestyle',
            'description' => '生活相關文章',
            'status' => 'active',
            'order' => 3,
            'color' => '#f59e0b',
            'icon' => 'cil-home',
        ]);

        // 子分類 - 技術
        Category::create([
            'parent_id' => $tech->id,
            'name' => 'Laravel',
            'slug' => 'laravel',
            'description' => 'Laravel 框架相關',
            'status' => 'active',
            'order' => 1,
            'color' => '#ef4444',
            'icon' => 'cib-laravel',
        ]);

        Category::create([
            'parent_id' => $tech->id,
            'name' => 'Vue.js',
            'slug' => 'vuejs',
            'description' => 'Vue.js 框架相關',
            'status' => 'active',
            'order' => 2,
            'color' => '#22c55e',
            'icon' => 'cib-vue-js',
        ]);

        Category::create([
            'parent_id' => $tech->id,
            'name' => 'Database',
            'slug' => 'database',
            'description' => '資料庫相關',
            'status' => 'active',
            'order' => 3,
            'color' => '#8b5cf6',
            'icon' => 'cil-storage',
        ]);

        // 子分類 - 新聞
        Category::create([
            'parent_id' => $news->id,
            'name' => '產業動態',
            'slug' => 'industry',
            'description' => '產業最新動態',
            'status' => 'active',
            'order' => 1,
            'color' => '#06b6d4',
            'icon' => 'cil-industry',
        ]);

        Category::create([
            'parent_id' => $news->id,
            'name' => '公司公告',
            'slug' => 'announcement',
            'description' => '公司公告事項',
            'status' => 'active',
            'order' => 2,
            'color' => '#f97316',
            'icon' => 'cil-bullhorn',
        ]);

        // 子分類 - 生活
        Category::create([
            'parent_id' => $lifestyle->id,
            'name' => '美食',
            'slug' => 'food',
            'description' => '美食分享',
            'status' => 'active',
            'order' => 1,
            'color' => '#ec4899',
            'icon' => 'cil-restaurant',
        ]);

        Category::create([
            'parent_id' => $lifestyle->id,
            'name' => '旅遊',
            'slug' => 'travel',
            'description' => '旅遊記錄',
            'status' => 'active',
            'order' => 2,
            'color' => '#14b8a6',
            'icon' => 'cil-airplane-mode',
        ]);

        $this->command->info('範例分類已建立完成');
        $this->command->info('頂層分類: 技術, 新聞, 生活');
        $this->command->info('總共建立: ' . Category::count() . ' 個分類');
    }
}
