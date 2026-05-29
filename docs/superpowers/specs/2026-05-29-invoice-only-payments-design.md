# 發票才收錢(單一收款帳本)— 設計文件

- 日期:2026-05-29
- 狀態:設計定稿,待實作
- 範圍:把「收款」收斂為**只發生在發票(Invoice)上**的單一帳本,合約(Contract)不再直接收款;既有合約收款遷移為發票。

## 背景與動機

先前為了讓「合約能產生發票」並避免重複計算,採「合約為收款唯一真實來源、合約發票不自成帳本」的模型。此模型有一個彆扭例外(合約發票不准用自己的帳本),衍生出一連串補丁:

- `ContractController::recordContractPayment`(合約發票專用收款入口)
- `InvoiceController::recordPayment` 對合約發票的守衛
- `Invoice::linkedPayments` 關聯
- 合約發票 `paid_amount` 直接寫入鏡像(非帳本同步)
- `Payment::scopeRevenue` 排除合約發票帳本收款

且營收口徑反覆改了多次。根因是**有兩套收款帳本(Contract 與 Invoice)又把合約發票挖成例外**。

本設計改採標準會計模型:**收款只記在發票上**(單一帳本),消除例外與補丁。

## 核心決策

1. **只有 `Invoice` 收款**(唯一帳本)。`Contract` 不再 `use HasPayments`,不再有 `payable=Contract` 的收款列。
2. **所有發票規則一致**:都用自己的 `HasPayments` 帳本收款。`contract_id` 僅是「此發票來源/歸屬於哪張合約」的連結,不再代表收款特例。
3. **合約收款狀態全部由旗下發票推導**:`paid_amount = Σ 旗下發票.paid_amount`,`balance_due = total − 已收`,`paid_at`、`payment_status` 皆衍生。
4. **既有合約直接收款自動遷移為發票**(搬移不新增,金額/日期/憑證原封不動)。
5. **保留「開發票並收款」一鍵**(合約頁),內部=建立發票 + 在該發票帳本記收款,維持操作順手度,但仍是單一帳本。

## 目標模型

```
Contract ──hasMany──▶ Invoice ──HasPayments──▶ Payment (payable=Invoice)
Contract: 不再收款；paid_amount/paid_at/balance_due 由旗下發票推導
Payment.payable 一律為 Invoice
```

## 變更明細

### A. Contract model
- **移除** `use HasPayments`、`afterPaymentsSynced()`、`afterPaymentsSaved()`。
- `getPaidAmountAttribute()`:回傳 `round((float) $this->invoices()->sum('paid_amount'), 2)`(衍生)。
  - 註:`contracts.paid_amount` 資料欄位保留但不再寫入(避免破壞既有 migration / 其他讀取);accessor 以同名覆寫優先回傳衍生值。`paid_at` 同理改 accessor:旗下發票全付清且有金額時取最近一次付款日,否則 null。
- `balance_due`、`payment_status` 沿用既有公式,但以衍生 `paid_amount` 為基礎(不需改公式,只因 `paid_amount` 來源改變)。
- 保留 `invoices()`、`invoiced_amount`、`uninvoiced_amount`(現有)。

### B. Invoice model
- 維持 `use HasPayments`(唯一收款者),維持既有 `afterPaymentsSynced()`(更新 status/paid_at)、`afterPaymentsSaved()`(`client->recalculateRevenue()`)。
- **移除** `linkedPayments()` 關聯(不再需要)。
- 保留 `contract()`、`contract_id`。

### C. Payment model
- **移除** `scopeRevenue()`、`invoice()` 關聯與 `invoice_id` 用法。`payable` 一律指向 Invoice。

### D. Controllers
- `ContractController`:
  - **移除** `recordPayment()`、`destroyPayment()`(合約不再收款)。
  - **保留** `createInvoice()`(功能一「只開發票」,4 模式,產出 draft 發票,不變)。
  - **新增** `createInvoiceAndPay()`(對應「開發票並收款」一鍵):`DB::transaction` 內建立發票(項目模式:摘要/自訂/複製等比)→ 呼叫該發票 `recordPayment(amount, method, paidOn, note, proofPath)`(走發票帳本)→ 導回合約。
- `InvoiceController`:
  - `recordPayment()`:**移除**對合約發票的守衛(現在所有發票都能正常收款)。
  - **移除** `recordContractPayment()`。
  - `show()` load 移除 `linkedPayments`。
- `DashboardController`:`month_revenue` = `Invoice::paid()->whereMonth('paid_at', ...)->whereYear(...)->sum('total')`(已付發票)。
- `InvoiceController@index` 統計:`total_revenue = Invoice::paid()->sum('total')`、`month_revenue` 同上(發票基準)。
- `Client::recalculateRevenue()`:`total_revenue = $this->invoices()->where('status','paid')->sum('total')`(回到單純已付發票;不需再區分 contract_id,因為錢都在發票上、每張發票一份)。

### E. 路由(`routes/admin.php`)
- **移除** `invoices.record-contract-payment`、`contracts.record-payment`、`contracts.destroy-payment`。
- **新增** `contracts.create-invoice-and-pay`(POST `contracts/{contract}/invoice-and-pay`)。
- 保留 `contracts.create-invoice`、`invoices.record-payment`、`invoices.destroy-payment`。

