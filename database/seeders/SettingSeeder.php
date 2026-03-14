<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // 一般設定
            [
                'group' => 'general',
                'key' => 'site_name',
                'value' => config('app.name'),
                'type' => 'string',
                'description' => '網站名稱',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'general',
                'key' => 'site_description',
                'value' => 'MH Studio 孟衡工作室 — 提供客製化網頁設計、App 開發、系統架構與 UI/UX 設計服務。台中在地團隊，免費諮詢。',
                'type' => 'string',
                'description' => '網站描述',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'general',
                'key' => 'site_keywords',
                'value' => '網頁設計, App開發, 網站製作, 系統開發, UI設計, UX設計, 台中網頁設計, MH Studio, 孟衡工作室',
                'type' => 'string',
                'description' => '網站關鍵字',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'general',
                'key' => 'admin_email',
                'value' => 'admin@example.com',
                'type' => 'string',
                'description' => '管理員信箱',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'general',
                'key' => 'timezone',
                'value' => 'Asia/Taipei',
                'type' => 'string',
                'description' => '時區',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'general',
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'type' => 'string',
                'description' => '日期格式',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'general',
                'key' => 'time_format',
                'value' => 'H:i:s',
                'type' => 'string',
                'description' => '時間格式',
                'is_public' => true,
                'is_editable' => true,
            ],

            // SEO 設定
            [
                'group' => 'seo',
                'key' => 'seo_default_title',
                'value' => 'MH Studio 孟衡 | 網頁設計·App開發·系統架構 | 台中',
                'type' => 'string',
                'description' => 'SEO 預設標題',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'seo_default_description',
                'value' => 'MH Studio 孟衡工作室 — 提供客製化網頁設計、App 開發、系統架構與 UI/UX 設計服務。以 Balance · Precision · Innovation 為核心理念，為企業打造高品質數位產品。台中在地團隊，免費諮詢。',
                'type' => 'string',
                'description' => 'SEO 預設描述',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'seo_default_keywords',
                'value' => '網頁設計, App開發, 網站製作, 系統開發, UI設計, UX設計, 台中網頁設計, Laravel, WordPress, 客製化網站, RWD響應式, SEO優化, MH Studio, 孟衡工作室',
                'type' => 'string',
                'description' => 'SEO 預設關鍵字',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'seo_sitemap_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '啟用 Sitemap',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'seo_robots_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '啟用 Robots.txt',
                'is_public' => false,
                'is_editable' => true,
            ],

            // 分析設定
            [
                'group' => 'analytics',
                'key' => 'analytics_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'description' => '啟用 Google Analytics',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'analytics',
                'key' => 'analytics_google_id',
                'value' => '',
                'type' => 'string',
                'description' => 'Google Analytics ID',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'analytics',
                'key' => 'analytics_track_admin',
                'value' => 'false',
                'type' => 'boolean',
                'description' => '追蹤管理員',
                'is_public' => false,
                'is_editable' => true,
            ],

            // 郵件設定
            [
                'group' => 'mail',
                'key' => 'mail_driver',
                'value' => 'smtp',
                'type' => 'string',
                'description' => '郵件驅動',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'mail',
                'key' => 'mail_from_address',
                'value' => 'noreply@example.com',
                'type' => 'string',
                'description' => '寄件者信箱',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'mail',
                'key' => 'mail_from_name',
                'value' => config('app.name'),
                'type' => 'string',
                'description' => '寄件者名稱',
                'is_public' => false,
                'is_editable' => true,
            ],

            // 上傳設定
            [
                'group' => 'upload',
                'key' => 'upload_max_size',
                'value' => '10240',
                'type' => 'integer',
                'description' => '最大上傳檔案大小 (KB)',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'upload',
                'key' => 'upload_allowed_types',
                'value' => 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx',
                'type' => 'string',
                'description' => '允許上傳的檔案類型',
                'is_public' => false,
                'is_editable' => true,
            ],

            // 公司資訊
            [
                'group' => 'company',
                'key' => 'company_name',
                'value' => 'MH Studio',
                'type' => 'string',
                'description' => '公司名稱（英文）',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'company',
                'key' => 'company_name_full',
                'value' => '孟衡工作室',
                'type' => 'string',
                'description' => '公司全名（中文）',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'company',
                'key' => 'company_owner',
                'value' => '紀孟勳',
                'type' => 'string',
                'description' => '負責人',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'company',
                'key' => 'company_phone',
                'value' => '0912-477-421',
                'type' => 'string',
                'description' => '聯絡電話',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'company',
                'key' => 'company_email',
                'value' => 'BryantChi.work@gmail.com',
                'type' => 'string',
                'description' => '公司信箱',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'company',
                'key' => 'company_address',
                'value' => '台中市西屯區漢成街75號4E',
                'type' => 'string',
                'description' => '公司地址',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'company',
                'key' => 'company_id_number',
                'value' => 'T124229077',
                'type' => 'string',
                'description' => '統一編號',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'company',
                'key' => 'company_website',
                'value' => 'mhstudio.tw',
                'type' => 'string',
                'description' => '公司網站',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'company',
                'key' => 'bank_name',
                'value' => '台新銀行',
                'type' => 'string',
                'description' => '銀行名稱',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'company',
                'key' => 'bank_code',
                'value' => '812',
                'type' => 'string',
                'description' => '銀行代碼',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'company',
                'key' => 'bank_account',
                'value' => '2888-10-0248535-5',
                'type' => 'string',
                'description' => '銀行帳號',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'company',
                'key' => 'bank_branch',
                'value' => '敦南分行（0023）',
                'type' => 'string',
                'description' => '銀行分行',
                'is_public' => false,
                'is_editable' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('系統設定已建立完成');
        $this->command->info('設定數量: ' . count($settings));
    }
}
