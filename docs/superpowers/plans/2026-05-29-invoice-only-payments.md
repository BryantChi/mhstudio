# 發票才收錢(單一收款帳本) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 把收款收斂為「只發生在發票上」的單一帳本,合約不再直接收款、其收款狀態由旗下發票推導,並把既有合約收款遷移為發票。

**Architecture:** `Invoice` 是唯一 `HasPayments` 收款者(`payable` 一律為 Invoice);`Contract` 移除 `HasPayments`,`paid_amount`/`paid_at` 改為由 `invoices()` 推導的 accessor;先前雙帳本的補丁(`recordContractPayment`、`Payment::scopeRevenue`、`Invoice::linkedPayments`、合約收款 UI/路由)全部移除;合約頁保留「開發票並收款」一鍵(建立發票 + 在該發票帳本記收款)。營收=已付發票。

**Tech Stack:** Laravel 11、Pest(測試庫 = 本機 `mhstudio`,`RefreshDatabase`)、Eloquent 多型 `payments`、Bootstrap 5 + CoreUI Blade。

**規格來源:** `docs/superpowers/specs/2026-05-29-invoice-only-payments-design.md`

**測試前提(沿用):** 無 factory,測試資料一律 `Model::create([...])`;`makeContract()` helper 已存在於 `tests/Feature/ContractInvoiceTest.php` 且內含 `actingAs`。`php artisan test` 跑在 `mhstudio`(每次 `RefreshDatabase` 清空重建)。

**重要 — 既有測試會大改:** 本重構移除 `contracts.record-payment`、`invoices.record-contract-payment` 等路由與方法,`tests/Feature/ContractInvoiceTest.php` 中對應的測試(功能二連動開票、recordContractPayment、linkedPayments、scopeRevenue、排除合約發票營收等)將被移除或改寫。各 Task 會明確指出要刪哪些測試。

---

## File Structure

| 檔案 | 變更 |
|---|---|
| `app/Models/Client.php` | `recalculateRevenue()` → 已付發票加總 |
| `app/Http/Controllers/Admin/DashboardController.php` | 月營收 → 已付發票;移除 `Payment` 用法 |
| `app/Http/Controllers/Admin/InvoiceController.php` | 統計 → 已付發票;移除 `recordContractPayment`、`recordPayment` 守衛、`linkedPayments` load |
| `app/Models/Payment.php` | 移除 `scopeRevenue()`、`invoice()` 關聯、`invoice_id` fillable |
| `app/Models/Invoice.php` | 移除 `linkedPayments()` |
| `app/Models/Contract.php` | 移除 `use HasPayments`、`afterPaymentsSynced/Saved`;`paid_amount`/`paid_at` 改 accessor |
| `app/Http/Controllers/Admin/ContractController.php` | 移除 `recordPayment`/`destroyPayment`/`buildLinkedInvoice`;新增 `createInvoiceAndPay` |
| `routes/admin.php` | 移除 contracts 收款、invoices.record-contract-payment;新增 contracts.create-invoice-and-pay |
| `app/Actions/Finance/MigrateContractPaymentsToInvoices.php`(新) | 遷移邏輯(可單測) |
| `database/migrations/*_migrate_contract_payments_to_invoices.php`(新) | 呼叫上述 Action + 移除 `payments.invoice_id` |
| `resources/views/admin/contracts/show.blade.php` | 移除合約收款 Modal/明細;開票面板加「同時收款」 |
| `resources/views/admin/invoices/show.blade.php` | 移除合約發票專用收款卡片/linkedPayments;統一收款 UI |
| `tests/Feature/ContractInvoiceTest.php` | 改寫(見各 Task) |

---

## Task 1: 營收口徑 → 已付發票

**Files:**
- Modify: `app/Models/Client.php`(`recalculateRevenue()`)
- Modify: `app/Http/Controllers/Admin/DashboardController.php`(`month_revenue` + 移除 `use App\Models\Payment;`)
- Modify: `app/Http/Controllers/Admin/InvoiceController.php`(index `$stats`)
- Modify: `app/Models/Payment.php`(移除 `scopeRevenue` 與 `Builder` import)
- Test: `tests/Feature/ContractInvoiceTest.php`

- [ ] **Step 1: 改寫營收測試(取代既有 payments-ledger / scopeRevenue 測試)**

刪除這些既有測試(整個 `it(...)` 區塊):`'client revenue counts contract payments and independent-invoice payments once'`、`'invoice index revenue sums the payments ledger (contract + independent, once)'`、`'does not double-count revenue when a contract invoice also has its own ledger payment'`、`'dashboard month revenue sums the payments ledger including contract payments'`、`'does not double-count revenue across the linked-payment (功能二) flow'`。

