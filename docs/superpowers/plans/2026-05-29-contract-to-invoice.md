# 合約產生發票 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 讓「合約 (Contract)」能產生「發票 (Invoice)」——支援手動開票（4 種金額模式）與「登記收款連動開票」，且不重複計算金流。

**Architecture:** 收款的唯一真實來源仍是合約自己的 `HasPayments` 帳本。合約發票（`invoices.contract_id` 有值）是請款／收據文件，不自成帳本；其「已付」狀態以對應的合約收款直接寫入（`Invoice.status=paid`、`paid_amount=total`），並用 `payments.invoice_id` 標記哪筆合約收款對應哪張發票。獨立發票（`contract_id=null`）行為完全不變。

**Tech Stack:** Laravel 11、Eloquent（多型 `payments`、`HasPayments` trait）、Pest、Bootstrap 5 + CoreUI Blade、`barryvdh/laravel-dompdf`（不在本次範圍）。

**規格來源：** `docs/superpowers/specs/2026-05-29-contract-to-invoice-design.md`

## 生產部署安全性（既有資料零影響）— 硬性需求

本功能上線到正式環境**不得更動任何既有資料**。保證方式：

1. **只新增 2 支 migration，且皆為「可空欄位」**：
   - `invoices.contract_id`：`nullable` FK。既有發票全部得到 `contract_id = NULL` = 獨立發票 = 行為完全不變。
   - `payments.invoice_id`：`nullable` FK。既有收款全部得到 `invoice_id = NULL` = 不對應任何發票 = 行為完全不變。
   - **無任何 UPDATE / 資料回填 / 欄位刪除 / 既有欄位型別變更**，純 `ADD COLUMN`（外加 FK 約束），既有列的資料一個位元都不會改。
2. **既有 migration 一律不動**（採用同款 MySQL 測試庫，毋須為 SQLite 修補）。正式環境已套用過的 58 支 migration 不會重跑。
3. **部署指令只跑新 migration**：`php artisan migrate --force`（或 `/deploy/migrate` 路由）只會執行這 2 支尚未套用的 migration，Laravel 會略過已套用者。
4. **應用層只「新增能力」不改舊行為**：新關聯／accessor／controller 方法／視圖區塊都是加法；獨立發票（`contract_id=NULL`）的收款與顯示邏輯完全沿用現狀（見功能驗證測試「獨立發票不受影響」）。
5. **部署前演練**：先在 `mhstudio_test`（或一份正式資料快照）跑 `migrate --force` 驗證 2 支 migration 成功、既有資料筆數不變，再上正式。

> 注意：`down()`（rollback）會 `dropConstrainedForeignId`，僅在「主動回滾」時觸發，正常部署不會執行，故不影響既有資料。

**前置現況（重要）：** 本專案雖在 `composer.json` 列入 Pest，但**尚無任何測試骨架**——沒有 `phpunit.xml`、`tests/` 目錄、`TestCase.php`、`Pest.php`，也**沒有任何 model factory**。因此 Task 0 必須先建立測試骨架，且所有測試資料一律以 `Model::create([...])` 直接建立，**不可使用 `::factory()`**。

**測試資料庫決策（已定）：** 採**獨立的 MySQL 測試庫 `mhstudio_test`**（與正式環境同款 MySQL，沿用本機同組帳密，僅資料庫名不同），而非 SQLite。原因：本專案 58 支既有 migration 含多處 MySQL 專屬操作（`fullText` 索引、依名 `dropForeign` 等），SQLite 無法跑完整 migration 歷史。
**安全警示：** `RefreshDatabase` 會清空它所連的資料庫，**測試環境的 `DB_DATABASE` 必須是 `mhstudio_test`，絕不可指向正式庫 `mhstudio`**。

**金額一致性規則（本計畫對 spec 的明確化，務必遵守）：**
所有金額都在「含稅 total」尺度上比較（`invoiced_amount = Σ invoice.total`、`uninvoiced_amount = contract.total − invoiced_amount`）。為使 4 種模式的金額互相可比：
- **單行模式（custom / percent / remaining）**：產出的發票 `tax_rate = 0`、`discount = 0`，單一項目的 `amount` 即為發票 `total`（視為含稅金額）。如此 `按比例 = total × %`、`剩餘 = total − invoiced_amount` 在重算後仍精確成立。
- **copy_items 模式**：逐項複製 `ContractItem`，並沿用合約 `tax_rate` 與 `discount`，`recalculate()` 後發票 `total` 恰等於 `contract.total`（faithful copy）。
- **收款連動 copy 模式（等比縮放）**：令 `f = 收款額 / 合約total`，項目 `unit_price × quantity` 各乘 `f`、`discount × f`、沿用 `tax_rate`，`recalculate()` 後 `total = f × 合約total = 收款額`（數學上精確，僅受逐項四捨五入影響 ≤ 數分錢）。

---

## File Structure

