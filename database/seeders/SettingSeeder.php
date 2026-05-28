<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

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
                'value' => 'MH Studio 孟衡數位 — 提供客製化網頁設計、App 開發、系統架構與 UI/UX 設計服務。台中在地團隊，免費諮詢。',
                'type' => 'string',
                'description' => '網站描述',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'general',
                'key' => 'site_keywords',
                'value' => '網頁設計, App開發, 網站製作, 系統開發, UI設計, UX設計, 台中網頁設計, MH Studio, 孟衡數位',
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
                'value' => 'MH Studio 孟衡數位 — 提供客製化網頁設計、App 開發、系統架構與 UI/UX 設計服務。以 Balance · Precision · Innovation 為核心理念，為企業打造高品質數位產品。台中在地團隊，免費諮詢。',
                'type' => 'string',
                'description' => 'SEO 預設描述',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'seo_default_keywords',
                'value' => '網頁設計, App開發, 網站製作, 系統開發, UI設計, UX設計, 台中網頁設計, Laravel, WordPress, 客製化網站, RWD響應式, SEO優化, MH Studio, 孟衡數位',
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

            // SEO 設定
            [
                'group' => 'seo',
                'key' => 'default_meta_title',
                'value' => 'MH Studio 孟衡 | 網頁設計·App開發·系統架構 | 台中',
                'type' => 'string',
                'description' => '預設 Meta 標題',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'default_meta_description',
                'value' => 'MH Studio 孟衡數位 — 提供客製化網頁設計、App 開發、系統架構與 UI/UX 設計服務。台中在地團隊，免費諮詢。',
                'type' => 'string',
                'description' => '預設 Meta 描述',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'default_meta_keywords',
                'value' => '網頁設計, App開發, 網站製作, 系統開發, UI設計, UX設計, 台中網頁設計, MH Studio, 孟衡數位',
                'type' => 'string',
                'description' => '預設 Meta 關鍵字',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'default_og_image',
                'value' => '',
                'type' => 'string',
                'description' => '預設 OG 社群分享圖片',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'facebook_app_id',
                'value' => '',
                'type' => 'string',
                'description' => 'Facebook App ID',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'twitter_username',
                'value' => '',
                'type' => 'string',
                'description' => 'Twitter 用戶名',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'twitter_card_type',
                'value' => 'summary_large_image',
                'type' => 'string',
                'description' => 'Twitter Card 類型',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'google_verification',
                'value' => '',
                'type' => 'string',
                'description' => 'Google Search Console 驗證碼',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'bing_verification',
                'value' => '',
                'type' => 'string',
                'description' => 'Bing Webmaster Tools 驗證碼',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'yandex_verification',
                'value' => '',
                'type' => 'string',
                'description' => 'Yandex Webmaster 驗證碼',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'enable_schema',
                'value' => '1',
                'type' => 'boolean',
                'description' => '啟用 Schema.org 標記',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'schema_type',
                'value' => 'Article',
                'type' => 'string',
                'description' => '預設 Schema 類型',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'organization_name',
                'value' => 'MH Studio 孟衡數位',
                'type' => 'string',
                'description' => 'Schema.org 組織名稱',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'organization_logo',
                'value' => '',
                'type' => 'string',
                'description' => 'Schema.org 組織 Logo',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'allow_indexing',
                'value' => '1',
                'type' => 'boolean',
                'description' => '允許搜尋引擎索引',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'auto_generate_meta',
                'value' => '1',
                'type' => 'boolean',
                'description' => '自動生成 Meta Tags',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'generate_canonical',
                'value' => '1',
                'type' => 'boolean',
                'description' => '自動生成 Canonical URL',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'sitemap_priority',
                'value' => '0.5',
                'type' => 'string',
                'description' => 'Sitemap 預設優先級',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'sitemap_changefreq',
                'value' => 'weekly',
                'type' => 'string',
                'description' => 'Sitemap 預設更新頻率',
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

            // 社群連結 — 新增平台
            [
                'group' => 'frontend',
                'key' => 'social_github_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'GitHub 連結顯示開關',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'frontend',
                'key' => 'social_linkedin_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'LinkedIn 連結顯示開關',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'frontend',
                'key' => 'social_line_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'LINE 連結顯示開關',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'frontend',
                'key' => 'line_id',
                'value' => '@mengheng.io',
                'type' => 'string',
                'description' => 'LINE 官方帳號 ID',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'frontend',
                'key' => 'line_qrcode_url',
                'value' => '',
                'type' => 'string',
                'description' => 'LINE QR Code 圖片網址',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'frontend',
                'key' => 'social_facebook',
                'value' => '#',
                'type' => 'string',
                'description' => 'Facebook 連結',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'frontend',
                'key' => 'social_facebook_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Facebook 連結顯示開關',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'frontend',
                'key' => 'social_twitter',
                'value' => '#',
                'type' => 'string',
                'description' => 'Twitter/X 連結',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'frontend',
                'key' => 'social_twitter_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Twitter/X 連結顯示開關',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'frontend',
                'key' => 'social_instagram',
                'value' => '#',
                'type' => 'string',
                'description' => 'Instagram 連結',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'frontend',
                'key' => 'social_instagram_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Instagram 連結顯示開關',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'frontend',
                'key' => 'social_youtube',
                'value' => '#',
                'type' => 'string',
                'description' => 'YouTube 連結',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'frontend',
                'key' => 'social_youtube_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'YouTube 連結顯示開關',
                'is_public' => true,
                'is_editable' => true,
            ],

            // 社群嵌入
            [
                'group' => 'frontend',
                'key' => 'social_embed_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => '首頁社群嵌入區塊開關',
                'is_public' => true,
                'is_editable' => true,
            ],
            // 首頁區塊開關
            ['group' => 'frontend', 'key' => 'section_stats_enabled', 'value' => '1', 'type' => 'boolean', 'description' => '首頁數據統計區塊開關', 'is_public' => true, 'is_editable' => true],
            ['group' => 'frontend', 'key' => 'section_services_enabled', 'value' => '1', 'type' => 'boolean', 'description' => '首頁專業服務區塊開關', 'is_public' => true, 'is_editable' => true],
            ['group' => 'frontend', 'key' => 'section_portfolio_enabled', 'value' => '1', 'type' => 'boolean', 'description' => '首頁精選作品區塊開關', 'is_public' => true, 'is_editable' => true],
            ['group' => 'frontend', 'key' => 'section_process_enabled', 'value' => '1', 'type' => 'boolean', 'description' => '首頁合作流程區塊開關', 'is_public' => true, 'is_editable' => true],
            ['group' => 'frontend', 'key' => 'section_techstack_enabled', 'value' => '1', 'type' => 'boolean', 'description' => '首頁技術棧區塊開關', 'is_public' => true, 'is_editable' => true],
            ['group' => 'frontend', 'key' => 'newsletter_enabled', 'value' => '1', 'type' => 'boolean', 'description' => '首頁電子報訂閱區塊開關', 'is_public' => true, 'is_editable' => true],

            [
                'group' => 'frontend',
                'key' => 'social_youtube_embed',
                'value' => '',
                'type' => 'string',
                'description' => 'YouTube 嵌入影片 URL',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'group' => 'frontend',
                'key' => 'social_instagram_embed',
                'value' => '',
                'type' => 'string',
                'description' => 'Instagram 嵌入貼文 URL',
                'is_public' => true,
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
                'value' => '孟衡數位',
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
            [
                'group' => 'company',
                'key' => 'bank_account_holder',
                'value' => '',
                'type' => 'string',
                'description' => '匯款戶名（PDF 顯示用）',
                'is_public' => false,
                'is_editable' => true,
            ],
            // 單據條款設定
            [
                'group' => 'document',
                'key' => 'quote_standard_terms',
                'value' => <<<'EOT'
一、付款方式
簽約金 50%，驗收完成後付尾款 50%。

二、驗收方式
1. 乙方完成製作後，甲方應於 7 個工作日內進行驗收。
2. 驗收期間如有 Bug 或功能異常，乙方應於 7 日內修正完畢。
3. 上線後 7 日內提供後台操作教育訓練。

三、保固範圍
包含程式錯誤修正、系統問題排除（不含新功能開發）。

四、修改定義
每次修改以不超過原設計 30% 為原則，超出規格另行報價。

五、注意事項
1. 以上報價有效期為 30 天。
2. 網站設計製作不含文案撰寫，如需文案服務請另行報價。
3. 網站圖片如需購買圖庫素材，費用由甲方負擔。
4. 網域名稱註冊費用不包含在本報價中（約 NT$ 800/年）。
5. 客戶需提供網站所需文字、圖片素材。
6. 本報價未含營業稅，如需開立發票另加 5% 營業稅。
EOT,
                'type' => 'text',
                'description' => '報價單「帶入標準條款」按鈕填入的內容',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'group' => 'document',
                'key' => 'quote_pdf_notes',
                'value' => <<<'EOT'
以上報價有效期為 30 天。
網站設計製作不含文案撰寫，如需文案服務請另行報價。
網站圖片如需購買圖庫素材，費用由甲方負擔。
網域名稱註冊費用不包含在本報價中。
如需多語系版本，依語系數量另行報價。
本報價未含營業稅，如需開立發票另加 5% 營業稅。
EOT,
                'type' => 'text',
                'description' => '報價單 PDF 固定備註（每行一條）',
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
        $this->command->info('設定數量: '.count($settings));
    }
}