新增(append):

```php
it('revenue equals paid invoices (client, dashboard, invoice index)', function () {
    $contract = makeContract(); // makeContract 已登入並建立 client
    $client = $contract->client;

    // 由合約開的已付發票(計入)
    $contract->invoices()->create([
        'client_id' => $client->id, 'title' => '合約款', 'status' => 'paid',
        'subtotal' => 50000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 50000, 'paid_amount' => 50000, 'currency' => 'TWD',
        'issued_date' => now(), 'due_date' => now(), 'paid_at' => now(),
    ]);
    // 獨立已付發票(計入)
    $client->invoices()->create([
        'contract_id' => null, 'title' => '獨立', 'status' => 'paid',
        'subtotal' => 8000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 8000, 'paid_amount' => 8000, 'currency' => 'TWD',
        'issued_date' => now(), 'due_date' => now(), 'paid_at' => now(),
    ]);
    // 未付發票(不計入)
    $client->invoices()->create([
        'contract_id' => null, 'title' => '未付', 'status' => 'sent',
        'subtotal' => 9999, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 9999, 'currency' => 'TWD', 'issued_date' => now(), 'due_date' => now(),
    ]);

    $client->recalculateRevenue();
    expect((float) $client->fresh()->total_revenue)->toBe(58000.0);

    $stats = $this->get(route('admin.invoices.index'))->viewData('stats');
    expect((float) $stats['total_revenue'])->toBe(58000.0);
    expect((float) $stats['month_revenue'])->toBe(58000.0);

    expect((float) $this->get(route('admin.dashboard'))->viewData('monthRevenue'))->toBe(58000.0);
});
```

- [ ] **Step 2: 跑測試確認失敗**

Run: `php artisan test --filter='revenue equals paid invoices'`
Expected: FAIL(目前 `Client::recalculateRevenue` 用 paid_amount 來源、`Dashboard`/`InvoiceController` 用 `Payment::revenue()`,值或邏輯不符;也可能因刪除的測試殘留引用而需先完成刪除)。

- [ ] **Step 3: `Client::recalculateRevenue()` 改為已付發票加總**

`app/Models/Client.php` — 將 `recalculateRevenue()` 內容改為:

```php
    public function recalculateRevenue(): void
    {
        // 營收 = 已付發票加總(收款只發生在發票上,故已付發票即實收)
        $this->total_revenue = round((float) $this->invoices()->where('status', 'paid')->sum('total'), 2);
        $this->save();
    }
```

- [ ] **Step 4: Dashboard 月營收改已付發票**

`app/Http/Controllers/Admin/DashboardController.php`:
- 移除 `use App\Models\Payment;`(若無其他用途)。
- 將 `month_revenue` 改為:

```php
                'month_revenue' => Invoice::where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year)
                    ->sum('total'),
```

- [ ] **Step 5: InvoiceController 統計改已付發票**

`app/Http/Controllers/Admin/InvoiceController.php` 的 `index()` `$stats`:

```php
        $stats = [
            'total_revenue' => Invoice::paid()->sum('total'),
            'month_revenue' => Invoice::paid()->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('total'),
            'pending_amount' => Invoice::unpaid()->sum('total') - Invoice::unpaid()->sum('paid_amount'),
            'overdue_count' => Invoice::overdue()->count(),
        ];
```

- [ ] **Step 6: 移除 `Payment::scopeRevenue`**

`app/Models/Payment.php` — 刪除 `scopeRevenue()` 方法,以及 `use Illuminate\Database\Eloquent\Builder;`(若無其他用途)。

- [ ] **Step 7: 跑測試確認通過**

Run: `php artisan test --filter='revenue equals paid invoices'`
Expected: PASS

- [ ] **Step 8: Commit**

```bash
git add app/Models/Client.php app/Http/Controllers/Admin/DashboardController.php app/Http/Controllers/Admin/InvoiceController.php app/Models/Payment.php tests/Feature/ContractInvoiceTest.php
git commit -m "重構: 營收口徑改為已付發票(移除 Payment::scopeRevenue)"
```

---

## Task 2: Contract 移除 HasPayments,paid_amount/paid_at 改為衍生

**Files:**
- Modify: `app/Models/Contract.php`
- Modify: `app/Http/Controllers/Admin/ContractController.php`(移除 `recordPayment`、`destroyPayment`、`buildLinkedInvoice`;`destroy()` 註解)
- Modify: `routes/admin.php`(移除 `contracts.record-payment`、`contracts.destroy-payment`)
- Test: `tests/Feature/ContractInvoiceTest.php`