| 檔案 | 責任 |
|---|---|
| `phpunit.xml` / `tests/TestCase.php` / `tests/Pest.php`（新） | 測試骨架（Task 0；目前完全不存在） |
| `database/migrations/*_add_contract_id_to_invoices_table.php`（新） | `invoices.contract_id` nullable FK → `contracts`, nullOnDelete |
| `database/migrations/*_add_invoice_id_to_payments_table.php`（新） | `payments.invoice_id` nullable FK → `invoices`, nullOnDelete |
| `app/Models/Contract.php`（改） | `invoices()` HasMany、`invoiced_amount` / `uninvoiced_amount` accessor |
| `app/Models/Invoice.php`（改） | `contract()` BelongsTo、`contract_id` 入 `$fillable` |
| `app/Models/Payment.php`（改） | `invoice_id` 入 `$fillable`、`invoice()` BelongsTo |
| `app/Http/Controllers/Admin/ContractController.php`（改） | `createInvoice()`（功能一）、`recordPayment()` 擴充（功能二） |
| `routes/admin.php`（改） | `POST contracts/{contract}/invoices` → `contracts.create-invoice` |
| `resources/views/admin/contracts/show.blade.php`（改） | 開票面板 + 相關發票清單 + 收款 Modal 連動欄位 |
| `resources/views/admin/invoices/show.blade.php`（改） | `contract_id` 有值時顯示「來源合約」連結 |
| `tests/Feature/ContractInvoiceTest.php`（新） | 功能一、功能二全部測試 |

---

## Task 0: 建立測試骨架（獨立 MySQL 測試庫 mhstudio_test）

> 本專案目前無任何測試設定，必須先建立骨架。測試庫採獨立的 `mhstudio_test`（見上方「測試資料庫決策」）。

**Files:**
- Create: `phpunit.xml`
- Create: `tests/TestCase.php`
- Create: `tests/Pest.php`
- Create: `tests/Feature/SmokeTest.php`

- [ ] **Step 0: 建立 `mhstudio_test` 資料庫（本機 MySQL，沿用 `.env` 帳密）**

無 `mysql` CLI 時用 PHP PDO：

```bash
php -r '$p=new PDO("mysql:host=127.0.0.1;port=3306","root","root");$p->exec("CREATE DATABASE IF NOT EXISTS mhstudio_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");echo "ok\n";'
```

- [ ] **Step 1: 建立 `phpunit.xml`**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="APP_MAINTENANCE_DRIVER" value="file"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_STORE" value="array"/>
        <!-- 測試使用獨立的 mhstudio_test 資料庫；切勿改為正式庫 mhstudio，RefreshDatabase 會清空它 -->
        <env name="DB_CONNECTION" value="mysql"/>
        <env name="DB_DATABASE" value="mhstudio_test"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
    </php>
</phpunit>
```

（其餘 DB 連線參數 `DB_HOST`/`DB_PORT`/`DB_USERNAME`/`DB_PASSWORD` 沿用 `.env`，僅覆寫資料庫名。）

- [ ] **Step 2: 建立 `tests/TestCase.php`**

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //
}
```

- [ ] **Step 3: 建立 `tests/Pest.php`**

```php
<?php

use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');
```

- [ ] **Step 4: 建立 `tests/Feature/.gitkeep`（空檔）並寫一個健全性測試**

`tests/Feature/SmokeTest.php`：

```php
<?php

it('boots the test harness', function () {
    expect(true)->toBeTrue();
});
```

- [ ] **Step 5: 跑測試確認骨架可運作**

Run: `php artisan test --filter='boots the test harness'`
Expected: PASS（1 passed）。
另以一個臨時 `RefreshDatabase` 測試確認 58 個 migration 在 `mhstudio_test` 跑得過（`Schema::hasTable('contracts')` 為真），確認後移除該臨時測試。**因採與正式同款的 MySQL，無需修改任何既有 migration。**

- [ ] **Step 6: Commit**

```bash
git add phpunit.xml tests/TestCase.php tests/Pest.php tests/Feature/SmokeTest.php
git commit -m "test: bootstrap Pest harness with in-memory sqlite"
```

---

## Task 1: 資料庫 Migration（雙向關聯欄位）

**Files:**
- Create: `database/migrations/2026_05_29_100001_add_contract_id_to_invoices_table.php`
- Create: `database/migrations/2026_05_29_100002_add_invoice_id_to_payments_table.php`
- Test: `tests/Feature/ContractInvoiceTest.php`

- [ ] **Step 1: 寫失敗測試（欄位存在性）**

建立 `tests/Feature/ContractInvoiceTest.php`：

```php
<?php

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('has contract_id column on invoices and invoice_id on payments', function () {
    expect(Schema::hasColumn('invoices', 'contract_id'))->toBeTrue();
    expect(Schema::hasColumn('payments', 'invoice_id'))->toBeTrue();
});
```

- [ ] **Step 2: 跑測試確認失敗**

Run: `php artisan test --filter='has contract_id column'`
Expected: FAIL（`contract_id` 欄位尚未存在 → `toBeTrue` 斷言失敗）

- [ ] **Step 3: 建立 invoices 的 migration**

`database/migrations/2026_05_29_100001_add_contract_id_to_invoices_table.php`：

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('contract_id')
                ->nullable()
                ->after('quote_id')
                ->constrained('contracts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contract_id');
        });
    }
};
```

- [ ] **Step 4: 建立 payments 的 migration**

`database/migrations/2026_05_29_100002_add_invoice_id_to_payments_table.php`：

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('invoice_id')
                ->nullable()
                ->after('id')
                ->constrained('invoices')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
        });
    }
};
```

- [ ] **Step 5: 跑測試確認通過**

