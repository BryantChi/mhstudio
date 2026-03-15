<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

class LegalPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => '隱私權政策',
                'slug' => 'privacy-policy',
                'type' => 'privacy',
                'order' => 0,
                'content' => $this->privacyContent(),
            ],
            [
                'title' => '服務條款',
                'slug' => 'terms-of-service',
                'type' => 'terms',
                'order' => 1,
                'content' => $this->termsContent(),
            ],
            [
                'title' => 'Cookie 政策',
                'slug' => 'cookie-policy',
                'type' => 'cookie',
                'order' => 2,
                'content' => $this->cookieContent(),
            ],
            [
                'title' => '免責聲明',
                'slug' => 'disclaimer',
                'type' => 'disclaimer',
                'order' => 3,
                'content' => $this->disclaimerContent(),
            ],
        ];

        foreach ($pages as $page) {
            LegalPage::firstOrCreate(
                ['slug' => $page['slug']],
                array_merge($page, ['is_active' => true, 'published_at' => now()])
            );
        }
    }

    protected function privacyContent(): string
    {
        $company = config('app.name', 'MH STUDIO');
        $year = date('Y');

        return <<<HTML
<p>生效日期：{$year} 年 3 月 15 日｜版本：1.0</p>

<h2>1. 前言與法律依據</h2>
<p>{$company}（以下簡稱「本公司」）依據中華民國《個人資料保護法》（以下簡稱「個資法」）及其施行細則，制定本隱私權政策。本政策適用於本公司透過 powerchi.com.tw 網站（以下簡稱「本網站」）所蒐集、處理及利用之個人資料。</p>
<p>本公司作為個資法第 2 條所定義之「非公務機關」，依法善盡個人資料保護之責任，並遵守個資法第 8 條之告知義務。</p>

<h2>2. 個人資料蒐集之目的與類別</h2>
<p>依個資法第 8 條第 1 項規定，本公司告知以下事項：</p>

<h3>2.1 蒐集目的（依特定目的分類）</h3>
<ul>
    <li><strong>040 行銷</strong>：電子報發送、服務推廣</li>
    <li><strong>090 消費者、客戶管理與服務</strong>：回應諮詢、報價、履行服務合約</li>
    <li><strong>136 資（通）訊與資料庫管理</strong>：網站分析、系統維運</li>
    <li><strong>148 網路購物及其他電子商務服務</strong>：線上報價、發票管理</li>
    <li><strong>157 調查、統計與研究分析</strong>：匿名化使用行為統計</li>
</ul>

<h3>2.2 蒐集之個人資料類別</h3>
<table>
<thead>
<tr><th>類別</th><th>資料項目</th><th>蒐集方式</th></tr>
</thead>
<tbody>
<tr><td>識別類（C001）</td><td>姓名、電子郵件、電話號碼、公司名稱</td><td>表單主動填寫</td></tr>
<tr><td>特徵類（C011）</td><td>偏好語言設定</td><td>系統自動記錄</td></tr>
<tr><td>社會情況（C031）</td><td>公司名稱、職稱</td><td>表單主動填寫</td></tr>
<tr><td>財務細節（C081）</td><td>專案預算範圍偏好</td><td>報價表單填寫</td></tr>
<tr><td>資訊技術類</td><td>IP 位址（已匿名化）、瀏覽器類型、裝置資訊、瀏覽紀錄</td><td>系統自動蒐集</td></tr>
</tbody>
</table>

<h2>3. 個人資料之利用期間、地區、對象及方式</h2>

<h3>3.1 利用期間</h3>
<ul>
    <li>客戶關係存續期間及終止後 5 年（依商業會計法保存年限）</li>
    <li>電子報訂閱：直至當事人取消訂閱為止</li>
    <li>網站分析資料：蒐集後保留 26 個月，逾期自動刪除</li>
    <li>聯繫訊息：處理完成後保留 2 年</li>
</ul>

<h3>3.2 利用地區</h3>
<p>中華民國境內。本公司使用之主機服務位於台灣。若涉及跨境傳輸（如使用第三方雲端服務），將依個資法第 21 條規定辦理。</p>

<h3>3.3 利用對象</h3>
<ul>
    <li>本公司及其受僱人</li>
    <li>依法有權調閱之機關（如司法機關、主管機關）</li>
    <li>本公司合法委託之技術服務供應商（如主機商），並已簽訂資料處理合約</li>
</ul>

<h3>3.4 利用方式</h3>
<p>以自動化機器或其他非自動化方式，於上述蒐集目的之必要範圍內，進行處理及利用。</p>

<h2>4. 資料安全保護措施</h2>
<p>本公司依個資法第 27 條及《個人資料檔案安全維護計畫標準》，採行以下安全措施：</p>
<ul>
    <li><strong>傳輸安全</strong>：全站啟用 SSL/TLS 加密（HTTPS），確保資料傳輸過程中不被竊取</li>
    <li><strong>存取控制</strong>：採用角色基礎存取控制（RBAC），僅授權人員得存取個人資料</li>
    <li><strong>密碼安全</strong>：使用者密碼以 Bcrypt（12 rounds）進行雜湊處理，不以明文儲存</li>
    <li><strong>IP 匿名化</strong>：網站瀏覽分析之 IP 位址於蒐集時即進行匿名化處理</li>
    <li><strong>Session 加密</strong>：使用者 Session 資料經過加密儲存</li>
    <li><strong>安全標頭</strong>：部署 CSP、X-Frame-Options、HSTS 等安全標頭防止攻擊</li>
    <li><strong>速率限制</strong>：對登入、表單提交等敏感操作實施速率限制，防止暴力攻擊</li>
    <li><strong>定期審查</strong>：定期進行安全性檢測與系統更新</li>
</ul>

<h2>5. 當事人權利行使</h2>
<p>依據個資法第 3 條，您就本公司所保有之個人資料，得行使以下權利：</p>
<ol>
    <li><strong>查詢或請求閱覽</strong>（個資法第 10 條）</li>
    <li><strong>請求製給複製本</strong>（個資法第 10 條）</li>
    <li><strong>請求補充或更正</strong>（個資法第 11 條第 1 項）</li>
    <li><strong>請求停止蒐集、處理或利用</strong>（個資法第 11 條第 2 項）</li>
    <li><strong>請求刪除</strong>（個資法第 11 條第 3 項）</li>
</ol>
<p>行使上述權利，請透過下列方式聯繫本公司，本公司將於收到申請後 30 日內回覆處理結果。本公司依個資法第 14 條得酌收必要成本費用。</p>

<h2>6. 自動化決策與分析</h2>
<p>本網站使用自行開發之匿名化瀏覽統計系統，不使用第三方追蹤工具。此統計系統不會對個人進行自動化決策或產生法律效果。</p>

<h2>7. 未成年人保護</h2>
<p>本網站之服務對象為企業客戶與成年個人。本公司不會故意蒐集未滿 18 歲之未成年人個人資料。若法定代理人發現未成年子女之個人資料遭蒐集，請立即聯繫本公司，我們將依法刪除相關資料。</p>

<h2>8. 資料外洩通報</h2>
<p>若發生個人資料外洩事件，本公司將依個資法第 12 條規定，以適當方式通知當事人，並於 72 小時內向主管機關（國家發展委員會個人資料保護專案辦公室）通報。</p>

<h2>9. 本政策之修訂</h2>
<p>本公司保留修訂本隱私權政策之權利。修訂後之政策將於本頁面公布，並更新生效日期。重大變更時，本公司將透過網站公告或電子郵件方式通知。</p>

<h2>10. 聯繫方式</h2>
<p>如您對本隱私權政策有任何疑問，或欲行使當事人權利，請透過以下方式聯繫：</p>
<ul>
    <li><strong>個資聯繫窗口</strong>：{$company} 客戶服務部</li>
    <li><strong>電子郵件</strong>：<a href="mailto:bryantchi.work@gmail.com">bryantchi.work@gmail.com</a></li>
    <li><strong>網站聯繫表單</strong>：<a href="/#contact">聯繫我們</a></li>
</ul>
<p>主管機關：國家發展委員會（<a href="https://www.ndc.gov.tw" target="_blank" rel="noopener">www.ndc.gov.tw</a>）</p>
HTML;
    }

    protected function termsContent(): string
    {
        $company = config('app.name', 'MH STUDIO');
        $year = date('Y');

        return <<<HTML
<p>生效日期：{$year} 年 3 月 15 日｜版本：1.0</p>

<h2>1. 總則</h2>
<p>{$company}（以下簡稱「本公司」）經營 powerchi.com.tw 網站（以下簡稱「本網站」），提供網站設計、系統開發、主機代管及相關技術服務。當您存取或使用本網站時，即表示您已閱讀、瞭解並同意接受本服務條款之所有內容。</p>
<p>本服務條款依中華民國法律訂定。若您不同意本條款之任何內容，請勿使用本網站。</p>

<h2>2. 定義</h2>
<ul>
    <li><strong>「使用者」</strong>：指存取或使用本網站之任何自然人或法人</li>
    <li><strong>「服務」</strong>：指本公司透過本網站或實體提供之所有技術服務</li>
    <li><strong>「內容」</strong>：指本網站上呈現之所有文字、圖片、設計、程式碼、影片及其他資料</li>
    <li><strong>「使用者內容」</strong>：指使用者透過表單、留言等方式提交之資料</li>
</ul>

<h2>3. 服務說明</h2>
<p>本網站提供以下功能：</p>
<ul>
    <li>公司服務資訊展示與方案說明</li>
    <li>作品集展示</li>
    <li>線上報價估算與報價請求提交</li>
    <li>部落格文章瀏覽</li>
    <li>電子報訂閱</li>
    <li>客戶專案管理入口</li>
</ul>
<p>透過本網站提交之報價請求、聯繫諮詢均不構成正式合約要約。正式服務合約須經雙方書面確認範圍、價格、交付時程及付款條件後方成立。</p>

<h2>4. 智慧財產權</h2>
<h3>4.1 本公司之權利</h3>
<p>本網站及其內容（包括但不限於視覺設計、版面編排、程式碼、文案、商標及標誌）之智慧財產權，歸屬本公司或其合法授權方所有，受中華民國《著作權法》、《商標法》及其他智慧財產權法律之保護。</p>

<h3>4.2 有限授權</h3>
<p>本公司授予使用者非專屬、不可轉讓之有限權利，僅供個人、非商業用途瀏覽本網站內容。未經本公司事先書面同意，不得以任何方式重製、散布、改作、公開傳輸或做其他超出合理使用範圍之利用。</p>

<h3>4.3 客戶專案之權利</h3>
<p>本公司為客戶開發之專案成果，其智慧財產權歸屬依個別服務合約約定。未另行約定者，著作財產權於客戶結清全部款項後移轉予客戶。</p>

<h2>5. 使用者義務與禁止行為</h2>
<p>使用者同意於使用本網站時，不得從事以下行為：</p>
<ul>
    <li>將本網站用於任何違反中華民國法律或國際法之目的</li>
    <li>未經授權存取本網站之伺服器、系統或資料庫</li>
    <li>上傳含有電腦病毒、惡意程式或任何破壞性程式碼之內容</li>
    <li>對本網站進行逆向工程、反編譯或試圖取得原始碼</li>
    <li>透過自動化工具（爬蟲、機器人等）大量存取本網站</li>
    <li>冒充他人身分或提供虛偽不實之資訊</li>
    <li>干擾或中斷本網站之正常運作</li>
</ul>

<h2>6. 免責聲明</h2>
<h3>6.1 資訊準確性</h3>
<p>本網站上之價格資訊、服務內容說明及技術規格僅供參考，本公司保留隨時修改之權利，且不另行通知。實際服務內容及價格以正式報價單或合約為準。</p>

<h3>6.2 服務可用性</h3>
<p>本公司不保證本網站將不間斷或無錯誤地運作。本網站可能因系統維護、升級或不可抗力因素而暫時無法使用。</p>

<h3>6.3 第三方連結</h3>
<p>本網站可能包含指向第三方網站之連結，該等連結僅為使用者方便而設。本公司不對第三方網站之內容、隱私政策或安全性負責。</p>

<h2>7. 責任限制</h2>
<p>在法律允許之最大範圍內，本公司就因使用或無法使用本網站所生之任何直接、間接、附帶、特殊或衍生性損害，不負賠償責任。前述限制不適用於本公司因故意或重大過失所致之損害。</p>

<h2>8. 個人資料保護</h2>
<p>本公司對於使用者個人資料之蒐集、處理及利用，悉依本公司之<a href="/legal/privacy-policy">隱私權政策</a>辦理，該政策構成本服務條款之一部分。</p>

<h2>9. 條款修訂</h2>
<p>本公司保留隨時修訂本服務條款之權利。修訂後之條款自公布於本網站之日起生效。建議使用者定期查閱本頁面。若於條款修訂後繼續使用本網站，視為同意修訂後之條款。</p>

<h2>10. 準據法與管轄法院</h2>
<p>本服務條款之解釋與適用，以及因本條款所生之爭議，均以中華民國法律為準據法。雙方同意以台灣台中地方法院為第一審管轄法院。</p>

<h2>11. 一般條款</h2>
<ul>
    <li><strong>條款之可分性</strong>：本條款之任一條款如經法院認定為無效或不可執行，不影響其餘條款之效力。</li>
    <li><strong>完整合意</strong>：本服務條款（含隱私權政策及 Cookie 政策）構成使用者與本公司間就本網站使用之完整合意。</li>
    <li><strong>權利之不棄</strong>：本公司未行使或延遲行使本條款所賦予之任何權利，不視為棄權。</li>
</ul>

<h2>12. 聯繫方式</h2>
<p>如有任何問題或建議，歡迎聯繫：</p>
<ul>
    <li><strong>電子郵件</strong>：<a href="mailto:bryantchi.work@gmail.com">bryantchi.work@gmail.com</a></li>
    <li><strong>網站聯繫表單</strong>：<a href="/#contact">聯繫我們</a></li>
</ul>
HTML;
    }

    protected function cookieContent(): string
    {
        $company = config('app.name', 'MH STUDIO');
        $year = date('Y');

        return <<<HTML
<p>生效日期：{$year} 年 3 月 15 日｜版本：1.0</p>

<h2>1. 什麼是 Cookie？</h2>
<p>Cookie 是網站伺服器傳送至您瀏覽器並儲存於您裝置上的小型文字檔案。Cookie 可協助網站記住您的偏好設定、維持登入狀態，並提供更好的使用體驗。類似技術包括 Local Storage 和 Session Storage。</p>

<h2>2. 法律依據</h2>
<p>{$company}（以下簡稱「本公司」）依據中華民國《個人資料保護法》之規定使用 Cookie。嚴格必要之 Cookie 基於「履行契約或事前措施所必要」（個資法第 19 條第 1 項第 2 款）處理；分析性及功能性 Cookie 基於「當事人同意」（個資法第 19 條第 1 項第 5 款）處理。</p>

<h2>3. 本網站使用的 Cookie</h2>

<h3>3.1 嚴格必要 Cookie</h3>
<p>這些 Cookie 是網站正常運作不可或缺的，無法在本網站系統中關閉。</p>
<table>
<thead>
<tr><th>Cookie 名稱</th><th>用途</th><th>存續期間</th></tr>
</thead>
<tbody>
<tr><td><code>XSRF-TOKEN</code></td><td>防止跨站請求偽造（CSRF）攻擊</td><td>Session</td></tr>
<tr><td><code>laravel_session</code></td><td>維持使用者 Session 狀態（已加密）</td><td>120 分鐘</td></tr>
<tr><td><code>locale</code></td><td>記住您選擇的語言設定</td><td>Session</td></tr>
</tbody>
</table>

<h3>3.2 功能性 Cookie / Local Storage</h3>
<p>這些用於提供增強功能和個人化體驗：</p>
<table>
<thead>
<tr><th>名稱</th><th>類型</th><th>用途</th><th>存續期間</th></tr>
</thead>
<tbody>
<tr><td><code>adminViewPref_*</code></td><td>Local Storage</td><td>記住後台管理介面的列表/卡片檢視偏好</td><td>永久（直至手動清除）</td></tr>
</tbody>
</table>

<h3>3.3 分析性 Cookie</h3>
<p>本網站使用<strong>自行開發的匿名化瀏覽統計系統</strong>，不使用 Google Analytics 或其他第三方追蹤工具。</p>
<table>
<thead>
<tr><th>技術</th><th>用途</th><th>隱私保護措施</th></tr>
</thead>
<tbody>
<tr><td>Server-side 追蹤</td><td>記錄頁面瀏覽量、來源頁面、瀏覽器類型</td><td>IP 位址於蒐集時即匿名化（末段歸零）</td></tr>
<tr><td>Session ID</td><td>識別同一瀏覽階段的多次頁面瀏覽</td><td>不與個人身分關聯，Session 結束後失效</td></tr>
</tbody>
</table>
<p>本系統不會：追蹤跨網站行為、建立使用者檔案、分享資料予第三方、使用瀏覽器指紋辨識技術。</p>

<h2>4. 第三方 Cookie</h2>
<p>本網站目前<strong>不使用</strong>第三方追蹤 Cookie。若未來引入（如 Google Analytics 或社群媒體外掛），本公司將更新本政策並提供適當的同意機制。</p>

<h2>5. 如何管理 Cookie</h2>
<p>您可以透過瀏覽器設定來管理或刪除 Cookie。請注意，停用嚴格必要 Cookie 可能導致網站無法正常運作。</p>
<p>各主要瀏覽器的 Cookie 管理說明：</p>
<ul>
    <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">Google Chrome</a></li>
    <li><a href="https://support.mozilla.org/zh-TW/kb/cookies-information-websites-store-on-your-computer" target="_blank" rel="noopener">Mozilla Firefox</a></li>
    <li><a href="https://support.apple.com/zh-tw/guide/safari/sfri11471/mac" target="_blank" rel="noopener">Apple Safari</a></li>
    <li><a href="https://support.microsoft.com/zh-tw/microsoft-edge" target="_blank" rel="noopener">Microsoft Edge</a></li>
</ul>

<h2>6. 政策更新</h2>
<p>本公司可能因技術變更或法律要求而更新本 Cookie 政策。更新後的政策將於本頁面公布，並更新生效日期。</p>

<h2>7. 聯繫我們</h2>
<p>如對本 Cookie 政策有任何疑問，請聯繫：<a href="mailto:bryantchi.work@gmail.com">bryantchi.work@gmail.com</a></p>
HTML;
    }

    protected function disclaimerContent(): string
    {
        $company = config('app.name', 'MH STUDIO');
        $year = date('Y');

        return <<<HTML
<p>生效日期：{$year} 年 3 月 15 日｜版本：1.0</p>

<h2>1. 資訊用途聲明</h2>
<p>{$company}（以下簡稱「本公司」）經營之本網站所刊載之所有內容（包括但不限於服務說明、價格資訊、技術文章、作品展示）僅供一般資訊參考之用，不構成任何形式之專業建議、要約或保證。</p>

<h2>2. 價格與服務</h2>
<ul>
    <li>本網站上顯示的價格和方案內容僅為參考，實際費用依正式報價單為準</li>
    <li>線上報價估算工具產生之金額為概估值，實際專案費用可能因需求細節而有所不同</li>
    <li>本公司保留隨時調整價格與服務內容之權利，恕不另行通知</li>
</ul>

<h2>3. 作品展示</h2>
<p>作品集中展示之專案成果，其著作權歸屬依各該專案合約約定。部分作品可能經客戶同意後刊載，展示內容可能與實際交付成品有所差異。</p>

<h2>4. 技術文章</h2>
<p>部落格文章中之技術資訊基於撰寫時的知識和技術環境，可能因技術演進而不再適用。讀者應自行評估其適用性，並參照最新的官方文件。</p>

<h2>5. 外部連結</h2>
<p>本網站可能包含指向第三方網站的連結。本公司不控制亦不負責第三方網站之內容、隱私政策或可用性。存取第三方網站之風險由使用者自行承擔。</p>

<h2>6. 可用性</h2>
<p>本公司盡力維持本網站之穩定運作，但不保證網站將不間斷、無錯誤或無安全漏洞。本公司可能因系統維護、升級或不可抗力事由而暫停服務，恕不另行通知。</p>

<h2>7. 責任限制</h2>
<p>在法律允許之最大範圍內，本公司對於使用者因信賴本網站內容或因使用/無法使用本網站所遭受之任何損失，不負賠償責任。此限制不適用於本公司因故意或重大過失所致之損害。</p>

<h2>8. 準據法</h2>
<p>本免責聲明之解釋與適用，以中華民國法律為準據法。</p>
HTML;
    }
}