- [ ] **Step 1: 改寫/刪除受影響測試**

刪除這些既有測試:`'linked payment creates a paid invoice bound to the payment (summary mode)'`、`'linked payment custom mode uses provided description'`、`'linked payment copy mode scales contract items and total equals payment'`、`'payment without create_invoice records only the contract ledger'`、`'records a contract invoice payment into the contract ledger and marks it paid'`、`'supports partial payment on a contract invoice'`、`'rejects the contract-payment route for an independent invoice'`、`'blocks the invoice self-ledger payment route for contract invoices'`、`'independent invoice (contract_id null) keeps its own payment ledger intact'` 中任何呼叫 `route('admin.contracts.record-payment'...)` 或 `recordContractPayment` 的部分。

> 註:`'independent invoice ... keeps its own payment ledger intact'` 若只測 `$invoice->recordPayment()`(發票自身帳本)可保留;若涉及合約收款路由則刪。判準:不得再引用 `admin.contracts.record-payment`、`admin.invoices.record-contract-payment`。

新增(append)合約衍生 paid_amount 測試:

```php
it('derives contract paid_amount and balance from its invoices', function () {
    $contract = makeContract(); // total 105000

    $contract->invoices()->create([
        'client_id' => $contract->client_id, 'title' => 'A', 'status' => 'paid',
        'subtotal' => 30000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 30000, 'paid_amount' => 30000, 'currency' => 'TWD',
        'issued_date' => now(), 'due_date' => now(), 'paid_at' => now(),
    ]);
    $contract->invoices()->create([
        'client_id' => $contract->client_id, 'title' => 'B', 'status' => 'partially_paid',
        'subtotal' => 20000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 20000, 'paid_amount' => 5000, 'currency' => 'TWD',
        'issued_date' => now(), 'due_date' => now(),
    ]);

    $contract = $contract->fresh();
    expect((float) $contract->paid_amount)->toBe(35000.0); // 30000 + 5000
    expect((float) $contract->balance_due)->toBe(70000.0); // 105000 - 35000
});
```

- [ ] **Step 2: 跑測試確認失敗**

Run: `php artisan test --filter='derives contract paid_amount and balance'`
Expected: FAIL(目前 `paid_amount` 是 DB 欄位值=0,非衍生)。

- [ ] **Step 3: Contract 移除 HasPayments 並加衍生 accessor**

`app/Models/Contract.php`:
- 移除 `use App\Models\Concerns\HasPayments;` 與 `use HasPayments, LogsActivity;` 改為 `use LogsActivity;`。
- 移除 `afterPaymentsSynced()` 與 `afterPaymentsSaved()` 兩個方法。
- 新增 accessor(放在其他 accessor 附近,例如 `getBalanceDueAttribute` 之前):

```php
    /**
     * 已收金額 = 旗下發票已付加總(收款只發生在發票上,合約不自有帳本)。
     */
    public function getPaidAmountAttribute(): float
    {
        return round((float) $this->invoices()->sum('paid_amount'), 2);
    }

    /**
     * 全部發票付清且有金額時,取最近一次發票付款日;否則 null。
     */
    public function getPaidAtAttribute()
    {
        if ($this->total > 0 && $this->paid_amount >= $this->total) {
            return $this->invoices()->whereNotNull('paid_at')->max('paid_at');
        }

        return null;
    }
```

> 說明:`getBalanceDueAttribute()`、`getPaymentStatusAttribute()` 公式不變,因其讀取 `$this->paid_amount`,而 accessor 已改為衍生值。`contracts.paid_amount`/`paid_at` 資料欄位保留但不再寫入(Eloquent accessor 於存取時優先於原始屬性)。

- [ ] **Step 4: 移除 ContractController 收款方法**

`app/Http/Controllers/Admin/ContractController.php`:
- 刪除 `recordPayment()`、`buildLinkedInvoice()`、`destroyPayment()` 三個方法。
- `destroy()` 內把註解「收款帳本由 HasPayments 連帶刪除」改為「合約收款已遷移至發票,合約本身不持有收款」;移除任何刪 payments 的依賴(原本靠 trait boot,現已無)。
- 若 `use App\Models\Payment;` 在此檔已無其他用途則移除。

- [ ] **Step 5: 移除合約收款路由**

`routes/admin.php` — 刪除:
```php
        Route::post('contracts/{contract}/payments', [ContractController::class, 'recordPayment'])->name('contracts.record-payment');
        Route::delete('contracts/{contract}/payments/{payment}', [ContractController::class, 'destroyPayment'])->name('contracts.destroy-payment');
```