Run: `php artisan test --filter='has contract_id column'`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_05_29_100001_add_contract_id_to_invoices_table.php \
        database/migrations/2026_05_29_100002_add_invoice_id_to_payments_table.php \
        tests/Feature/ContractInvoiceTest.php
git commit -m "feat: add contract_id to invoices and invoice_id to payments"
```

---

## Task 2: Model 關聯與 accessor

**Files:**
- Modify: `app/Models/Contract.php`（`invoices()` 約於 `items()` 之後 line 163 後；accessor 於 `getBalanceDueAttribute` 附近 line 338 後）
- Modify: `app/Models/Invoice.php`（`contract()` 於 `quote()` 之後 line 110 後；`$fillable` line 17-37）
- Modify: `app/Models/Payment.php`（`$fillable` line 11-18；`invoice()` 關聯）
- Test: `tests/Feature/ContractInvoiceTest.php`

- [ ] **Step 1: 寫失敗測試（關聯與 accessor）**

在 `tests/Feature/ContractInvoiceTest.php` 末尾追加。先建立一個共用的 helper 來造合約（後續任務也會用到）：

```php
function makeContract(array $overrides = []): Contract
{
    // contracts.created_by 為 NOT NULL FK；Contract::boot() 取 auth()->id()，故須先登入
    test()->actingAs(\App\Models\User::create([
        'name' => '測試人員',
        'email' => 'tester'.uniqid().'@example.com',
        'password' => 'password',
    ]));

    $client = \App\Models\Client::create(['name' => '測試客戶']);

    $contract = Contract::create(array_merge([
        'client_id' => $client->id,
        'title' => '測試合約',
        'content' => '內容',
        'type' => 'service',
        'status' => 'active',
        'currency' => 'TWD',
        'tax_rate' => 5,
        'discount' => 0,
        'payment_terms' => 'net30',
    ], $overrides));

    $contract->items()->create([
        'description' => '網站開發',
        'quantity' => 1,
        'unit' => '式',
        'unit_price' => 100000,
        'amount' => 100000,
        'order' => 0,
    ]);
    $contract->recalculate(); // subtotal=100000, tax 5% => total=105000

    return $contract->fresh();
}

it('relates contract to its invoices and computes invoiced/uninvoiced amounts', function () {
    $contract = makeContract(); // total = 105000

    $contract->invoices()->create([
        'client_id' => $contract->client_id,
        'title' => '部分請款',
        'status' => 'draft',
        'subtotal' => 40000,
        'tax_rate' => 0,
        'tax_amount' => 0,
        'discount' => 0,
        'total' => 40000,
        'currency' => 'TWD',
        'issued_date' => now(),
        'due_date' => now()->addDays(30),
    ]);

    $contract = $contract->fresh();
    expect($contract->invoices)->toHaveCount(1);
    expect((float) $contract->invoiced_amount)->toBe(40000.0);
    expect((float) $contract->uninvoiced_amount)->toBe(65000.0); // 105000 - 40000

    $invoice = $contract->invoices->first();
    expect($invoice->contract->id)->toBe($contract->id);
});
```

- [ ] **Step 2: 跑測試確認失敗**

Run: `php artisan test --filter='relates contract to its invoices'`
Expected: FAIL（`invoices()` 方法或 `contract_id` fillable 不存在 → BadMethodCallException 或 MassAssignmentException）

- [ ] **Step 3: 在 `Invoice` 加入 `contract_id` fillable 與 `contract()` 關聯**

`app/Models/Invoice.php` — 將 `'quote_id',` 後加入 `'contract_id',`（位於 `$fillable` 陣列，line 21 之後）：

```php
        'quote_id',
        'contract_id',
        'title',
```

在 `quote()` 方法（line 107-110）之後新增：

```php
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
```

- [ ] **Step 4: 在 `Contract` 加入 `invoices()` 與 accessor**

`app/Models/Contract.php` — 在 `items()`（line 160-163）之後新增關聯：

```php
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->latest();
    }
```

在 `getBalanceDueAttribute()`（line 335-338）之後新增 accessor：

```php
    /**
     * 旗下發票的 total 加總（含稅尺度）。
     */
    public function getInvoicedAmountAttribute(): float
    {
        return round((float) $this->invoices()->sum('total'), 2);
    }

    /**
     * 尚可開立的金額 = 合約 total − 已開發票總額（可為負，UI 自行判斷）。
     */
    public function getUninvoicedAmountAttribute(): float
    {
        return round((float) $this->total - $this->invoiced_amount, 2);
    }
```

- [ ] **Step 5: 在 `Payment` 加入 `invoice_id` fillable 與 `invoice()` 關聯**

`app/Models/Payment.php` — `$fillable`（line 11-18）的 `'amount',` 之前加入 `'invoice_id',`：

```php
    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_method',
        'paid_on',
        'note',
        'proof_path',
        'created_by',
    ];
```

在 `payable()`（line 25-28）之後新增：

```php
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
```

- [ ] **Step 6: 跑測試確認通過**

Run: `php artisan test --filter='relates contract to its invoices'`
Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add app/Models/Contract.php app/Models/Invoice.php app/Models/Payment.php tests/Feature/ContractInvoiceTest.php
git commit -m "feat: add contract<->invoice<->payment relations and invoiced amount accessors"
```

