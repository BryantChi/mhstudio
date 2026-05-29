# 合約產生發票 — 設計文件

- 日期:2026-05-29
- 狀態:設計定稿,待實作
- 範圍:讓「合約 (Contract)」能產生「發票 (Invoice)」,並支援收款連動開票

## 背景與現況

目前合約與發票在系統中是兩個**完全獨立**的財務單據:

- `Contract` 與 `Invoice` 各自使用 `HasPayments` trait,各有獨立的收款帳本(多型 `payments` 表)。
- 唯一能產生發票的途徑是報價單轉換 `QuoteController::convertToInvoice()`(Quote → Invoice)。
- `Contract` 沒有任何產生發票的方法;`Invoice` 沒有 `contract_id`。
- 合約登記收款(`ContractController::recordPayment()`)只記在合約自己的帳本,不會產生任何發票。

需求:合約要能開立發票,且要「夠靈活」——綜合「手動開票」「分期/里程碑多張發票」「收款連動開票」三種情境。

## 核心設計決策

### 決策一:收款唯一真實來源 = 合約帳本(不重複計算)

系統將出現兩類發票:

| 發票類型 | 來源 | 收款記在哪 |
|---|---|---|
| 獨立發票 | `Quote→Invoice` 或直接建立(`contract_id = null`) | 發票自己的 `HasPayments` 帳本(現狀不動) |
| 合約發票 | 本次新增(`contract_id` 有值) | **不自成帳本;收款以合約為唯一真實來源** |

選定理由:

1. **零重複風險** — 全系統只有一處加總合約收款(`Contract` 的 `HasPayments` 帳本),合約發票不會把同一筆錢算第二次。
2. **改動最小** — 完全不動現有合約收款邏輯與獨立發票收款邏輯,只新增「開立發票」這一層。
3. **符合實務** — 有合約時合約才是金流真相;合約發票是開給客戶請款/報帳用的文件。

代價:合約發票的「已付狀態」由它對應的合約收款**鏡像顯示**,而非發票自己收款。此取捨優於「兩套帳本對帳」。

被否決的替代方案(方案 A):收款一律改記在發票、合約看旗下發票加總。否決原因:需推翻並遷移現有合約直接收款流程(已建立的核心流程),改動過大。

### 決策二:超開「警告但允許」

一個合約可被開出多張發票。當「已開發票總額 + 本次開票額」超過合約 `total` 時,顯示 flash 警告提示已超出合約金額,但仍允許建立發票(適合追加情境)。

### 決策三:收款連動開票為「選用」,且可事後補處理

登記收款時未勾選連動開票,事後仍可手動從合約開票,或將既有收款補綁到後來開的發票。

## 資料模型變更

```
Contract  ──hasMany──▶  Invoice
Invoice.contract_id      nullable, FK → contracts
Payment.invoice_id       nullable, FK → invoices
```

1. **Migration**
   - `invoices` 新增 `contract_id`(nullable, FK → `contracts.id`, `nullOnDelete`)。null = 獨立發票。
   - `payments` 新增 `invoice_id`(nullable, FK → `invoices.id`, `nullOnDelete`)。標記「這筆合約收款對應到哪張請款發票」。
   - `Payment.payable` 仍指向 `Contract`(真實來源不變);`invoice_id` 僅為對應標記,不影響合約 `paid_amount` 加總。

2. **Contract model**
   - 新增 `invoices(): HasMany`。
   - 新增 accessor `invoiced_amount`(旗下發票 `total` 加總)。
   - 新增 accessor `uninvoiced_amount`(= `total - invoiced_amount`,可為負,UI 自行判斷顯示)。
   - 將 `contract_id` 加入 `Invoice` 的 `$fillable`。

3. **Invoice model**
   - 新增 `contract(): BelongsTo`。

## 功能一:合約手動開票(4 種金額模式)

合約 show 頁新增「開立發票」面板,選一種模式產生發票:

| 模式 | 金額計算 | 項目 |
|---|---|---|
| 自訂金額 | 使用者輸入 | 單一項目(描述預設為合約標題,可改) |
| 按比例 | 合約 `total` × 輸入 % | 單一項目 |
| 剩餘未開全額 | `total - invoiced_amount` | 單一項目 |
| 複製合約所有項目 | 各 `ContractItem.amount` 加總 | 逐項複製 `ContractItem → InvoiceItem` |