- [ ] **Step 6: 跑測試確認通過**

Run: `php artisan test --filter='derives contract paid_amount and balance'`
Expected: PASS

- [ ] **Step 7: 全套測試(確認移除未殘留引用)**

Run: `php artisan test`
Expected: 全綠。若有測試仍引用 `admin.contracts.record-payment`,回 Step 1 刪除之。

- [ ] **Step 8: Commit**

```bash
git add app/Models/Contract.php app/Http/Controllers/Admin/ContractController.php routes/admin.php tests/Feature/ContractInvoiceTest.php
git commit -m "重構: 合約不再持有收款,paid_amount/paid_at 改由旗下發票推導"
```

---

## Task 3: 合約頁「開發票並收款」一鍵(createInvoiceAndPay)

**Files:**
- Modify: `app/Http/Controllers/Admin/ContractController.php`(新增 `createInvoiceAndPay`)
- Modify: `routes/admin.php`(新增 `contracts.create-invoice-and-pay`)
- Test: `tests/Feature/ContractInvoiceTest.php`

- [ ] **Step 1: 寫失敗測試**

```php
it('creates an invoice and records its payment in one step (一鍵)', function () {
    $contract = makeContract(); // total 105000

    $this->post(route('admin.contracts.create-invoice-and-pay', $contract), [
        'amount' => 50000,
        'item_mode' => 'summary',
        'payment_method' => '轉帳',
    ]);

    $invoice = $contract->fresh()->invoices()->first();
    expect($invoice)->not->toBeNull();
    expect($invoice->contract_id)->toBe($contract->id);
    expect((float) $invoice->total)->toBe(50000.0);
    expect($invoice->status)->toBe('paid');
    expect((float) $invoice->paid_amount)->toBe(50000.0);
    // 收款記在發票自身帳本(payable=Invoice),一筆
    expect($invoice->payments()->count())->toBe(1);
    // 合約已收(衍生)= 50000
    expect((float) $contract->fresh()->paid_amount)->toBe(50000.0);
});

it('one-step partial payment leaves invoice partially_paid', function () {
    $contract = makeContract();

    $this->post(route('admin.contracts.create-invoice-and-pay', $contract), [
        'amount' => 20000, 'item_mode' => 'summary',
    ]);
    // 摘要模式:發票 total = 收款額 = 20000 → 全額付清 → paid
    $invoice = $contract->fresh()->invoices()->first();
    expect($invoice->status)->toBe('paid');
    expect((float) $invoice->total)->toBe(20000.0);
});
```

- [ ] **Step 2: 跑測試確認失敗**

Run: `php artisan test --filter='creates an invoice and records its payment in one step'`
Expected: FAIL(route `admin.contracts.create-invoice-and-pay` 未定義)。

- [ ] **Step 3: 新增路由**

`routes/admin.php` — 在 `contracts.create-invoice` 那行之後加入:
```php
        Route::post('contracts/{contract}/invoice-and-pay', [ContractController::class, 'createInvoiceAndPay'])->name('contracts.create-invoice-and-pay');
```

- [ ] **Step 4: 實作 `createInvoiceAndPay()`**

`app/Http/Controllers/Admin/ContractController.php` — 在 `createInvoice()` 之後新增(`use App\Models\Invoice;`、`use Illuminate\Support\Facades\DB;` 已存在):