---

## Task 3: 功能一 — 合約手動開票（4 模式 + 路由）

**Files:**
- Modify: `routes/admin.php`（line 192 之前，於合約區塊內加入）
- Modify: `app/Http/Controllers/Admin/ContractController.php`（新增 `createInvoice()`；需 `use App\Models\Invoice;` 與 `use Illuminate\Support\Facades\DB;`）
- Test: `tests/Feature/ContractInvoiceTest.php`

- [ ] **Step 1: 寫失敗測試（4 模式 + 剩餘 + 超開）**

在 `tests/Feature/ContractInvoiceTest.php` 末尾追加測試。

> 注意：`makeContract()` 已在內部 `actingAs()` 一個新使用者，故**凡呼叫 `makeContract()` 的測試都已登入**，不需再額外登入。只有「不經 `makeContract()` 卻仍需登入」的測試（如 Task 4 的「獨立發票」測試會直接 `Invoice::create`，其 `boot()` 取 `auth()->id()`）才需自行 `test()->actingAs(\App\Models\User::create([...]))`。本計畫下列 Task 3 測試皆使用 `makeContract()`，因此**移除原先的 `actingAdmin()` 呼叫**。

接著新增測試：

```php
it('creates a custom-amount invoice (tax_rate 0, single item, draft)', function () {
    actingAdmin();
    $contract = makeContract(); // total 105000

    $response = $this->post(route('admin.contracts.create-invoice', $contract), [
        'mode' => 'custom',
        'amount' => 30000,
    ]);

    $invoice = $contract->fresh()->invoices()->first();
    expect($invoice)->not->toBeNull();
    expect($invoice->contract_id)->toBe($contract->id);
    expect($invoice->status)->toBe('draft');
    expect((float) $invoice->total)->toBe(30000.0);
    expect($invoice->items)->toHaveCount(1);
    $response->assertRedirect(route('admin.invoices.show', $invoice));
});

it('creates a percent invoice = contract total * percent', function () {
    actingAdmin();
    $contract = makeContract(); // total 105000

    $this->post(route('admin.contracts.create-invoice', $contract), [
        'mode' => 'percent',
        'percent' => 30,
    ]);

    $invoice = $contract->fresh()->invoices()->first();
    expect((float) $invoice->total)->toBe(31500.0); // 105000 * 30%
    expect($invoice->items)->toHaveCount(1);
});

it('creates a remaining invoice = total - already invoiced', function () {
    actingAdmin();
    $contract = makeContract(); // total 105000
    // 先開一張 40000
    $contract->invoices()->create([
        'client_id' => $contract->client_id, 'title' => '頭款', 'status' => 'draft',
        'subtotal' => 40000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 40000, 'currency' => 'TWD', 'issued_date' => now(), 'due_date' => now()->addDays(30),
    ]);

    $this->post(route('admin.contracts.create-invoice', $contract), [
        'mode' => 'remaining',
    ]);

    $invoice = $contract->fresh()->invoices()->where('total', '!=', 40000)->first();
    expect((float) $invoice->total)->toBe(65000.0); // 105000 - 40000
});

it('creates a copy_items invoice reproducing contract total with tax', function () {
    actingAdmin();
    $contract = makeContract(); // 1 item 100000, tax 5% => total 105000

    $this->post(route('admin.contracts.create-invoice', $contract), [
        'mode' => 'copy_items',
    ]);

    $invoice = $contract->fresh()->invoices()->first();
    expect($invoice->items)->toHaveCount(1);
    expect((float) $invoice->subtotal)->toBe(100000.0);
    expect((float) $invoice->total)->toBe(105000.0); // 沿用 tax_rate 5%
});

it('warns but still creates an over-invoiced invoice', function () {
    actingAdmin();
    $contract = makeContract(); // total 105000
    // 先把已開總額拉到 105000
    $contract->invoices()->create([
        'client_id' => $contract->client_id, 'title' => '全額', 'status' => 'draft',
        'subtotal' => 105000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 105000, 'currency' => 'TWD', 'issued_date' => now(), 'due_date' => now()->addDays(30),
    ]);

    $response = $this->post(route('admin.contracts.create-invoice', $contract), [
        'mode' => 'custom',
        'amount' => 10000,
    ]);

    expect($contract->fresh()->invoices()->count())->toBe(2); // 仍建立
    $response->assertSessionHas('warning');
});
```

> 已確認：`flash_warning()`（`app/Helpers/helpers.php:159`）寫入的 session key 為 `warning`（非 `flash_warning`）。

- [ ] **Step 2: 跑測試確認失敗**

Run: `php artisan test --filter='create-invoice|custom-amount invoice|percent invoice|remaining invoice|copy_items invoice|over-invoiced'`
Expected: FAIL（route `admin.contracts.create-invoice` 未定義 → RouteNotFoundException）

- [ ] **Step 3: 新增路由**

`routes/admin.php` — 在「合約管理」區塊（line 191-200），於 `Route::resource('contracts', ...)`（line 200）**之前**加入：

```php
        Route::post('contracts/{contract}/invoices', [ContractController::class, 'createInvoice'])->name('contracts.create-invoice');
```

- [ ] **Step 4: 實作 `createInvoice()`**

