# 合約期間起算點與上線日彈性化 — 設計文件

- 日期：2026-10-08
- 狀態：設計定稿，待實作
- 範圍：拆開「執行中」與「上線」、上線日可事後修改、期間起算點可依合約選擇

## 背景與現況

9/29 起合約期間改為「自上線日起算」（commit `9e062c6`、`d05d941`、`c466e79`），目前運作方式：

1. 簽約階段填「預計交件日」＋「合約期間（月）」，起訖日先存**預估值**：預計交件日 + 緩衝天數（全域設定 `contract_go_live_buffer_days`，預設 7）視為預估上線日。
2. 實際上線的**唯一入口**是詳情頁把狀態從「已簽署」轉「執行中」：`goLiveModal` 強制填實際上線日，`ContractController::updateStatus()` 以 `Contract::termDatesFrom()` 覆寫起訖日。
3. 上線日沒有獨立欄位，只被用來算出 `start_date`。
4. `Contract::isTermEstimated()` 以狀態判斷（draft／sent／signed 視為預估）。

### 痛點

| # | 痛點 | 現況造成的問題 |
|---|---|---|
| 1 | 上線後要改上線日（填錯、延後、提前） | 只能去編輯頁手動改起訖日，系統不依月數重算 |
| 2 | 「執行中」≠「上線」 | 簽約後開發期間就想轉執行中，卻被迫當下填上線日 |
| 3 | 起算點不一定是上線日 | 有些合約要從實際交件日或自訂日期起算 |

## 核心設計決策

### 決策一：上線日是「欄位」不是「狀態」

上線日改為獨立欄位 `go_live_date`，狀態機（`STATUS_TRANSITIONS`）**完全不動**。轉「執行中」只代表開始執行，不再要求上線日。

不採用「新增『已上線』狀態」的理由：狀態機、徽章顏色、列表篩選、到期提醒查詢都得跟著改，改動面大；而上線日本質上是一個可修正的日期事實，不是流程階段。

### 決策二：期間起算點三選一，存在合約上

新增 `term_anchor`：

| 值 | 名稱 | 說明 |
|---|---|---|
| `go_live` | 上線日（預設） | 以 `go_live_date` 起算 |
| `delivery` | 實際交件日 | 以 `actual_delivery_date` 起算 |
| `custom` | 自訂日期 | 以使用者填的 `start_date` 起算（不另開欄位） |

### 決策三：「預估 vs 確定」改看起算日期是否已知

`isTermEstimated()` 由「看狀態」改為「看起算點日期有沒有填」。這是決策一的必要配套：拆開後，執行中但尚未上線的合約起訖日仍是預估值，若沿用狀態判斷會被誤標為確定。

## 資料模型

### 新增欄位（`contracts` 表）

| 欄位 | 型別 | 預設 | 用途 |
|---|---|---|---|
| `term_anchor` | string(20) | `'go_live'` | 期間起算點 |
| `go_live_date` | date，nullable | null | 實際上線日（里程碑紀錄） |
| `actual_delivery_date` | date，nullable | null | 實際交件日（里程碑紀錄） |

- 三個欄位加入 `$fillable`、`casts`（日期固定 `date:Y-m-d`，與既有 `start_date`／`end_date` 一致）。
- 三個欄位加入 activity log 的 `logOnly`，修改前後的值可查回。
- 起算點常數與標籤放在 `Contract` model（如 `TERM_ANCHORS`），供驗證規則與 Blade 下拉共用。

### 既有資料回填（migration 內以 query builder `UPDATE`，不觸發 activity log）

| 條件 | 處理 | 理由 |
|---|---|---|
| 全部合約 | `term_anchor = 'go_live'`（欄位預設值） | 與現行行為一致，不改變舊合約意義 |
| `status IN ('active','completed') AND term_months IS NOT NULL` | `go_live_date = start_date` | `term_months` 是 9/29 才加的欄位；有月數又已執行中，必定走過上線 modal，`start_date` 即上線日 |
| 執行中／已完成但 `term_months IS NULL` | 不回填 | 功能上線前的舊合約，`start_date` 為手填，無法保證等於上線日 |

`down()` 直接移除三個欄位。

## 計算規則

### 起點日期

| 起算點 | 起點日期已知（確定） | 起點日期未知（預估） |
|---|---|---|
| `go_live` | `go_live_date` | 預計交件日 + 緩衝天數 |
| `delivery` | `actual_delivery_date` | 預計交件日 + 緩衝天數 |
| `custom` | `start_date`（使用者填） | —（永遠視為確定） |

- 緩衝天數兩種起算點**共用**既有設定 `contract_go_live_buffer_days`；後台「單據條款」的說明文字改為通用說法（不限上線日）。
- 起訖日公式沿用 `Contract::termDatesFrom()`：結束日 = 起點 + N 個月 − 1 天，月底不溢位。

### `isTermEstimated()`

```text
term_months 為 null                 → false（沒有期間概念，維持現狀）
term_anchor = custom                → false
term_anchor = go_live               → go_live_date 為 null
term_anchor = delivery              → actual_delivery_date 為 null
```

### 重算時機

| 觸發 | 行為 |
|---|---|
| 詳情頁設定的日期**等於**起算點 | 依月數重算起訖日並覆寫 |
| 詳情頁設定的日期**不是**起算點 | 只存日期，起訖日完全不動 |
| 編輯頁改起算點／月數／預計交件日（go_live、delivery）或開始日期（custom） | 前端依上表帶出起訖日，仍可手動微調 |
| 沒設月數的合約 | 只把開始日改為起點日期，結束日保留（沿用現有規則） |