```php
    /**
     * 一鍵:從合約開立發票並在該發票帳本記一筆收款(單一帳本)。
     * 收款金額即發票金額(摘要/自訂),或複製合約項目等比縮放(copy)。
     */
    public function createInvoiceAndPay(Request $request, Contract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'item_mode' => 'required|in:summary,custom,copy',
            'description' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:255',
            'paid_on' => 'nullable|date',
            'note' => 'nullable|string|max:500',
            'proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $contract->loadMissing('items');
        $amount = round((float) $validated['amount'], 2);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $file = $request->file('proof');
            $proofPath = $file->storeAs('uploads/'.date('Y/m'), \Illuminate\Support\Str::uuid().'.'.$file->getClientOriginalExtension(), 'public');
        }

        $invoice = DB::transaction(function () use ($validated, $contract, $amount, $proofPath) {
            $invoice = Invoice::create([
                'client_id' => $contract->client_id,
                'project_id' => $contract->project_id,
                'contract_id' => $contract->id,
                'title' => $contract->title,
                'status' => 'draft',
                'subtotal' => 0, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0, 'total' => 0,
                'currency' => $contract->currency ?? 'TWD',
                'issued_date' => now(),
                'due_date' => now(),
            ]);

            if ($validated['item_mode'] === 'copy' && $contract->total > 0 && $contract->items->isNotEmpty()) {
                $f = $amount / (float) $contract->total;
                foreach ($contract->items as $item) {
                    $scaled = round((float) $item->amount * $f, 2);
                    $invoice->items()->create([
                        'description' => $item->description, 'quantity' => 1, 'unit' => $item->unit,
                        'unit_price' => $scaled, 'amount' => $scaled, 'order' => $item->order,
                    ]);
                }
                $invoice->tax_rate = $contract->tax_rate;
                $invoice->discount = round((float) $contract->discount * $f, 2);
                $invoice->save();
            } else {
                $desc = $validated['item_mode'] === 'custom' && ! empty($validated['description'])
                    ? $validated['description']
                    : "合約 {$contract->contract_number} 款項";
                $invoice->items()->create([
                    'description' => $desc, 'quantity' => 1, 'unit' => '式',
                    'unit_price' => $amount, 'amount' => $amount, 'order' => 0,
                ]);
            }

            $invoice->recalculate(); // 設 subtotal/tax/total

            // 在發票自身帳本記收款(唯一帳本);recordPayment 由 HasPayments 提供,會同步狀態
            $invoice->recordPayment($amount, $validated['payment_method'] ?? null, $validated['paid_on'] ?? null, $validated['note'] ?? null, $proofPath);

            return $invoice;
        });

        flash_success('已開立發票並登記收款');

        return redirect()->route('admin.invoices.show', $invoice);
    }
```

- [ ] **Step 5: 跑測試確認通過**

Run: `php artisan test --filter='creates an invoice and records its payment in one step|one-step partial payment'`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Admin/ContractController.php routes/admin.php tests/Feature/ContractInvoiceTest.php
git commit -m "功能: 合約頁一鍵開發票並收款(收款記在發票帳本)"
```

---

## Task 4: 簡化發票/Payment/Invoice(移除雙帳本補丁)

**Files:**
- Modify: `app/Http/Controllers/Admin/InvoiceController.php`(移除 `recordContractPayment`、`recordPayment` 守衛、`linkedPayments` load)
- Modify: `app/Models/Invoice.php`(移除 `linkedPayments()`)
- Modify: `app/Models/Payment.php`(移除 `invoice()` 關聯、`invoice_id` fillable)
- Modify: `routes/admin.php`(移除 `invoices.record-contract-payment`)
- Test: `tests/Feature/ContractInvoiceTest.php`

- [ ] **Step 1: 寫測試 — 所有發票(含合約發票)一律用發票自身帳本收款**

```php
it('all invoices including contract invoices collect on their own ledger', function () {
    $contract = makeContract();
    // 合約發票(contract_id 有值),draft
    $invoice = $contract->invoices()->create([
        'client_id' => $contract->client_id, 'title' => '合約發票', 'status' => 'sent',
        'subtotal' => 30000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 30000, 'currency' => 'TWD', 'issued_date' => now(), 'due_date' => now(),
    ]);

    // 一般 record-payment 路由對合約發票也應正常運作(不再被擋)
    $this->post(route('admin.invoices.record-payment', $invoice), [
        'amount' => 30000, 'payment_method' => '現金',
    ]);

    $invoice = $invoice->fresh();
    expect($invoice->status)->toBe('paid');
    expect((float) $invoice->paid_amount)->toBe(30000.0);
    expect($invoice->payments()->count())->toBe(1);
    expect((float) $contract->fresh()->paid_amount)->toBe(30000.0); // 合約衍生
});
```

- [ ] **Step 2: 跑測試確認失敗**

Run: `php artisan test --filter='all invoices including contract invoices collect on their own ledger'`
Expected: FAIL(目前 `InvoiceController::recordPayment` 對 `contract_id` 發票會擋下並導回,不會記款)。

- [ ] **Step 3: InvoiceController 簡化**

`app/Http/Controllers/Admin/InvoiceController.php`:
- `recordPayment()`:刪除開頭對 `$invoice->contract_id` 的守衛區塊(那段 `if ($invoice->contract_id) { flash_error... return ...; }`),恢復為單純記款。
- 刪除整個 `recordContractPayment()` 方法。
- `show()` 的 `load([...])` 移除 `'linkedPayments'`(保留 `'payments'`、`'contract'`)。
- 若 `use Illuminate\Support\Facades\DB;` 在此檔已無其他用途則移除。

- [ ] **Step 4: 移除 Invoice::linkedPayments**

`app/Models/Invoice.php` — 刪除 `linkedPayments()` 方法(及其註解)。

- [ ] **Step 5: 移除 Payment::invoice 關聯與 invoice_id fillable**

`app/Models/Payment.php`:
- `$fillable` 移除 `'invoice_id',`。
- 刪除 `invoice()` 關聯方法。
- 若 `use Illuminate\Database\Eloquent\Relations\BelongsTo;` 已無其他用途則移除(注意 `creator()` 仍用 BelongsTo,故**保留**)。

> 註:`payments.invoice_id` 欄位本身於 Task 6 的 migration 移除;本步只移除模型對它的引用。

- [ ] **Step 6: 移除 invoices.record-contract-payment 路由**

`routes/admin.php` — 刪除:
```php
        Route::post('invoices/{invoice}/contract-payment', [InvoiceController::class, 'recordContractPayment'])->name('invoices.record-contract-payment');