`app/Http/Controllers/Admin/ContractController.php` — 在 `use` 區塊（line 5-15）補上（若缺）：

```php
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
```

在 `recordPayment()`（line 442）**之前**新增方法：

```php
    /**
     * 功能一：從合約手動開立發票（4 種金額模式）。
     * custom/percent/remaining 為單行模式（tax_rate=0，項目金額即發票 total）；
     * copy_items 逐項複製並沿用合約稅率，total 等於合約 total。
     */
    public function createInvoice(Request $request, Contract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => 'required|in:custom,percent,remaining,copy_items',
            'amount' => 'required_if:mode,custom|nullable|numeric|min:0.01',
            'percent' => 'required_if:mode,percent|nullable|numeric|min:0.01|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $contract->loadMissing('items');

        $invoice = DB::transaction(function () use ($validated, $contract) {
            $invoice = Invoice::create([
                'client_id' => $contract->client_id,
                'project_id' => $contract->project_id,
                'contract_id' => $contract->id,
                'title' => $contract->title,
                'status' => 'draft',
                'subtotal' => 0,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'discount' => 0,
                'total' => 0,
                'currency' => $contract->currency ?? 'TWD',
                'issued_date' => now(),
                'due_date' => now()->addDays(30),
            ]);

            if ($validated['mode'] === 'copy_items') {
                // faithful copy：逐項複製 + 沿用合約稅率/折扣，total 等於合約 total
                foreach ($contract->items as $item) {
                    $invoice->items()->create([
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'unit_price' => $item->unit_price,
                        'amount' => $item->amount,
                        'order' => $item->order,
                    ]);
                }
                $invoice->tax_rate = $contract->tax_rate;
                $invoice->discount = $contract->discount;
                $invoice->save();
            } else {
                // 單行模式：算出目標含稅金額，作為單一項目，tax_rate=0
                $amount = match ($validated['mode']) {
                    'custom' => round((float) $validated['amount'], 2),
                    'percent' => round((float) $contract->total * ((float) $validated['percent'] / 100), 2),
                    'remaining' => round((float) $contract->uninvoiced_amount, 2),
                };

                $invoice->items()->create([
                    // description 為 nullable，未送出時不在 validated() 中，需 ?? null 防 Undefined key
                    'description' => ($validated['description'] ?? null) ?: $contract->title,
                    'quantity' => 1,
                    'unit' => '式',
                    'unit_price' => $amount,
                    'amount' => $amount,
                    'order' => 0,
                ]);
            }

            $invoice->recalculate(); // 重算 subtotal/tax/total

            return $invoice;
        });

        // 超開警告（已開總額已超過合約 total）
        if ($contract->fresh()->invoiced_amount > $contract->total) {
            flash_warning('已開發票總額已超過合約金額，請確認是否為追加項目');
        }

        flash_success('已從合約開立發票');

        return redirect()->route('admin.invoices.show', $invoice);
    }
```

> 註：`remaining` 模式若 `uninvoiced_amount <= 0`，`recalculate()` 後 `total` 為 0 或負；驗證規則未擋（spec 允許追加情境）。此時單一項目 `amount` 可能為 0，發票仍會建立。實務上 UI 會在餘額 ≤ 0 時隱藏此模式（見 Task 5）。

- [ ] **Step 5: 跑測試確認通過**

Run: `php artisan test --filter='custom-amount invoice|percent invoice|remaining invoice|copy_items invoice|over-invoiced'`
Expected: PASS（5 個測試全綠）

- [ ] **Step 6: Commit**

```bash
git add routes/admin.php app/Http/Controllers/Admin/ContractController.php tests/Feature/ContractInvoiceTest.php
git commit -m "feat: contract manual invoice creation with 4 amount modes"
```

---

## Task 4: 功能二 — 收款連動開票

**Files:**
- Modify: `app/Http/Controllers/Admin/ContractController.php`（`recordPayment()` line 442-469 擴充）
- Test: `tests/Feature/ContractInvoiceTest.php`

- [ ] **Step 1: 寫失敗測試（連動已付且綁定、三種項目方式、不勾選＝現狀）**

在 `tests/Feature/ContractInvoiceTest.php` 末尾追加：