- 產出發票:`status = draft`、`contract_id` 帶入、`paid_amount = 0`,建立後導向發票編輯頁。
- 金額/稅率:沿用合約 `tax_rate`,以 `Invoice::recalculate()` 重算 `subtotal/tax_amount/total`。
- 超開時 `flash_warning` 但仍建立(決策二)。

**路由 / 控制器**
- `POST admin/contracts/{contract}/invoices` → `name=contracts.create-invoice`
- `ContractController::createInvoice(Request $request, Contract $contract)`
  - 驗證 `mode in [custom, percent, remaining, copy_items]` 及對應欄位。
  - `DB::transaction` 內建立 `Invoice` + `InvoiceItem`,重算,回傳導向。

## 功能二:收款連動開票

合約「登記收款」表單(`ContractController::recordPayment()`)新增:

- ☐ **同時為此筆款開立發票**(勾選才開)
- 勾選後展開「發票項目方式」下拉:
  - **摘要**:單一項目「合約 {contract_number} 款項」,金額 = 收款額
  - **自訂描述**:單一項目,描述取表單輸入的「發票描述」欄(留空則用摘要預設)
  - **複製合約項目(等比縮放)**:複製所有 `ContractItem`,每項金額 × (收款額 / 合約 total)

行為:
- 在現有 `recordPayment` 的同一交易內,先記合約收款(現狀),再依勾選建立發票:
  - `contract_id` 帶入、`status = paid`、`paid_amount = total = 收款額`
  - 將該 `Payment.invoice_id` 指向新建發票
- 不勾選 → 維持現狀,只記合約帳本。

**表單欄位新增**:`create_invoice`(bool)、`invoice_item_mode`(摘要/自訂/複製)、`invoice_description`(自訂模式用)。

## 呈現變更

- **合約 show 頁**:
  - 新增「相關發票」清單(發票號、金額、狀態、連結)。
  - 顯示「已開發票總額 / 合約總額 / 可開餘額」。
- **發票 show 頁**:當 `contract_id` 有值時,顯示「來源合約」連結。

## 不做(YAGNI)

- 不改獨立發票(`contract_id = null`)的收款邏輯。
- 不做自動分期排程 / 定時開票。
- 不做發票作廢時回沖合約金額的邏輯。
- 不在合約端重算/鏡像顯示「每張發票自己的部分收款」——合約發票視為「對應整筆收款的已付文件」或「未付草稿」二態。

## 測試計畫(驗證意圖)

1. **手動開票 — 4 模式金額正確**
   - 自訂/按比例/剩餘全額/複製項目,各驗證 `subtotal/tax/total` 與項目數正確,`contract_id` 與 `status=draft` 正確。
2. **剩餘全額 = total − 已開總額**
   - 先開一張部分發票,再開「剩餘全額」,驗證金額 = 合約 total − 第一張。
3. **超開警告但仍建立**
   - 已開總額已達 total 後再開,驗證發票仍建立且回傳含警告訊息。
4. **收款連動開票 — 發票已付且綁定**
   - 登記收款並勾選連動,驗證:新發票 `status=paid`、`paid_amount=total=收款額`、`Payment.invoice_id` 指向它;合約 `paid_amount` 不因發票而重複增加。
5. **連動開票三種項目方式**
   - 摘要/自訂/複製等比,各驗證項目內容與金額。
6. **不勾選連動 = 現狀**
   - 只記合約收款,不產生發票。
7. **獨立發票不受影響**
   - `Quote→Invoice` 流程與其自身收款帳本行為不變。

## 影響檔案(預估)

- `database/migrations/*_add_contract_id_to_invoices.php`(新)
- `database/migrations/*_add_invoice_id_to_payments.php`(新)
- `app/Models/Contract.php`(關聯 + accessor)
- `app/Models/Invoice.php`(關聯 + fillable)
- `app/Http/Controllers/Admin/ContractController.php`(`createInvoice()` + `recordPayment()` 擴充)
- `routes/admin.php`(新增 create-invoice 路由)
- `resources/views/admin/contracts/show.blade.php`(開票面板 + 相關發票清單 + 收款表單擴充)
- `resources/views/admin/invoices/show.blade.php`(來源合約連結)
- `tests/`(對應上述測試)