```

- [ ] **Step 7: 跑測試確認通過 + 全套**

Run: `php artisan test`
Expected: 全綠。

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Admin/InvoiceController.php app/Models/Invoice.php app/Models/Payment.php routes/admin.php tests/Feature/ContractInvoiceTest.php
git commit -m "重構: 移除合約發票收款特例,所有發票統一走自身帳本"
```

---

## Task 5: 視圖(合約頁/發票頁)

**Files:**
- Modify: `resources/views/admin/contracts/show.blade.php`
- Modify: `resources/views/admin/invoices/show.blade.php`
- 無自動化測試;以 `npm run build && php artisan view:cache` 驗證 Blade 編譯。

- [ ] **Step 1: 合約頁移除收款 Modal/明細,改為開票面板**

`resources/views/admin/contracts/show.blade.php`:
- **刪除**「登記收款 Modal」整段(`{{-- 登記收款 Modal --}}` 到其結束 `@endif`)。
- **刪除**合約自身的「收款明細」表格(含 `route('admin.contracts.destroy-payment'...)` 的列;以該路由名定位刪除)。
- 「相關發票」卡片**保留**;財務摘要(合約總額/已開發票/可開餘額)沿用(`$contract->paid_amount` 現為衍生,無需改 Blade)。可額外顯示「已收 NT$ {{ number_format($contract->paid_amount) }}」。
- 「開立發票」Modal(`createInvoiceModal`,POST `admin.contracts.create-invoice`)**保留**。新增第二顆按鈕/Modal「開發票並收款」,POST 到 `route('admin.contracts.create-invoice-and-pay', $contract)`,欄位:`amount`、`item_mode`(summary/custom/copy)、`description`(custom 用)、`payment_method`、`paid_on`、`note`、`proof`(file,enctype multipart)。

範例「開發票並收款」Modal(置於「開立發票」Modal 之後):

```blade
{{-- 開發票並收款 Modal --}}
<div class="modal fade" id="createInvoicePayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.contracts.create-invoice-and-pay', $contract) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">開發票並收款</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">收款金額 <span class="text-danger">*</span></label>
                        <div class="input-group"><span class="input-group-text">NT$</span>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">發票項目方式</label>
                        <select name="item_mode" class="form-select"
                                onchange="document.getElementById('ciapDescWrap').classList.toggle('d-none', this.value !== 'custom')">
                            <option value="summary">摘要(合約款項)</option>
                            <option value="custom">自訂描述</option>
                            <option value="copy">複製合約項目(等比縮放)</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="ciapDescWrap">
                        <label class="form-label">發票描述</label>
                        <input type="text" name="description" class="form-control" placeholder="留空則用摘要預設">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">付款方式</label>
                        <input type="text" name="payment_method" class="form-control" placeholder="例如:銀行轉帳">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">收款日期</label>
                        <input type="date" name="paid_on" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">備註</label>
                        <input type="text" name="note" class="form-control" placeholder="選填">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">收款憑證</label>
                        <input type="file" name="proof" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">選填,上傳一次即可(存於發票收款)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-success">開發票並收款</button>
                </div>
            </form>
        </div>
    </div>
</div>
```

在「相關發票」卡片 header 的「開立發票」按鈕旁加觸發鈕:
```blade
<button type="button" class="btn btn-sm btn-success" data-coreui-toggle="modal" data-coreui-target="#createInvoicePayModal">開發票並收款</button>
```

- [ ] **Step 2: 發票頁統一收款 UI**

`resources/views/admin/invoices/show.blade.php`:
- **刪除**「登記收款(合約發票走合約)」卡片整段(以 `route('admin.invoices.record-contract-payment'...)` 定位)。
- 「記錄付款」表單條件改回不分合約發票:`@if($invoice->balance_due > 0 && !in_array($invoice->status, ['cancelled', 'draft']))`(移除 `&& !$invoice->contract_id`)。
- 「收款明細」改回單一來源 `$invoice->payments`(移除 `$ledgerPayments`/`linkedPayments` 分支與「於合約登記/唯讀」邏輯,恢復含刪除鈕的 `admin.invoices.destroy-payment` 版本)。
- 「來源合約」連結**保留**。