```php
it('linked payment creates a paid invoice bound to the payment (summary mode)', function () {
    actingAdmin();
    $contract = makeContract(); // total 105000

    $this->post(route('admin.contracts.record-payment', $contract), [
        'amount' => 50000,
        'create_invoice' => '1',
        'invoice_item_mode' => 'summary',
    ]);

    $contract = $contract->fresh();
    // 合約帳本只加一次，不因發票重複計算
    expect((float) $contract->paid_amount)->toBe(50000.0);

    $invoice = $contract->invoices()->first();
    expect($invoice)->not->toBeNull();
    expect($invoice->status)->toBe('paid');
    expect((float) $invoice->total)->toBe(50000.0);
    expect((float) $invoice->paid_amount)->toBe(50000.0);
    expect($invoice->items)->toHaveCount(1);

    // Payment.invoice_id 指向新發票
    $payment = $contract->payments()->first();
    expect($payment->invoice_id)->toBe($invoice->id);
});

it('linked payment custom mode uses provided description', function () {
    actingAdmin();
    $contract = makeContract();

    $this->post(route('admin.contracts.record-payment', $contract), [
        'amount' => 20000,
        'create_invoice' => '1',
        'invoice_item_mode' => 'custom',
        'invoice_description' => '第二期款',
    ]);

    $invoice = $contract->fresh()->invoices()->first();
    expect($invoice->items->first()->description)->toBe('第二期款');
    expect((float) $invoice->total)->toBe(20000.0);
});

it('linked payment copy mode scales contract items and total equals payment', function () {
    actingAdmin();
    $contract = makeContract(); // 1 item 100000, total 105000

    $this->post(route('admin.contracts.record-payment', $contract), [
        'amount' => 52500, // 合約 total 的一半
        'create_invoice' => '1',
        'invoice_item_mode' => 'copy',
    ]);

    $invoice = $contract->fresh()->invoices()->first();
    expect($invoice->items)->toHaveCount(1);
    // f = 52500/105000 = 0.5 → 項目 50000，含 5% 稅 → total 52500
    expect((float) $invoice->items->first()->amount)->toBe(50000.0);
    expect((float) $invoice->total)->toBe(52500.0);
    expect($invoice->status)->toBe('paid');
});

it('payment without create_invoice records only the contract ledger', function () {
    actingAdmin();
    $contract = makeContract();

    $this->post(route('admin.contracts.record-payment', $contract), [
        'amount' => 30000,
    ]);

    $contract = $contract->fresh();
    expect((float) $contract->paid_amount)->toBe(30000.0);
    expect($contract->invoices()->count())->toBe(0);
    expect($contract->payments()->first()->invoice_id)->toBeNull();
});

it('independent invoice (contract_id null) keeps its own payment ledger intact', function () {
    actingAdmin();
    $client = \App\Models\Client::create(['name' => '獨立客戶']);

    $invoice = Invoice::create([
        'client_id' => $client->id,
        'contract_id' => null,
        'title' => '獨立發票',
        'status' => 'sent',
        'subtotal' => 10000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 10000, 'currency' => 'TWD',
        'issued_date' => now(), 'due_date' => now()->addDays(30),
    ]);

    // 走發票自身帳本（spec 決策一：獨立發票行為不變）
    $invoice->recordPayment(10000, '現金');

    $invoice = $invoice->fresh();
    expect($invoice->contract_id)->toBeNull();
    expect((float) $invoice->paid_amount)->toBe(10000.0);
    expect($invoice->status)->toBe('paid');
    expect($invoice->payments()->count())->toBe(1); // 收款記在發票自己帳本
});
```

- [ ] **Step 2: 跑測試確認失敗**

Run: `php artisan test --filter='linked payment|payment without create_invoice'`
Expected: FAIL（連動發票未建立 → `$invoice` 為 null，斷言失敗）

- [ ] **Step 3: 擴充 `recordPayment()`**

`app/Http/Controllers/Admin/ContractController.php` — 將 `recordPayment()`（line 442-469）整段替換為：

```php
    /**
     * 登記一筆收款；可選擇同時為此筆款開立發票（功能二）。
     */
    public function recordPayment(Request $request, Contract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:'.$contract->balance_due,
            'payment_method' => 'nullable|string|max:255',
            'paid_on' => 'nullable|date',
            'note' => 'nullable|string|max:500',
            'proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            // 連動開票
            'create_invoice' => 'nullable|boolean',
            'invoice_item_mode' => 'nullable|in:summary,custom,copy',
            'invoice_description' => 'nullable|string|max:255',
        ]);

        // 收款憑證（轉帳截圖／收據，選填）
        $proofPath = null;
        if ($request->hasFile('proof')) {
            $file = $request->file('proof');
            $proofPath = $file->storeAs('uploads/'.date('Y/m'), \Illuminate\Support\Str::uuid().'.'.$file->getClientOriginalExtension(), 'public');
        }

        $amount = (float) $validated['amount'];

        DB::transaction(function () use ($request, $validated, $contract, $amount, $proofPath) {
            // 1) 記合約收款（唯一真實來源，現狀不動）
            $payment = $contract->recordPayment(
                $amount,
                $validated['payment_method'] ?? null,
                $validated['paid_on'] ?? null,
                $validated['note'] ?? null,
                $proofPath,
            );

            // 2) 勾選才連動開票
            if ($request->boolean('create_invoice')) {
                $invoice = $this->buildLinkedInvoice(
                    $contract,
                    $amount,
                    $validated['invoice_item_mode'] ?? 'summary',
                    $validated['invoice_description'] ?? null,
                );
                $payment->update(['invoice_id' => $invoice->id]);
            }
        });

        flash_success($request->boolean('create_invoice') ? '收款已登記並開立發票' : '收款已登記');

        return redirect()->route('admin.contracts.show', $contract);
    }

    /**
     * 建立「收款連動發票」：已付請款文件，total 等於收款額，不自成帳本。
     */
    private function buildLinkedInvoice(Contract $contract, float $amount, string $itemMode, ?string $description): Invoice
    {
        $contract->loadMissing('items');

        $invoice = Invoice::create([
            'client_id' => $contract->client_id,
            'project_id' => $contract->project_id,
            'contract_id' => $contract->id,
            'title' => $contract->title,
            'status' => 'paid',
            'subtotal' => 0,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'discount' => 0,
            'total' => 0,
            'currency' => $contract->currency ?? 'TWD',
            'issued_date' => now(),
            'due_date' => now(),
        ]);

        if ($itemMode === 'copy' && $contract->total > 0 && $contract->items->isNotEmpty()) {
            // 等比縮放：f = 收款額 / 合約total；沿用稅率/折扣使 total = 收款額
            $f = $amount / (float) $contract->total;
            foreach ($contract->items as $item) {
                $scaled = round((float) $item->amount * $f, 2);
                $invoice->items()->create([
                    'description' => $item->description,
                    'quantity' => 1,
                    'unit' => $item->unit,
                    'unit_price' => $scaled,
                    'amount' => $scaled,
                    'order' => $item->order,
                ]);
            }
            $invoice->tax_rate = $contract->tax_rate;
            $invoice->discount = round((float) $contract->discount * $f, 2);
            $invoice->save();
        } else {
            // summary / custom：單一項目，金額即收款額
            $desc = $itemMode === 'custom' && $description
                ? $description
                : "合約 {$contract->contract_number} 款項";

            $invoice->items()->create([
                'description' => $desc,
                'quantity' => 1,
                'unit' => '式',
                'unit_price' => $amount,
                'amount' => $amount,
                'order' => 0,
            ]);
        }

        $invoice->recalculate(); // 設 subtotal/tax/total
        // 直接標記為已付（不走發票自身帳本，避免雙重計算）
        $invoice->paid_amount = $invoice->total;
        $invoice->status = 'paid';
        $invoice->paid_at = now();
        $invoice->save();

        return $invoice;
    }
```