### F. 視圖
- `contracts/show.blade.php`:**移除**「登記收款」Modal 與合約收款明細;保留「相關發票」卡片;「開立發票」面板新增「同時收款」選項(或獨立「開發票並收款」按鈕)導向新路由。合約財務摘要(總額/已開/已收/未收)以衍生值顯示。
- `invoices/show.blade.php`:**移除**合約發票專用的「登記收款(走合約)」卡片與唯讀 linkedPayments 明細;**所有**發票一律用既有「記錄付款」表單(自身帳本)+ 收款明細(可刪除)。保留「來源合約」連結。

### G. payments.invoice_id 欄位
- 此欄位在新模型下冗餘(`payable` 已指向發票)。遷移時將沿用它的舊資料清理(見遷移),**移除欄位**(`down()` 還原)。

## 資料遷移(既有合約收款 → 發票)

一支 migration,`up()`:

1. 對**每張有收款列(`payable=Contract`)的合約** `$contract`:
   - 計算 `collected = 該合約現有 payable=Contract 收款列金額加總`。
   - 建立承載發票 `Invoice`:`contract_id=$contract->id`、`client_id`、`title='合約收款(轉換) '.contract_number`、`status='paid'`、`tax_rate=0`、`discount=0`、`subtotal=total=collected`、`paid_amount=collected`、`issued_date/paid_at` 取最近一次收款日、`currency` 沿用。
   - 建立單一 `InvoiceItem`:`description='合約 {number} 已收款(轉換)'`、`quantity=1`、`unit_price=amount=collected`。
   - 將該合約所有 `payable=Contract` 收款列**改掛**到承載發票:`payable_type=Invoice`、`payable_id=新發票->id`、清空 `invoice_id`。金額/`paid_on`/`payment_method`/`note`/`proof_path`/`created_by` **不動**。
2. 既有「合約發票自身帳本收款」(先前補丁產生的 `payable=Invoice` 且該發票 `contract_id` 有值者)維持原狀即可(本就在發票帳本)。
3. 對所有受影響客戶呼叫 `recalculateRevenue()`,讓 `clients.total_revenue` 反映遷移後的已付發票。
4. 移除 `payments.invoice_id` 欄位(冗餘)。

`down()`:把承載發票的收款改掛回原合約、刪除承載發票與其項目、還原 `payments.invoice_id` 欄位(資料無法完全還原 invoice_id 對應,僅還原結構)。

**安全保證:** 純搬移。遷移前後「全系統收款總額」「每筆收款金額/日期/憑證」完全相同;合約衍生 `paid_amount` 等於遷移前的值。先在測試庫跑並斷言筆數與總額一致,再上正式。

## 不做(YAGNI)
- 不改合約的狀態流程、簽署、PDF。
- 不改報價單轉發票流程(獨立發票本就在發票帳本收款,天然符合)。
- 不做發票作廢回沖等新邏輯。

## 測試計畫(驗證意圖)
1. **遷移正確且不損金額**:造 1 張有 2 筆直接收款(合計 X)的合約 → 跑遷移 → 承載發票 `total=paid_amount=X`、`status=paid`;2 筆收款 `payable` 改為該發票;合約衍生 `paid_amount=X`;`Payment` 總額不變;`payable=Contract` 收款歸零。
2. **合約 paid_amount 為衍生**:合約有 2 張發票(已付 a、b)→ `contract->paid_amount == a+b`、`balance_due == total-(a+b)`。
3. **開發票並收款(一鍵)**:POST → 建立發票 + 該發票帳本一筆收款、發票 `status` 依金額為 paid/partially_paid;合約衍生已收增加;`payable=Invoice`。
4. **營收=已付發票**:儀表板月營收、發票列表 total/month、客戶 `total_revenue` 皆等於已付發票加總(含由合約開的發票),不重複、不遺漏。
5. **回歸**:獨立發票(報價單轉)收款與刪除流程不變;`InvoiceController::recordPayment` 對所有發票一致可用。
6. **拆補丁無殘留**:`recordContractPayment`、`scopeRevenue`、`linkedPayments` 移除後無引用、測試全綠。

## 影響檔案(預估)
- `app/Models/Contract.php`(移除 HasPayments、paid_amount/paid_at 改 accessor)
- `app/Models/Invoice.php`(移除 linkedPayments)
- `app/Models/Payment.php`(移除 scopeRevenue / invoice 關聯)
- `app/Http/Controllers/Admin/ContractController.php`(移除 recordPayment/destroyPayment、createContractPayment 類;新增 createInvoiceAndPay)
- `app/Http/Controllers/Admin/InvoiceController.php`(移除 recordContractPayment、合約發票守衛;統計改已付發票)
- `app/Http/Controllers/Admin/DashboardController.php`(月營收改已付發票)
- `app/Models/Client.php`(recalculateRevenue 改已付發票)
- `routes/admin.php`(增減路由如上)
- `resources/views/admin/contracts/show.blade.php`、`resources/views/admin/invoices/show.blade.php`
- `database/migrations/*_migrate_contract_payments_to_invoices.php`(新)
- `database/migrations/*_drop_invoice_id_from_payments.php`(或併入上一支)
- `tests/Feature/`(對應上述)

## 部署順序(正式環境)
1. 部署程式碼。
2. 跑 migration:`php artisan migrate --force`(含遷移合約收款→發票、移除 payments.invoice_id)。
3. 驗證:儀表板/發票列表營收 = 已付發票加總;抽查數張合約的「已收」與遷移前一致。