- [ ] **Step 3: 驗證 Blade 編譯**

Run: `npm run build && php artisan view:cache`
Expected: 成功、無 ParseError。完成後 `php artisan view:clear`。

- [ ] **Step 4: 全套測試 + Pint**

Run: `php artisan test` → 全綠
Run: `php vendor/bin/pint app/ routes/` → 無錯

- [ ] **Step 5: Commit**

```bash
git add resources/views/admin/contracts/show.blade.php resources/views/admin/invoices/show.blade.php
git commit -m "視圖: 合約頁改開發票並收款、移除合約收款 UI;發票頁統一收款"
```

---

## Task 6: 資料遷移(既有合約收款 → 發票)+ 移除 payments.invoice_id

**Files:**
- Create: `app/Actions/Finance/MigrateContractPaymentsToInvoices.php`
- Create: `database/migrations/2026_05_30_000001_migrate_contract_payments_to_invoices.php`
- Test: `tests/Feature/ContractInvoiceTest.php`

- [ ] **Step 1: 寫失敗測試(Action 直接測,因 RefreshDatabase 已 migrate 過)**

`Contract` 在 Task 2 已移除 `HasPayments`,沒有 `payments()` 關聯,所以「模擬舊合約收款」要用多型 `associate` 直接建立。先在 `tests/Feature/ContractInvoiceTest.php` 的 helper 區(`makeContract` 附近)加入:

```php
/** 模擬舊資料:直接記在合約上的收款(payable=Contract) */
function makeContractPayment(Contract $c, float $amt, string $on, ?string $method = null): Payment
{
    $p = new Payment(['amount' => $amt, 'paid_on' => $on, 'payment_method' => $method, 'created_by' => auth()->id()]);
    $p->payable()->associate($c);
    $p->save();

    return $p;
}
```

接著新增測試:

```php
it('migrates existing contract direct payments into a carrier invoice without changing money', function () {
    $contract = makeContract(); // total 105000

    // 模擬舊資料:兩筆直接記在合約的收款(payable=Contract)
    makeContractPayment($contract, 30000, '2026-04-01', '轉帳');
    makeContractPayment($contract, 20000, '2026-05-10');

    $totalBefore = Payment::sum('amount');

    (new \App\Actions\Finance\MigrateContractPaymentsToInvoices)->execute();

    // 收款總額不變
    expect((float) Payment::sum('amount'))->toBe((float) $totalBefore);
    // 合約已無 payable=Contract 收款
    expect(Payment::where('payable_type', Contract::class)->count())->toBe(0);

    // 產生一張承載發票,total=已收=50000、status=paid
    $invoice = $contract->fresh()->invoices()->first();
    expect($invoice)->not->toBeNull();
    expect((float) $invoice->total)->toBe(50000.0);
    expect((float) $invoice->paid_amount)->toBe(50000.0);
    expect($invoice->status)->toBe('paid');
    // 兩筆收款改掛此發票
    expect($invoice->payments()->count())->toBe(2);
    // 合約衍生已收不變 = 50000
    expect((float) $contract->fresh()->paid_amount)->toBe(50000.0);
});
```

> 註:`Payment`、`Contract` 已在測試檔頂部 `use`。`payable()->associate()` 直接設多型欄位,不受 `$fillable` 限制。

- [ ] **Step 2: 跑測試確認失敗**

Run: `php artisan test --filter='migrates existing contract direct payments'`
Expected: FAIL(`App\Actions\Finance\MigrateContractPaymentsToInvoices` 不存在)。

- [ ] **Step 3: 建立 Action**

`app/Actions/Finance/MigrateContractPaymentsToInvoices.php`:

