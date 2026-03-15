<?php

namespace Database\Seeders;

use App\Models\PricingCategory;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // 取得關聯 ID
        $catWeb = PricingCategory::where('slug', 'web')->value('id');
        $catApp = PricingCategory::where('slug', 'app')->value('id');
        $catSystem = PricingCategory::where('slug', 'system')->value('id');
        $catAi = PricingCategory::where('slug', 'ai')->value('id');
        $catDesign = PricingCategory::where('slug', 'design')->value('id');

        $services = [
            // ============================================================
            // 原有 6 個服務（含完整 content/FAQ/tech_tags + 合併 plan 資料）
            // show_on_homepage = true
            // ============================================================
            [
                'title' => '一頁式網站設計',
                'slug' => 'one-page-website',
                'type' => 'website',
                'icon' => 'cil-screen-desktop',
                'excerpt' => '快速上線的單頁式網站，適合個人品牌、活動宣傳、產品展示。含 RWD 響應式設計、基本 SEO、聯繫表單。',
                'content' => '<h3>什麼是一頁式網站？</h3><p>一頁式網站（Landing Page）將所有關鍵資訊集中在一個頁面上，讓訪客無需跳轉就能了解您的品牌或產品。簡潔、快速、直覺，是最有效率的線上名片。</p><h3>適合誰？</h3><ul><li>個人品牌 / 自由接案者</li><li>活動宣傳頁面</li><li>新產品或服務推廣</li><li>快速驗證商業想法</li></ul><h3>製作流程</h3><p>需求確認 → 版型選擇 → 內容填入 → 微調修改 → 上線交付，最快 7 個工作天完成。</p>',
                'features' => ['RWD 響應式設計', '套版設計可選版型', '基本 SEO 設定', 'Google Analytics 串接', '聯繫表單功能', 'SSL 安全憑證', '社群媒體連結', '3 次修改機會'],
                'tech_tags' => ['HTML5', 'CSS3', 'JavaScript', 'RWD'],
                'pricing_category_id' => $catWeb,
                'subtitle' => '快速上線的最佳選擇',
                'price' => 8000,
                'billing_cycle' => 'once',
                'pages_min' => 1,
                'pages_max' => 1,
                'design_method' => '套版設計',
                'revisions' => 3,
                'warranty_months' => 3,
                'work_days_min' => 7,
                'work_days_max' => 10,
                'is_featured' => false,
                'faq' => [
                    ['q' => '一頁式和多頁式有什麼差別？', 'a' => '一頁式將所有資訊放在同一頁，瀏覽動線更集中；多頁式適合內容較多、需要分類的網站。'],
                    ['q' => '可以之後升級成多頁式嗎？', 'a' => '可以！我們提供升級方案，將一頁式擴展為基礎版或 Plus 版。'],
                    ['q' => '需要自己準備什麼？', 'a' => '請準備您的文案內容（公司簡介、服務說明等）和相關圖片素材，我們也提供文案撰寫加值服務。'],
                ],
                'order' => 1,
                'is_active' => true,
                'show_on_homepage' => true,
                'items' => [
                    ['name' => '一頁式RWD響應式設計', 'type' => 'included'],
                    ['name' => '套版設計（可選擇版型）', 'type' => 'included'],
                    ['name' => '基本SEO設定', 'type' => 'included'],
                    ['name' => 'Google Analytics 安裝', 'type' => 'included'],
                    ['name' => '聯絡表單', 'type' => 'included'],
                    ['name' => 'SSL 安全憑證', 'type' => 'included'],
                    ['name' => '3次修改機會', 'type' => 'included'],
                    ['name' => '3個月保固', 'type' => 'included'],
                ],
            ],
            [
                'title' => '企業形象網站',
                'slug' => 'corporate-website',
                'type' => 'website',
                'icon' => 'cil-globe-alt',
                'excerpt' => '專業的企業官網設計，含客製化 UI/UX、後台管理系統（CMS）、完整 SEO 優化，打造品牌數位門面。',
                'content' => '<h3>為什麼需要企業形象網站？</h3><p>企業官網是品牌在網路上的第一印象。一個專業、美觀且功能完善的網站，能有效提升品牌信任度、吸引潛在客戶，並成為 24 小時不打烊的業務窗口。</p><h3>我們的設計理念</h3><p>以使用者體驗為核心，結合品牌識別設計，打造既美觀又好用的企業官網。每個網站都經過完整的 RWD 適配測試，確保在各種裝置上都有最佳表現。</p><h3>後台管理系統</h3><p>內建直覺化的 CMS 後台，讓您輕鬆管理網站內容，不需要技術背景也能自行更新文字和圖片。</p>',
                'features' => ['客製化 UI/UX 設計', 'RWD 全裝置適配', '後台管理系統（CMS）', 'SEO 搜尋引擎優化', 'Google Analytics + Search Console', '聯繫表單 + Google Map', '社群媒體整合', '基本動態效果'],
                'tech_tags' => ['Laravel', 'Bootstrap', 'MySQL', 'JavaScript', 'SCSS'],
                'pricing_category_id' => $catWeb,
                'subtitle' => '功能完整的專業方案',
                'price' => 37500,
                'billing_cycle' => 'once',
                'pages_min' => 6,
                'pages_max' => 10,
                'design_method' => '客製化設計',
                'special_features_count' => 3,
                'cms_modules_count' => 5,
                'revisions' => 8,
                'warranty_months' => 6,
                'work_days_min' => 25,
                'work_days_max' => 35,
                'is_featured' => true,
                'faq' => [
                    ['q' => '製作時間大概多久？', 'a' => '視方案而定，基礎版約 15-20 個工作天，Plus 版約 25-35 個工作天。'],
                    ['q' => '可以自己更新網站內容嗎？', 'a' => '當然！我們提供完整的 CMS 後台系統，並在上線後提供教育訓練。'],
                    ['q' => '網站上線後有保固嗎？', 'a' => '有的，基礎版提供 6 個月保固，Plus/Pro 版則視方案不同提供 6-12 個月保固。'],
                    ['q' => '可以串接第三方服務嗎？', 'a' => '可以，如 Google 地圖、社群登入、金流、電子報訂閱等，依需求另行報價。'],
                ],
                'order' => 2,
                'is_active' => true,
                'show_on_homepage' => true,
                'items' => [
                    ['name' => '6-10頁RWD響應式設計', 'type' => 'included'],
                    ['name' => '客製化UI/UX設計', 'type' => 'highlighted'],
                    ['name' => '完整SEO優化方案', 'type' => 'included'],
                    ['name' => 'Google Analytics + Search Console + Tag Manager', 'type' => 'included'],
                    ['name' => '聯絡表單 + Google Map', 'type' => 'included'],
                    ['name' => 'SSL 安全憑證', 'type' => 'included'],
                    ['name' => '社群媒體連結整合', 'type' => 'included'],
                    ['name' => '進階動態效果與互動設計', 'type' => 'highlighted'],
                    ['name' => '後台管理系統（CMS）含5個模組', 'type' => 'highlighted'],
                    ['name' => '3項特殊功能開發', 'type' => 'highlighted'],
                    ['name' => '8次修改機會', 'type' => 'included'],
                    ['name' => '6個月保固', 'type' => 'included'],
                ],
            ],
            [
                'title' => '電商網站開發',
                'slug' => 'ecommerce-website',
                'type' => 'website',
                'icon' => 'cil-dollar',
                'excerpt' => '完整的線上購物網站方案，含產品管理、購物車、訂單系統、金流串接，讓您輕鬆開設網路商店。',
                'content' => '<h3>一站式電商解決方案</h3><p>從產品上架、購物車結帳到金流串接，我們提供完整的電商網站開發服務。無論是小型精品店或中型品牌電商，都能找到合適的方案。</p><h3>核心功能</h3><ul><li>產品管理：分類、庫存、規格、圖片管理</li><li>購物車系統：加入購物車、數量調整、結帳流程</li><li>訂單管理：訂單狀態追蹤、出貨通知</li><li>金流串接：綠界 / 藍新等主流金流服務</li><li>會員系統：註冊登入、訂單記錄、收藏清單</li></ul>',
                'features' => ['產品管理系統', '購物車與結帳', '訂單管理後台', '金流串接（綠界/藍新）', '會員系統', '庫存管理', 'SEO 優化', '行動優先設計'],
                'tech_tags' => ['Laravel', 'Vue.js', 'MySQL', 'ECPay', 'NewebPay'],
                'pricing_category_id' => $catWeb,
                'subtitle' => '頂級客製化方案',
                'price' => 48600,
                'price_label' => 'NT$ 48,600 起',
                'billing_cycle' => 'once',
                'design_method' => '全客製化設計',
                'revisions' => null,
                'warranty_months' => 12,
                'work_days_min' => 35,
                'work_days_max' => 50,
                'is_featured' => false,
                'faq' => [
                    ['q' => '支援哪些金流？', 'a' => '支援綠界（ECPay）、藍新（NewebPay）等主流台灣金流服務，含信用卡、ATM、超商代碼等。'],
                    ['q' => '可以串接物流嗎？', 'a' => '可以，超商取貨、宅配等物流串接依需求另行報價。'],
                    ['q' => '有沒有行銷功能？', 'a' => '可加入優惠券、促銷活動、電子報訂閱等行銷模組。'],
                ],
                'order' => 3,
                'is_active' => true,
                'show_on_homepage' => true,
                'items' => [
                    ['name' => '不限頁面數RWD響應式設計', 'type' => 'highlighted'],
                    ['name' => '全客製化UI/UX設計', 'type' => 'highlighted'],
                    ['name' => '完整SEO優化方案 + 結構化資料', 'type' => 'included'],
                    ['name' => 'Google Analytics + Search Console + Tag Manager', 'type' => 'included'],
                    ['name' => '聯絡表單 + Google Map + 線上預約', 'type' => 'included'],
                    ['name' => 'SSL 安全憑證', 'type' => 'included'],
                    ['name' => '社群媒體全方位整合', 'type' => 'included'],
                    ['name' => '高級動態效果與互動設計', 'type' => 'highlighted'],
                    ['name' => '後台管理系統（CMS）不限模組', 'type' => 'highlighted'],
                    ['name' => '不限特殊功能開發', 'type' => 'highlighted'],
                    ['name' => '多國語言支援', 'type' => 'highlighted'],
                    ['name' => 'API 串接服務', 'type' => 'highlighted'],
                    ['name' => '效能優化與快取機制', 'type' => 'included'],
                    ['name' => '不限次數修改', 'type' => 'highlighted'],
                    ['name' => '12個月保固', 'type' => 'highlighted'],
                    ['name' => '專屬客服支援', 'type' => 'highlighted'],
                ],
            ],
            [
                'title' => 'App 行動應用開發',
                'slug' => 'app-development',
                'icon' => 'cil-mobile',
                'excerpt' => '跨平台 App 開發服務，支援 iOS 與 Android，從規劃、設計到上架，一站式完成您的行動應用需求。',
                'content' => '<h3>打造您的專屬 App</h3><p>使用 Flutter 或 React Native 等跨平台技術，一次開發同時支援 iOS 和 Android 雙平台，降低開發成本、加快上市時間。</p><h3>開發流程</h3><p>需求分析 → UI/UX 設計 → 功能開發 → 測試除錯 → 上架發布 → 維護更新</p><h3>適合的應用情境</h3><ul><li>品牌會員 App</li><li>預約/訂位系統</li><li>內部管理工具</li><li>社群互動平台</li></ul>',
                'features' => ['iOS + Android 雙平台', '跨平台開發（Flutter/RN）', 'UI/UX 介面設計', '推播通知', 'API 串接', '上架協助', '後續維護支援'],
                'tech_tags' => ['Flutter', 'React Native', 'Firebase', 'REST API'],
                'pricing_category_id' => $catApp,
                'faq' => [
                    ['q' => '原生 App 和跨平台有什麼差別？', 'a' => '跨平台（如 Flutter）一次開發支援雙系統，成本較低；原生開發效能最佳但需分開開發，費用較高。'],
                    ['q' => 'App 上架需要什麼費用？', 'a' => 'Apple 開發者帳號年費約 US$99，Google Play 一次性 US$25，這些費用由客戶負擔。'],
                ],
                'order' => 4,
                'is_active' => true,
                'show_on_homepage' => true,
            ],
            [
                'title' => 'AI 智慧應用',
                'slug' => 'ai-applications',
                'icon' => 'cil-lightbulb',
                'excerpt' => 'AI 聊天機器人、智慧客服、內容生成助手，用 AI 提升您的業務效率與客戶體驗。',
                'content' => '<h3>讓 AI 為您的業務加值</h3><p>不需要複雜的技術背景，我們幫您將 AI 技術落地到實際業務中。從智慧客服到內容生成，讓 AI 成為您最好的數位助手。</p><h3>熱門 AI 應用</h3><ul><li><strong>AI 智慧客服</strong>：24 小時自動回覆客戶常見問題，降低人力成本</li><li><strong>內容生成助手</strong>：自動生成文案、摘要、翻譯，提升內容產出效率</li><li><strong>LINE / Messenger Bot</strong>：社群平台自動回覆，即時服務客戶</li><li><strong>AI 表單分析</strong>：自動分類客戶需求，智慧推薦合適方案</li></ul>',
                'features' => ['AI 智慧客服機器人', '內容自動生成', 'LINE Bot 整合', '智慧表單分析', '簡易後台管理', '使用量報表'],
                'tech_tags' => ['OpenAI', 'Claude API', 'LINE SDK', 'Python', 'Laravel'],
                'pricing_category_id' => $catAi,
                'faq' => [
                    ['q' => 'AI 客服能回答哪些問題？', 'a' => '可以設定您的產品資訊、服務說明、常見問題等知識庫，AI 會根據內容自動回答。'],
                    ['q' => '需要持續付費嗎？', 'a' => '開發費用為一次性，但 AI API 使用量（如 OpenAI）會產生月費，通常每月數百到數千元不等。'],
                    ['q' => 'AI 回答會不會出錯？', 'a' => '我們會設定安全邊界和回答範圍，確保 AI 不會亂答。對於無法回答的問題，會自動轉接真人客服。'],
                ],
                'order' => 5,
                'is_active' => true,
                'show_on_homepage' => true,
            ],
            [
                'title' => '網站維護與代管',
                'slug' => 'hosting-maintenance',
                'icon' => 'cil-cloud',
                'excerpt' => '穩定的主機代管、每日備份、安全監控，加上定期維護更新服務，讓您的網站安心無憂地運作。',
                'content' => '<h3>專業的網站託管服務</h3><p>網站上線只是開始，持續的維護與代管才能確保網站穩定運作。我們提供主機代管和網站維護的完整方案，讓您專注在業務發展上。</p><h3>主機代管方案</h3><ul><li>高效能雲端主機</li><li>SSL 安全憑證（免費續約）</li><li>每日自動備份</li><li>99.9% 正常運作保證</li><li>24/7 系統監控</li></ul><h3>網站維護服務</h3><ul><li>系統安全更新</li><li>內容修改支援</li><li>Bug 修復</li><li>效能優化</li></ul>',
                'features' => ['高效能雲端主機', 'SSL 憑證免費續約', '每日自動備份', '99.9% 正常運作保證', '系統安全更新', '內容修改支援', 'Bug 修復', '技術諮詢服務'],
                'tech_tags' => ['Linux', 'Nginx', 'MySQL', 'SSL', 'CloudFlare'],
                'price_range' => '主機 NT$ 3,500/年 · 維護 NT$ 3,000/年起',
                'faq' => [
                    ['q' => '不維護會怎樣？', 'a' => '長期不更新可能導致安全漏洞、瀏覽器相容性問題、網站速度變慢等，建議至少做基本維護。'],
                    ['q' => '可以只買主機不買維護嗎？', 'a' => '可以，主機代管和維護服務是分開計價的，您可以依需求選擇。'],
                    ['q' => '現有的網站可以搬過來嗎？', 'a' => '可以，我們提供網站搬家服務，費用視複雜度而定。'],
                ],
                'order' => 6,
                'is_active' => true,
                'show_on_homepage' => true,
            ],

            // ============================================================
            // 新增：技術顧問服務
            // show_on_homepage = true
            // ============================================================
            [
                'title' => '技術顧問服務',
                'slug' => 'tech-consulting',
                'type' => 'consulting',
                'icon' => 'cil-chat-bubble',
                'excerpt' => '以小時計費的專業技術諮詢，涵蓋 AI 導入評估、技術架構諮詢、系統效能分析等，協助您做出最佳技術決策。',
                'content' => '<h3>專業技術諮詢服務</h3><p>無論您是在評估新技術導入、需要架構設計建議，或是想要優化現有系統，我們提供專業的技術顧問服務，以小時計費，彈性又經濟。</p><h3>諮詢範圍</h3><ul><li><strong>AI 導入評估</strong>：評估 AI 技術在您業務中的應用可能性與 ROI</li><li><strong>技術架構諮詢</strong>：系統架構設計、技術選型建議</li><li><strong>系統效能分析</strong>：找出效能瓶頸，提供優化方案</li><li><strong>第三方服務選型</strong>：金流、物流、雲端服務等比較與推薦</li><li><strong>專案可行性評估</strong>：需求分析、時程估算、預算規劃</li><li><strong>團隊教育訓練</strong>：客製化技術培訓課程</li></ul>',
                'features' => ['AI 導入評估', '技術架構諮詢', '系統效能分析', '第三方服務選型', '專案可行性評估', '團隊教育訓練'],
                'tech_tags' => ['Consulting', 'AI', 'Architecture', 'DevOps'],
                'subtitle' => '以小時計費的專業技術諮詢',
                'price' => 2000,
                'price_label' => 'NT$ 2,000~3,000/hr',
                'billing_cycle' => 'hourly',
                'faq' => [
                    ['q' => '諮詢費用怎麼算？', 'a' => '基本費率 NT$ 2,000/hr，AI 相關或特殊專業領域 NT$ 3,000/hr，以半小時為最小計費單位。'],
                    ['q' => '可以線上諮詢嗎？', 'a' => '可以，支援 Google Meet / Zoom 線上會議，也可以到貴公司現場諮詢。'],
                    ['q' => '諮詢後會有書面報告嗎？', 'a' => '是的，每次諮詢後會提供書面建議摘要，完整技術報告另行報價。'],
                ],
                'order' => 7,
                'is_active' => true,
                'show_on_homepage' => true,
                'items' => [
                    ['name' => '一對一技術諮詢', 'type' => 'included'],
                    ['name' => '書面建議摘要', 'type' => 'included'],
                    ['name' => '線上/現場皆可', 'type' => 'included'],
                    ['name' => 'AI 導入可行性評估', 'type' => 'highlighted'],
                    ['name' => '技術架構設計建議', 'type' => 'highlighted'],
                    ['name' => '完整技術報告', 'type' => 'optional'],
                ],
            ],

            // ============================================================
            // 原 ServicePlan 純定價服務（無對應 Service 的方案）
            // show_on_homepage = false
            // ============================================================
            [
                'title' => '基礎版',
                'slug' => 'basic',
                'type' => 'website',
                'excerpt' => '適合小型企業、工作室、個人網站',
                'subtitle' => '專業網站的起點',
                'price' => 27500,
                'billing_cycle' => 'once',
                'pages_min' => 3,
                'pages_max' => 5,
                'design_method' => '客製化設計',
                'revisions' => 5,
                'warranty_months' => 6,
                'work_days_min' => 15,
                'work_days_max' => 20,
                'is_featured' => false,
                'order' => 101,
                'is_active' => true,
                'show_on_homepage' => false,
                'items' => [
                    ['name' => '3-5頁RWD響應式設計', 'type' => 'included'],
                    ['name' => '客製化UI/UX設計', 'type' => 'highlighted'],
                    ['name' => '進階SEO優化', 'type' => 'included'],
                    ['name' => 'Google Analytics + Search Console', 'type' => 'included'],
                    ['name' => '聯絡表單 + Google Map', 'type' => 'included'],
                    ['name' => 'SSL 安全憑證', 'type' => 'included'],
                    ['name' => '社群媒體連結整合', 'type' => 'included'],
                    ['name' => '基本動態效果', 'type' => 'included'],
                    ['name' => '後台管理系統（CMS）', 'type' => 'highlighted'],
                    ['name' => '5次修改機會', 'type' => 'included'],
                    ['name' => '6個月保固', 'type' => 'included'],
                ],
            ],
            [
                'title' => '主機代管服務',
                'slug' => 'hosting',
                'type' => 'hosting',
                'excerpt' => '穩定可靠的主機代管方案',
                'price' => 3500,
                'billing_cycle' => 'yearly',
                'order' => 102,
                'is_active' => true,
                'show_on_homepage' => false,
                'items' => [
                    ['name' => '高效能雲端主機', 'type' => 'included'],
                    ['name' => 'SSL 安全憑證（免費續約）', 'type' => 'included'],
                    ['name' => '每日自動備份', 'type' => 'included'],
                    ['name' => '99.9% 主機正常運行保證', 'type' => 'highlighted'],
                    ['name' => '流量監控與異常通知', 'type' => 'included'],
                    ['name' => '技術支援（Email）', 'type' => 'included'],
                ],
            ],
            [
                'title' => '基礎版維護',
                'slug' => 'maintenance-basic',
                'type' => 'maintenance',
                'excerpt' => '適用於一頁式網站的年度維護',
                'price' => 3000,
                'billing_cycle' => 'yearly',
                'order' => 103,
                'is_active' => true,
                'show_on_homepage' => false,
                'items' => [
                    ['name' => '系統安全更新', 'type' => 'included'],
                    ['name' => '內容小幅修改（每月2次）', 'type' => 'included'],
                    ['name' => 'Bug修復', 'type' => 'included'],
                    ['name' => '技術諮詢支援', 'type' => 'included'],
                ],
            ],
            [
                'title' => 'Plus/Pro版維護',
                'slug' => 'maintenance-plus-pro',
                'type' => 'maintenance',
                'excerpt' => '適用於基礎版以上方案的年度維護',
                'price' => 0,
                'price_label' => '網站製作費用10%/年',
                'billing_cycle' => 'yearly',
                'order' => 104,
                'is_active' => true,
                'show_on_homepage' => false,
                'items' => [
                    ['name' => '系統安全更新與版本升級', 'type' => 'included'],
                    ['name' => '內容修改（每月5次）', 'type' => 'included'],
                    ['name' => 'Bug修復與效能優化', 'type' => 'included'],
                    ['name' => '功能微調', 'type' => 'included'],
                    ['name' => '每月維護報告', 'type' => 'highlighted'],
                    ['name' => '優先技術支援', 'type' => 'highlighted'],
                ],
            ],
            [
                'title' => 'Logo 設計',
                'slug' => 'addon-logo',
                'type' => 'addon',
                'excerpt' => '專業品牌識別設計',
                'price' => 5000,
                'price_label' => 'NT$ 5,000 起',
                'billing_cycle' => 'once',
                'order' => 105,
                'is_active' => true,
                'show_on_homepage' => false,
                'items' => [
                    ['name' => '含3款設計提案 + 2次修改', 'type' => 'included'],
                ],
            ],
            [
                'title' => '名片設計',
                'slug' => 'addon-business-card',
                'type' => 'addon',
                'excerpt' => '搭配品牌識別的名片設計',
                'price' => 2000,
                'price_label' => 'NT$ 2,000 起',
                'billing_cycle' => 'once',
                'order' => 106,
                'is_active' => true,
                'show_on_homepage' => false,
                'items' => [
                    ['name' => '雙面設計 + 印刷稿輸出', 'type' => 'included'],
                ],
            ],
            [
                'title' => '產品攝影',
                'slug' => 'addon-photography',
                'type' => 'addon',
                'excerpt' => '專業產品攝影服務',
                'price' => 8000,
                'price_label' => 'NT$ 8,000 起',
                'billing_cycle' => 'once',
                'order' => 107,
                'is_active' => true,
                'show_on_homepage' => false,
                'items' => [
                    ['name' => '含20張精修照片', 'type' => 'included'],
                ],
            ],
            [
                'title' => '文案撰寫',
                'slug' => 'addon-copywriting',
                'type' => 'addon',
                'excerpt' => '網站內容文案撰寫',
                'price' => 3000,
                'price_label' => 'NT$ 3,000 起',
                'billing_cycle' => 'once',
                'order' => 108,
                'is_active' => true,
                'show_on_homepage' => false,
                'items' => [
                    ['name' => '含SEO關鍵字優化', 'type' => 'included'],
                ],
            ],
            [
                'title' => '社群經營',
                'slug' => 'addon-social-media',
                'type' => 'addon',
                'excerpt' => '社群媒體經營管理',
                'price' => 5000,
                'price_label' => 'NT$ 5,000/月 起',
                'billing_cycle' => 'monthly',
                'order' => 109,
                'is_active' => true,
                'show_on_homepage' => false,
                'items' => [
                    ['name' => '含每月12篇貼文 + 數據分析報告', 'type' => 'included'],
                ],
            ],
        ];

        foreach ($services as $data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $service = Service::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            // 只在新建立時才建立子項目，避免覆蓋已修改的資料
            if ($service->wasRecentlyCreated && count($items) > 0) {
                foreach ($items as $index => $item) {
                    $service->items()->create([
                        'name' => $item['name'],
                        'description' => $item['description'] ?? null,
                        'type' => $item['type'] ?? 'included',
                        'order' => $index,
                        'is_active' => true,
                    ]);
                }
            }
        }

        $this->command->info('服務項目已建立完成，共 ' . count($services) . ' 個服務');
    }
}