`fillEstimatedTermDates()`（前端失效時的後端備援）同步改為依起算點計算：`custom` 不補；`go_live`／`delivery` 起點日期已知時用已知日期，未知時用預估。

## 介面與流程

### 建立／編輯頁（`partials/term-fields.blade.php`）

- 「合約期間（月）」旁新增下拉「期間起算點」。
- JS 自動帶日期改為依起算點：
  - `go_live`／`delivery`：監聽預計交件日、月數、起算點；起點日期**已記錄**時用實際日期，否則預計交件日 + 緩衝天數。
  - `custom`：監聽開始日期、月數、起算點；只自動算結束日。
- 已記錄的 `go_live_date`／`actual_delivery_date` 以 `data-*` 屬性傳給 JS。**理由**：否則在已上線合約的編輯頁改月數時，JS 會用預估值把已確定的起訖日蓋掉。
- 說明文字改為依起算點描述，移除「在詳情頁將狀態改為『執行中』並填入上線日」的舊說法。
- 維持「只在使用者改動欄位時才重算、載入時不覆蓋」的既有原則。

### 詳情頁（`show.blade.php`）

1. **「執行中」按鈕回歸一般確認**：移除 `goLiveModal` 與按鈕迴圈中的 `active` 特例，與其他狀態相同用 `confirm()` 送出。
2. **新增里程碑按鈕**（僅 `signed`／`active` 顯示）：「設定上線日」「設定實際交件日」，已有值時文字改為「修改…」。各自一個 modal，內含日期欄位（預設值：已記錄的日期，否則今天）與說明：
   - 此日期是起算點、且有月數 → 「將以此日期起算 N 個月，覆寫目前起訖日（X ~ Y）」
   - 此日期是起算點、但沒有月數 → 「只會把開始日期改為此日期，結束日期維持不變（Y）」
   - 此日期不是起算點 → 「僅記錄日期，不影響合約期間（目前起算點：○○）」
3. **資訊表格**：
   - 「合約期間」依起算點顯示：`自上線日起 N 個月`／`自實際交件日起 N 個月`／`自訂起算 N 個月`
   - 新增「實際上線日」「實際交件日」兩列（未設定顯示 `-`）
   - 「預估」徽章沿用，判斷改用新版 `isTermEstimated()`

### PDF（`pdf.blade.php`）

第 148 行「（預估；實際以上線日起算 N 個月）」改為依起算點顯示（上線日／實際交件日）。`custom` 不會是預估，不會進到此分支。

## 後端

### 新路由與 action

```text
PUT contracts/{contract}/milestone-date → ContractController::updateMilestoneDate()
name: admin.contracts.update-milestone-date（放在 Route::resource 之前）
```

- 驗證：`field` 必填且 `in:go_live_date,actual_delivery_date`；`date` 必填 `date`。
- 狀態不是 `signed`／`active` → `flash_error` 並導回詳情頁，不寫入。
- 寫入該日期；若 `field` 對應目前的 `term_anchor`，以 `termDatesFrom()` 重算起訖日（無月數時只改開始日）。
- 導回詳情頁並 `flash_success`。

### 既有程式調整

| 位置 | 調整 |
|---|---|
| `updateStatus()` | 移除 `go_live_date` 驗證與 `active` 時的起訖日重算 |
| `store()`／`update()` | 驗證規則加入 `term_anchor`（`in:` 三個值）；寫入該欄位 |
| `duplicate()` | 一併清空 `go_live_date`、`actual_delivery_date`；`term_anchor` 沿用來源合約 |
| `Contract::estimatedTermDates()` | 維持簽名不變（兩種起算點共用同一預估公式） |

## 不在本次範圍

- **清除已記錄的上線日／交件日**：只能修改日期，不能清空（YAGNI，真的遇到再加）。
- **`scopeExpiringSoon`**：不動，仍依 `end_date` 撈已簽署／執行中合約；預估結束日離今天很遠，不會誤觸發。
- **每份合約各自的緩衝天數**：維持全域設定。
- **已完成／已取消合約的事後更正**：鎖住不開放。

## 邊界情況

- 補登很久以前的起點日期，導致結束日早於今天：照算不擋，屬事實資料。
- 起算點從 `go_live` 改為 `custom`：編輯頁開始日期欄位保留目前值，使用者自行調整；送出後即視為確定。
- 起算點改為某個已記錄日期的類型（例如改成 `delivery` 且已記錄交件日）：前端以已記錄日期重算。

## 測試（延伸 `tests/Feature/ContractTermTest.php`）

每個測試須在業務規則被改壞時失敗：

1. 三種起算點的 `isTermEstimated()`：執行中但未上線（`go_live`、`go_live_date` 為 null）仍為預估；`custom` 永遠確定。
2. 設定的日期等於起算點 → 起訖日依月數重算；不等於起算點 → 起訖日完全不變，只存日期。
3. `draft`／`sent`／`completed`／`cancelled` 合約呼叫 `updateMilestoneDate` 被拒，日期不寫入。
4. 轉「執行中」不需要上線日，且起訖日不變。
5. 沒設月數的合約設定起點日期：只改開始日，結束日保留。
6. 複製合約時兩個里程碑日期被清空、`term_anchor` 沿用。
7. `fillEstimatedTermDates()` 備援：`custom` 不補值；`go_live` 已知上線日時用實際日期。

驗證流程：`php artisan test` 全綠；改過的 Blade 對編譯產物跑 `php -l`（`view:cache` 的 success 不可信）。