```php
<?php

namespace App\Actions\Finance;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class MigrateContractPaymentsToInvoices
{
    /**
     * 把每張有「直接收款(payable=Contract)」的合約,
     * 其收款搬到一張承載發票上(payable=Invoice),金額/日期/憑證不動。
     */
    public function execute(): void
    {
        $contractIds = Payment::where('payable_type', Contract::class)
            ->distinct()
            ->pluck('payable_id');

        $affectedClientIds = [];

        foreach ($contractIds as $contractId) {
            $contract = Contract::find($contractId);
            if (! $contract) {
                continue;
            }

            DB::transaction(function () use ($contract, &$affectedClientIds) {
                $payments = Payment::where('payable_type', Contract::class)
                    ->where('payable_id', $contract->id)
                    ->get();

                $collected = round((float) $payments->sum('amount'), 2);
                $latestPaidOn = $payments->max('paid_on');

                $invoice = Invoice::create([
                    'client_id' => $contract->client_id,
                    'project_id' => $contract->project_id,
                    'contract_id' => $contract->id,
                    'title' => '合約收款(轉換) '.$contract->contract_number,
                    'status' => 'paid',
                    'subtotal' => $collected,
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'discount' => 0,
                    'total' => $collected,
                    'paid_amount' => $collected,
                    'currency' => $contract->currency ?? 'TWD',
                    'issued_date' => $latestPaidOn ?? now(),
                    'due_date' => $latestPaidOn ?? now(),
                    'paid_at' => $latestPaidOn ?? now(),
                ]);

                $invoice->items()->create([
                    'description' => '合約 '.$contract->contract_number.' 已收款(轉換)',
                    'quantity' => 1,
                    'unit' => '式',
                    'unit_price' => $collected,
                    'amount' => $collected,
                    'order' => 0,
                ]);

                // 把收款改掛到承載發票(金額/日期/憑證不動)
                foreach ($payments as $payment) {
                    $payment->payable_type = Invoice::class;
                    $payment->payable_id = $invoice->id;
                    $payment->save();
                }

                $affectedClientIds[$contract->client_id] = true;
            });
        }

        // 重算受影響客戶營收
        foreach (array_keys($affectedClientIds) as $clientId) {
            optional(\App\Models\Client::find($clientId))->recalculateRevenue();
        }
    }
}
```

> 註:`Payment` 的 `payable_type`/`payable_id` 直接賦值再 `save()` 可行(它們非 guarded、是真實欄位,直接設屬性不受 `$fillable` 限制——`$fillable` 只擋 mass-assignment)。

- [ ] **Step 4: 跑測試確認通過**

Run: `php artisan test --filter='migrates existing contract direct payments'`
Expected: PASS

- [ ] **Step 5: 建立 migration(呼叫 Action + 移除 payments.invoice_id)**

`database/migrations/2026_05_30_000001_migrate_contract_payments_to_invoices.php`:

```php
<?php

use App\Actions\Finance\MigrateContractPaymentsToInvoices;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. 既有合約直接收款 → 承載發票
        (new MigrateContractPaymentsToInvoices)->execute();

        // 2. 移除冗餘欄位(payable 已指向發票)
        if (Schema::hasColumn('payments', 'invoice_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('invoice_id');
            });
        }
    }

    public function down(): void
    {
        // 結構還原:重建 invoice_id 欄位(資料對應無法還原)
        if (! Schema::hasColumn('payments', 'invoice_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('invoice_id')->nullable()->after('id')->constrained('invoices')->nullOnDelete();
            });
        }
        // 承載發票/收款歸屬不自動回滾(請用資料庫備份)。
    }
};
```

- [ ] **Step 6: 跑全套測試(migration 在 RefreshDatabase 空庫上為 no-op,應全綠)**

Run: `php artisan test`
Expected: 全綠。

- [ ] **Step 7: 驗證欄位已移除**

Run: `php artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasColumn('payments','invoice_id') ? 'STILL EXISTS' : 'DROPPED';"`
Expected: `DROPPED`

- [ ] **Step 8: Pint + Commit**

```bash
php vendor/bin/pint app/Actions/Finance/MigrateContractPaymentsToInvoices.php database/migrations/2026_05_30_000001_migrate_contract_payments_to_invoices.php tests/Feature/ContractInvoiceTest.php
git add app/Actions/Finance/MigrateContractPaymentsToInvoices.php database/migrations/2026_05_30_000001_migrate_contract_payments_to_invoices.php tests/Feature/ContractInvoiceTest.php
git commit -m "遷移: 既有合約收款轉為發票,移除 payments.invoice_id"
```

---

## 完成後驗證清單

- [ ] `php artisan test` 全綠
- [ ] `php vendor/bin/pint --test` 無待修正
- [ ] `grep -rn "recordContractPayment\|scopeRevenue\|linkedPayments\|buildLinkedInvoice\|record-contract-payment\|contracts.record-payment" app/ routes/ resources/` 無殘留
- [ ] 手動:合約頁可「開立發票」與「開發票並收款」;發票頁所有發票一律可記款;儀表板/發票列表營收=已付發票;合約「已收」與遷移前一致
- [ ] 部署:`migrate --force` 跑遷移;抽查數張合約已收金額與遷移前相同
