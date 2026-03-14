<?php

namespace Database\Seeders;

use App\Models\PricingCategory;
use App\Models\PricingFeature;
use Illuminate\Database\Seeder;

class PricingSeeder extends Seeder
{
    public function run(): void
    {
        // 先清空既有資料避免重複
        PricingFeature::query()->delete();
        PricingCategory::query()->delete();

        $categories = [
            // ===== 網頁設計（核心業務，對齊服務方案 8K~48.6K）=====
            [
                'name' => '網頁設計',
                'slug' => 'web',
                'description' => '從一頁式到完整電商，滿足各種網站需求',
                'base_price_min' => 8000,
                'base_price_max' => 50000,
                'icon' => 'cil-globe-alt',
                'order' => 1,
                'is_active' => true,
                'features' => [
                    ['name' => 'RWD 響應式設計', 'slug' => 'responsive', 'price_min' => 3000, 'price_max' => 8000, 'description' => '適配手機、平板、桌面等裝置', 'order' => 1],
                    ['name' => 'CMS 後台管理', 'slug' => 'cms', 'price_min' => 5000, 'price_max' => 15000, 'description' => '可自行管理網站內容的後台系統', 'order' => 2],
                    ['name' => 'SEO 基礎優化', 'slug' => 'seo', 'price_min' => 3000, 'price_max' => 8000, 'description' => 'Meta 標籤、結構化資料、Sitemap', 'order' => 3],
                    ['name' => '進階 SEO 方案', 'slug' => 'seo-pro', 'price_min' => 8000, 'price_max' => 18000, 'description' => 'Schema.org、GTM、Search Console 完整設定', 'order' => 4],
                    ['name' => '聯繫/詢問表單', 'slug' => 'contact-form', 'price_min' => 2000, 'price_max' => 5000, 'description' => '表單收件 + Email 通知 + 後台管理', 'order' => 5],
                    ['name' => '部落格/新聞系統', 'slug' => 'blog', 'price_min' => 5000, 'price_max' => 12000, 'description' => '文章發布、分類、標籤、RSS', 'order' => 6],
                    ['name' => '會員系統', 'slug' => 'member', 'price_min' => 8000, 'price_max' => 20000, 'description' => '註冊/登入/個人資料/權限管理', 'order' => 7],
                    ['name' => '電商購物車', 'slug' => 'ecommerce', 'price_min' => 15000, 'price_max' => 35000, 'description' => '產品管理、購物車、訂單系統', 'order' => 8],
                    ['name' => '金流串接', 'slug' => 'payment', 'price_min' => 8000, 'price_max' => 20000, 'description' => '綠界/藍新等第三方金流服務', 'order' => 9],
                    ['name' => '多語系支援', 'slug' => 'i18n', 'price_min' => 5000, 'price_max' => 15000, 'description' => '中英文或更多語言版本切換', 'order' => 10],
                    ['name' => '預約/行事曆系統', 'slug' => 'booking', 'price_min' => 8000, 'price_max' => 20000, 'description' => '線上預約時段與通知', 'order' => 11],
                    ['name' => 'Google 服務串接', 'slug' => 'google', 'price_min' => 2000, 'price_max' => 5000, 'description' => 'Analytics、Map、Search Console', 'order' => 12],
                ],
            ],

            // ===== App 開發 =====
            [
                'name' => 'App 開發',
                'slug' => 'app',
                'description' => '原生或跨平台 App 開發',
                'base_price_min' => 80000,
                'base_price_max' => 250000,
                'icon' => 'cil-mobile',
                'order' => 2,
                'is_active' => true,
                'features' => [
                    ['name' => '使用者登入/註冊', 'slug' => 'auth', 'price_min' => 10000, 'price_max' => 25000, 'description' => '帳號系統、OAuth 社群登入', 'order' => 1],
                    ['name' => '推播通知', 'slug' => 'push', 'price_min' => 8000, 'price_max' => 20000, 'description' => 'FCM/APNs 推送與排程', 'order' => 2],
                    ['name' => '支付整合', 'slug' => 'payment', 'price_min' => 15000, 'price_max' => 35000, 'description' => 'Apple Pay/Google Pay/第三方金流', 'order' => 3],
                    ['name' => '社群互動', 'slug' => 'social', 'price_min' => 12000, 'price_max' => 30000, 'description' => '按讚、留言、分享、追蹤', 'order' => 4],
                    ['name' => '地圖/定位', 'slug' => 'map', 'price_min' => 8000, 'price_max' => 20000, 'description' => 'GPS 定位、路線規劃、地標標記', 'order' => 5],
                    ['name' => '即時通訊', 'slug' => 'chat', 'price_min' => 15000, 'price_max' => 35000, 'description' => '一對一/群組聊天、圖片傳送', 'order' => 6],
                    ['name' => '資料儀表板', 'slug' => 'dashboard', 'price_min' => 10000, 'price_max' => 25000, 'description' => '圖表視覺化、報表匯出', 'order' => 7],
                    ['name' => '離線模式', 'slug' => 'offline', 'price_min' => 8000, 'price_max' => 20000, 'description' => '本地快取、離線同步', 'order' => 8],
                ],
            ],

            // ===== 系統開發 =====
            [
                'name' => '系統開發',
                'slug' => 'system',
                'description' => '後端系統、API、雲端架構',
                'base_price_min' => 50000,
                'base_price_max' => 150000,
                'icon' => 'cil-layers',
                'order' => 3,
                'is_active' => true,
                'features' => [
                    ['name' => 'RESTful API 設計', 'slug' => 'api', 'price_min' => 10000, 'price_max' => 30000, 'description' => 'API 架構設計、文件撰寫', 'order' => 1],
                    ['name' => '資料庫設計', 'slug' => 'database', 'price_min' => 8000, 'price_max' => 25000, 'description' => '資料模型、索引優化、遷移', 'order' => 2],
                    ['name' => 'CI/CD 自動部署', 'slug' => 'cicd', 'price_min' => 8000, 'price_max' => 20000, 'description' => 'GitHub Actions/GitLab CI 自動化', 'order' => 3],
                    ['name' => '雲端部署', 'slug' => 'cloud', 'price_min' => 10000, 'price_max' => 30000, 'description' => 'AWS/GCP/Azure 架構規劃與部署', 'order' => 4],
                    ['name' => '第三方 API 整合', 'slug' => 'integration', 'price_min' => 8000, 'price_max' => 25000, 'description' => '外部服務串接與資料同步', 'order' => 5],
                    ['name' => '效能監控', 'slug' => 'monitoring', 'price_min' => 5000, 'price_max' => 15000, 'description' => '系統監控、告警、日誌分析', 'order' => 6],
                ],
            ],

            // ===== AI 應用開發（簡化為入門級項目）=====
            [
                'name' => 'AI 應用開發',
                'slug' => 'ai',
                'description' => 'AI 聊天機器人、智慧客服、自動化整合',
                'base_price_min' => 15000,
                'base_price_max' => 80000,
                'icon' => 'cil-lightbulb',
                'order' => 4,
                'is_active' => true,
                'features' => [
                    ['name' => 'AI 智慧客服機器人', 'slug' => 'chatbot', 'price_min' => 15000, 'price_max' => 35000, 'description' => '網站嵌入式 AI 客服，自動回覆常見問題', 'order' => 1],
                    ['name' => 'AI 內容生成助手', 'slug' => 'ai-content', 'price_min' => 8000, 'price_max' => 20000, 'description' => '文案/摘要/翻譯等 AI 輔助功能', 'order' => 2],
                    ['name' => 'LINE / Messenger Bot', 'slug' => 'social-bot', 'price_min' => 12000, 'price_max' => 30000, 'description' => '社群平台自動回覆機器人', 'order' => 3],
                    ['name' => 'AI 表單 / 問卷分析', 'slug' => 'ai-form', 'price_min' => 8000, 'price_max' => 18000, 'description' => '自動分類客戶需求、智慧推薦', 'order' => 4],
                ],
            ],

            // ===== UI/UX 設計（加值服務）=====
            [
                'name' => 'UI/UX 設計',
                'slug' => 'design',
                'description' => '品牌視覺、介面設計、使用者體驗',
                'base_price_min' => 5000,
                'base_price_max' => 50000,
                'icon' => 'cil-brush',
                'order' => 5,
                'is_active' => true,
                'features' => [
                    ['name' => 'Logo 設計', 'slug' => 'logo', 'price_min' => 5000, 'price_max' => 15000, 'description' => '3 款提案 + 2 次修改 + 原始檔', 'order' => 1],
                    ['name' => '名片設計', 'slug' => 'business-card', 'price_min' => 2000, 'price_max' => 5000, 'description' => '雙面設計 + 印刷稿輸出', 'order' => 2],
                    ['name' => 'UI 設計稿', 'slug' => 'ui-design', 'price_min' => 8000, 'price_max' => 25000, 'description' => '高保真視覺設計、元件規範', 'order' => 3],
                    ['name' => 'Wireframe 線框圖', 'slug' => 'wireframe', 'price_min' => 5000, 'price_max' => 12000, 'description' => '頁面架構與使用流程規劃', 'order' => 4],
                    ['name' => '互動原型', 'slug' => 'prototype', 'price_min' => 8000, 'price_max' => 18000, 'description' => 'Figma/Adobe XD 可互動原型', 'order' => 5],
                    ['name' => '品牌識別系統', 'slug' => 'branding', 'price_min' => 10000, 'price_max' => 30000, 'description' => '完整品牌色彩、字型、視覺規範', 'order' => 6],
                ],
            ],
        ];

        foreach ($categories as $catData) {
            $features = $catData['features'];
            unset($catData['features']);
            $category = PricingCategory::create($catData);

            foreach ($features as $featureData) {
                $featureData['pricing_category_id'] = $category->id;
                $featureData['is_active'] = true;
                PricingFeature::create($featureData);
            }

            $this->command->info("已建立分類：{$category->name}，共 " . count($features) . ' 個功能');
        }

        $this->command->info('');
        $this->command->info('定價資料種子建立完成！');
    }
}
