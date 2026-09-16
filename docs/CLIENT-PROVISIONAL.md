# 暫定客戶（Provisional Client）與單據客戶選擇

> 設計日期：2026-09-16

## 目錄

- [要解決的問題](#要解決的問題)
- [設計決策](#設計決策)
- [為什麼不讓 client_id 可為空](#為什麼不讓-client_id-可為空)
- [資料模型](#資料模型)
- [操作流程](#操作流程)
- [實作組成](#實作組成)
- [完全不受影響的既有功能](#完全不受影響的既有功能)
- [日後維護注意事項](#日後維護注意事項)

## 要解決的問題

報價單、合約、發票的「客戶」欄位原本都是一個純下拉選單（`required|exists:clients,id`），只能挑已建檔的客戶。實務上常遇到客戶還沒談定、不想先進客戶管理建檔，卻已經要出報價單——此時使用者必須中斷手上的表單，跳去客戶管理新增客戶，再回頭重填整張單。

**真正的痛點是「開單當下不能離開表單」，而不是「資料庫裡不該有這筆客戶」。** 這個區分決定了整個設計方向。

## 設計決策

採「**打字即建檔**」：客戶欄位改成可搜尋的下拉（Select2），找不到客戶時直接打名字即可開單，系統在背後建立一筆標記為「暫定」的客戶，`client_id` 永遠有值。

兩條建立路徑：

| 路徑 | 操作 | 建立的客戶 |
|---|---|---|
| 打字直送 | 在下拉打名字 → 點「＋ 用「xxx」開單（暫定客戶）」 | 只有 `name`，`is_provisional = true` |
| 填資料建立 | 點欄位旁按鈕開小視窗，填名稱／聯絡人／Email／電話／公司／統編 | 6 欄資料，`is_provisional` 預設 true（可於視窗內取消勾選） |

兩種路徑建立的客戶 `status` 都是 `lead`、`source` 為 `other`、`tier` 為 `standard`，與既有客戶語意一致。

## 為什麼不讓 client_id 可為空

曾評估「`client_id` 改 nullable、客戶資料以純文字存在單據上」的做法。體感上與本設計相同（都是打完名字就能開單），但代價差距極大。以下是實際盤點出的受影響範圍：

**會直接出錯**

| 位置 | 問題 |
|---|---|
| `app/Models/Invoice.php:184` | `afterPaymentsSaved()` 呼叫 `$this->client->recalculateRevenue()`，客戶為 null 會 fatal error。此處在收款主線上 |
| `app/Http/Controllers/Admin/ContractController.php:388` | 寄合約給客戶：`$contract->client_signer_email ?: $contract->client->email` |
| `resources/views/emails/contract-to-client.blade.php:17` | 信件抬頭 `$contract->client->name` |
| 三張單的 index／show 共 9 處 | `route('admin.clients.show', $x->client)` 傳 null 會拋路由例外 |
| `quotes/pdf.blade.php`、`contracts/pdf.blade.php` 共 13 處 | 客戶名稱、公司、統編、簽署欄 |

**資料庫要動**：`quotes`、`contracts`、`invoices` 三張表的 `client_id` 都是 NOT NULL 外鍵，且報價轉發票／轉合約／合約轉發票共 4 處都直接複製 `client_id`，三張表得一起改。

**功能會默默變爛**：三張單的關鍵字搜尋都走 `orWhereHas('client', ...)`，臨時客戶的單據會搜不到；合約的客戶篩選會漏；這些單據不會出現在任何客戶頁；收的錢不計入 `clients.total_revenue`，客戶營收排行與客戶頁累計營收會少算。

**營收口徑補充（重要）**：全站營收數字（Dashboard 本月／本年、發票管理頁三張卡、報表中心營收財務）走的是 `Payment::forInvoices()->sum('amount')`，與客戶無關，不會因此失真。只有「錢算在哪個客戶頭上」會漏。

相較之下，暫定客戶方案只加一個欄位，上述所有位置一處都不用改。

## 資料模型

```
clients
├── is_provisional  boolean, default false, index   ← 本次新增
└── status          enum(lead/active/inactive/archived)  ← 既有，語意不同
```

`is_provisional` 與 `status` 是兩個正交概念，刻意不合併：

- `status` 表示「客戶與我們的關係走到哪」——潛在、活躍、停用、封存
- `is_provisional` 表示「這筆客戶資料完不完整、是不是開單當下順手建的」

兩者會交叉（一個暫定客戶也可能已經 active），硬塞進 `status` 會讓既有的狀態篩選與統計語意變髒。

## 操作流程

```
報價單／合約／發票表單
        │
        ├─ 選既有客戶 ─────────────────────────────→ 照舊
        │
        ├─ 打字找不到 → 點「＋ 用「xxx」開單」
        │        │
        │        ↓ 立即 POST clients/quick-store
        │   建立暫定客戶 → 回傳 id → 塞回 select → 送出表單帶合法 id
        │
        └─ 點旁邊按鈕 → 小視窗填 6 欄 → 同上

                        ↓ 之後談成

客戶管理
  ├─ 列表：顯示「暫定」徽章，可篩選 全部／僅正式／僅暫定
  ├─ 詳情：提示條 ＋「轉為正式客戶」按鈕（POST clients/{client}/promote）
  └─ 編輯：「暫定客戶」勾選框，取消勾選並儲存即轉正
```

### 為什麼選到新客戶就立刻建檔，而不是等表單送出

Select2 的 `tags` 產生的項目，value 是使用者輸入的文字（例如 `大東實業`）。若直接送出，後端 `required|exists:clients,id` 會擋下；要支援就得改三個 Controller 的 store 與 update 共 6 處驗證與建立邏輯。

改成「選了就先建好、把真 id 換回 select」後，那 6 處驗證一行都不用動，而且使用者當下就看得到客戶已建立，不會按了送出才失敗、整張表單重填。

## 實作組成

| 檔案 | 內容 |
|---|---|
| `database/migrations/*_add_is_provisional_to_clients_table.php` | 加 `is_provisional` 欄位與索引 |
| `app/Models/Client.php` | fillable、cast boolean、`scopeProvisional()`／`scopeFormal()` |
| `app/Http/Controllers/Admin/ClientController.php` | `quickStore()` 回傳 JSON、`promote()` 轉正、`index()` 加暫定篩選、`update()` 接受 `is_provisional` |
| `routes/admin.php` | `clients/quick-store`、`clients/{client}/promote`，**必須放在 `Route::resource('clients')` 之前**，否則會被 `clients/{client}` 吃掉（同 `media/browse` 的排法） |
| `resources/views/admin/partials/client-select.blade.php` | 共用 partial：下拉本體 ＋ Select2 初始化 ＋ 快速建立視窗 ＋ AJAX |
| 六個表單 | quotes／contracts／invoices 的 create 與 edit，各自把原本的 `<select>` 換成 `@include` |
| `resources/views/admin/clients/index｜show｜edit.blade.php` | 暫定徽章、篩選、轉正按鈕、勾選框 |

### 前端依賴

Select2 4.1 從 CDN 載入，需搭配 jQuery 3（Select2 的硬性依賴，專案原本沒有 jQuery），另加 select2-bootstrap-5-theme 讓外觀與 CoreUI 一致。三支資源都走 `https://cdn.jsdelivr.net`，已在 `SecurityHeaders` 的 CSP `script-src`／`style-src` 白名單內，**不需要改 CSP**。

資源以 `@push('styles')`／`@push('scripts')` 放在共用 partial 內，只有 include 它的頁面才會載入，符合專案「頁面專用資源用 push」的慣例。

## 完全不受影響的既有功能

因為 `client_id` 永遠有值，以下維持原狀，不需任何修改：

- 收款流程與客戶營收重算（`Invoice.php:184`、`Client::recalculateRevenue()`）
- 寄合約信（`ContractController.php:388`、`emails/contract-to-client.blade.php`）
- 報價單／合約 PDF 的客戶名稱、公司、統編、簽署欄
- 三張單列表與詳情頁的客戶連結
- 三張單的關鍵字搜尋（`whereHas('client')`）與合約的客戶篩選
- 報價轉發票、報價轉合約、合約轉發票
- 客戶頁的報價／合約／發票清單
- Dashboard、發票管理頁、報表中心的所有營收數字與客戶營收排行

## 日後維護注意事項

1. **客戶欄位只改共用 partial**：六個表單都 include 同一份 `admin/partials/client-select.blade.php`，不要回頭在個別表單內聯 Select2 設定。此專案已有前例——項目列 JS 與服務方案面板同樣抽成共用 partial。
2. **新增與客戶相關的功能時不必特判暫定客戶**：暫定客戶就是一筆正常的 Client，差別只在 `is_provisional` 這個顯示用旗標。唯一要記得的是客戶列表的篩選預設值。
3. **暫定客戶不會自動清除**：若累積過多未使用的暫定客戶，需另行處理（可依「無任何報價／合約／發票關聯且為暫定」條件清理），目前不實作。