> 重要：連動發票**不呼叫** `$invoice->recordPayment()`，而是直接寫 `paid_amount`／`status`，因此不會在發票自身帳本新增 Payment，金流仍只記在合約帳本一處（spec 決策一）。

- [ ] **Step 4: 跑測試確認通過**

Run: `php artisan test --filter='linked payment|payment without create_invoice'`
Expected: PASS（4 個測試全綠）

- [ ] **Step 5: 回歸 — 確認獨立發票與既有合約收款不受影響**

Run: `php artisan test --filter='Invoice|Contract'`
Expected: PASS（既有相關測試全綠；若無既有測試則略過）

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Admin/ContractController.php tests/Feature/ContractInvoiceTest.php
git commit -m "feat: link contract payment to an auto-generated paid invoice"
```

---

## Task 5: 視圖呈現（開票面板 + 相關發票 + 收款連動欄位 + 來源合約連結）

**Files:**
- Modify: `resources/views/admin/contracts/show.blade.php`（收款 Modal line 386-433；右側資訊區另尋插入點）
- Modify: `resources/views/admin/invoices/show.blade.php`
- 無新測試（純呈現層；以 `npm run build` + view:cache 驗證 Blade 語法）

- [ ] **Step 1: 在收款 Modal 加入連動開票欄位**

`resources/views/admin/contracts/show.blade.php` — 在收款憑證欄位（line 419-423 的 `proof` 區塊）**之後**、`</div>`（modal-body 結束 line 424）**之前**插入：

```blade
                    <hr>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="createInvoiceCheck" name="create_invoice" value="1"
                               onchange="document.getElementById('invoiceItemModeWrap').classList.toggle('d-none', !this.checked)">
                        <label class="form-check-label" for="createInvoiceCheck">同時為此筆款開立發票</label>
                    </div>
                    <div id="invoiceItemModeWrap" class="d-none">
                        <div class="mb-3">
                            <label class="form-label">發票項目方式</label>
                            <select name="invoice_item_mode" class="form-select"
                                    onchange="document.getElementById('invoiceDescWrap').classList.toggle('d-none', this.value !== 'custom')">
                                <option value="summary">摘要（合約款項，金額＝收款額）</option>
                                <option value="custom">自訂描述</option>
                                <option value="copy">複製合約項目（等比縮放）</option>
                            </select>
                        </div>
                        <div id="invoiceDescWrap" class="mb-3 d-none">
                            <label class="form-label">發票描述</label>
                            <input type="text" name="invoice_description" class="form-control" placeholder="留空則用摘要預設">
                        </div>
                    </div>
```

- [ ] **Step 2: 加入「相關發票」清單與開票面板**

`resources/views/admin/contracts/show.blade.php` — 在收款 Modal（line 386 的 `{{-- 登記收款 Modal --}}`）**之前**插入「相關發票」卡片與「開立發票」Modal：

```blade
{{-- 相關發票 --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>相關發票</strong>
        <button type="button" class="btn btn-sm btn-primary" data-coreui-toggle="modal" data-coreui-target="#createInvoiceModal">
            <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-plus"></use></svg> 開立發票
        </button>
    </div>
    <div class="card-body">
        <div class="row text-center mb-3">
            <div class="col"><div class="small text-medium-emphasis">合約總額</div><div class="fw-semibold">NT$ {{ number_format($contract->total) }}</div></div>
            <div class="col"><div class="small text-medium-emphasis">已開發票</div><div class="fw-semibold">NT$ {{ number_format($contract->invoiced_amount) }}</div></div>
            <div class="col"><div class="small text-medium-emphasis">可開餘額</div><div class="fw-semibold {{ $contract->uninvoiced_amount < 0 ? 'text-danger' : '' }}">NT$ {{ number_format($contract->uninvoiced_amount) }}</div></div>
        </div>
        @if($contract->invoices->isEmpty())
            <p class="text-medium-emphasis mb-0 small">尚無發票</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>發票號</th><th class="text-end">金額</th><th>狀態</th><th></th></tr></thead>
                    <tbody>
                        @foreach($contract->invoices as $inv)
                        <tr>
                            <td>{{ $inv->invoice_number }}</td>
                            <td class="text-end">NT$ {{ number_format($inv->total) }}</td>
                            <td><span class="badge bg-{{ $inv->status_color }}">{{ $inv->status_label }}</span></td>
                            <td class="text-end"><a href="{{ route('admin.invoices.show', $inv) }}" class="btn btn-sm btn-light">檢視</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- 開立發票 Modal --}}
