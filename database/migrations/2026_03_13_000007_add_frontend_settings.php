<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 前台設定 — 插入 frontend 群組設定
     */
    public function up(): void
    {
        $now = now();

        $settings = [
            // Hero 設定
            [
                'group'       => 'frontend',
                'key'         => 'hero_title',
                'value'       => 'MH STUDIO',
                'type'        => 'string',
                'description' => '首頁 Hero 標題',
                'is_public'   => true,
                'is_editable' => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'group'       => 'frontend',
                'key'         => 'hero_subtitle',
                'value'       => '孟 衡 工 作 室',
                'type'        => 'string',
                'description' => '首頁 Hero 副標題（中文）',
                'is_public'   => true,
                'is_editable' => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'group'       => 'frontend',
                'key'         => 'hero_tagline',
                'value'       => 'Balance • Precision • Innovation',
                'type'        => 'string',
                'description' => '首頁 Hero 標語',
                'is_public'   => true,
                'is_editable' => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'group'       => 'frontend',
                'key'         => 'hero_description',
                'value'       => '專注 App 開發與網頁設計，以精準技術與創新思維，為您打造超越期待的數位產品體驗。',
                'type'        => 'string',
                'description' => '首頁 Hero 描述文字',
                'is_public'   => true,
                'is_editable' => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],

            // 數據統計
            [
                'group'       => 'frontend',
                'key'         => 'stats_years_experience',
                'value'       => '7',
                'type'        => 'integer',
                'description' => '年開發經驗',
                'is_public'   => true,
                'is_editable' => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'group'       => 'frontend',
                'key'         => 'stats_projects_completed',
                'value'       => '50',
                'type'        => 'integer',
                'description' => '完成專案數',
                'is_public'   => true,
                'is_editable' => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'group'       => 'frontend',
                'key'         => 'stats_happy_clients',
                'value'       => '30',
                'type'        => 'integer',
                'description' => '滿意客戶數',
                'is_public'   => true,
                'is_editable' => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'group'       => 'frontend',
                'key'         => 'stats_ontime_delivery',
                'value'       => '99',
                'type'        => 'integer',
                'description' => '準時交付百分比',
                'is_public'   => true,
                'is_editable' => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],

            // 技術棧
            [
                'group'       => 'frontend',
                'key'         => 'tech_stack',
                'value'       => json_encode([
                    ['name' => 'Kotlin',    'type' => 'Android'],
                    ['name' => 'Flutter',   'type' => 'Cross-Platform'],
                    ['name' => 'Compose',   'type' => 'UI Framework'],
                    ['name' => 'Laravel',   'type' => 'Backend'],
                    ['name' => 'MVVM',      'type' => 'Architecture'],
                    ['name' => 'Git',       'type' => 'Version Control'],
                    ['name' => 'BLE',       'type' => 'IoT Protocol'],
                    ['name' => 'Firebase',  'type' => 'Cloud'],
                    ['name' => 'REST API',  'type' => 'Integration'],
                    ['name' => 'CI/CD',     'type' => 'DevOps'],
                    ['name' => 'SEO',       'type' => 'Marketing'],
                    ['name' => 'Figma',     'type' => 'Design'],
                ], JSON_UNESCAPED_UNICODE),
                'type'        => 'json',
                'description' => '技術棧列表（JSON 格式）',
                'is_public'   => true,
                'is_editable' => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],

            // 聯繫資訊
            [
                'group'       => 'frontend',
                'key'         => 'contact_email',
                'value'       => 'bryantchi.work@gmail.com',
                'type'        => 'string',
                'description' => '聯繫 Email',
                'is_public'   => true,
                'is_editable' => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'group'       => 'frontend',
                'key'         => 'contact_location',
                'value'       => '台中市，台灣',
                'type'        => 'string',
                'description' => '聯繫地點',
                'is_public'   => true,
                'is_editable' => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],

            // 社群連結
            [
                'group'       => 'frontend',
                'key'         => 'social_github',
                'value'       => '#',
                'type'        => 'string',
                'description' => 'GitHub 連結',
                'is_public'   => true,
                'is_editable' => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'group'       => 'frontend',
                'key'         => 'social_linkedin',
                'value'       => '#',
                'type'        => 'string',
                'description' => 'LinkedIn 連結',
                'is_public'   => true,
                'is_editable' => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'group'       => 'frontend',
                'key'         => 'social_line',
                'value'       => '#',
                'type'        => 'string',
                'description' => 'LINE 連結',
                'is_public'   => true,
                'is_editable' => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        DB::table('settings')->where('group', 'frontend')->delete();
    }
};