<div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.contracts.create-invoice', $contract) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">從合約開立發票</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">金額模式</label>
                        <select name="mode" class="form-select" id="createInvoiceMode" onchange="
                            document.getElementById('ciAmountWrap').classList.toggle('d-none', this.value !== 'custom');
                            document.getElementById('ciPercentWrap').classList.toggle('d-none', this.value !== 'percent');">
                            <option value="custom">自訂金額</option>
                            <option value="percent">按比例（合約總額的百分比）</option>
                            <option value="remaining">剩餘未開全額（NT$ {{ number_format($contract->uninvoiced_amount) }}）</option>
                            <option value="copy_items">複製合約所有項目</option>
                        </select>
                    </div>
                    <div class="mb-3" id="ciAmountWrap">
                        <label class="form-label">金額</label>
                        <div class="input-group">
                            <span class="input-group-text">NT$</span>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01">
                        </div>
                    </div>
                    <div class="mb-3 d-none" id="ciPercentWrap">
                        <label class="form-label">百分比</label>
                        <div class="input-group">
                            <input type="number" name="percent" class="form-control" step="0.01" min="0.01" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">發票項目描述（單行模式適用，選填）</label>
                        <input type="text" name="description" class="form-control" placeholder="留空則用合約標題">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">建立發票</button>
                </div>
            </form>
        </div>
    </div>
</div>
```

> 註：`$contract->invoices` 已於 `ContractController::show()` 的 `load([...])` 之外延遲載入；為避免 N+1，於 Step 4 在 controller 的 `load()` 陣列補上 `'invoices'`。

- [ ] **Step 3: 發票 show 頁加入「來源合約」連結**

`resources/views/admin/invoices/show.blade.php` — 找到顯示「來源報價單」或發票後設資訊的區塊（以 `grep -n "quote\|報價單\|invoice_number\|客戶" resources/views/admin/invoices/show.blade.php` 定位），在客戶／報價單資訊附近加入：

```blade
@if($invoice->contract_id)
    <div class="mb-2">
        <span class="text-medium-emphasis">來源合約：</span>
        <a href="{{ route('admin.contracts.show', $invoice->contract_id) }}">
            {{ $invoice->contract->contract_number ?? '檢視合約' }}
        </a>
    </div>
@endif
```

- [ ] **Step 4: controller 預載 invoices（避免 N+1）**

`app/Http/Controllers/Admin/ContractController.php` — `show()` 的 `$contract->load([...])`（line 192）加入 `'invoices'`：

```php
        $contract->load(['client', 'project', 'creator', 'items', 'quote', 'payments', 'invoices']);
```

並確認 `InvoiceController::show()` 對 `$invoice` 有載入 `contract`（若使用 lazy load 則 Blade 內 `$invoice->contract` 會自動查詢，可接受；如要避免額外查詢，於該 controller 的 `show()` load 陣列加入 `'contract'`）。

- [ ] **Step 5: 驗證 Blade 語法與 SCSS 編譯**

Run: `npm run build && php artisan view:cache`
Expected: 編譯成功、無 Blade ParseError。完成後執行 `php artisan view:clear`。

- [ ] **Step 6: 跑整套測試確認無回歸**

Run: `php artisan test --filter=ContractInvoice`
Expected: PASS（功能一、功能二全部測試）

- [ ] **Step 7: Pint 格式化**

Run: `./vendor/bin/pint app/Http/Controllers/Admin/ContractController.php app/Models/Contract.php app/Models/Invoice.php app/Models/Payment.php`
Expected: 無錯誤（自動修正格式）

- [ ] **Step 8: Commit**

```bash
git add resources/views/admin/contracts/show.blade.php \
        resources/views/admin/invoices/show.blade.php \
        app/Http/Controllers/Admin/ContractController.php
git commit -m "feat: contract show invoice panel, linked-payment form, invoice source-contract link"
```

---

## 完成後驗證清單（verification-before-completion）

- [ ] `php artisan test --filter=ContractInvoice` 全綠
- [ ] `./vendor/bin/pint --test`（或已執行 pint）無待修正
- [ ] `./vendor/bin/phpstan analyse app/Models/Contract.php app/Models/Invoice.php app/Http/Controllers/Admin/ContractController.php`（Level 6）無新增錯誤
- [ ] 手動：合約 show 頁可開 4 模式發票、可登記收款並連動開票、相關發票清單與餘額正確
- [ ] 手動：合約發票的發票頁顯示「來源合約」連結；獨立發票（Quote→Invoice）行為不變
```

