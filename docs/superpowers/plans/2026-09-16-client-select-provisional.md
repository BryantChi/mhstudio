# 暫定客戶與單據客戶選擇 實作計畫

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 讓報價單／合約／發票的客戶欄位可搜尋，且客戶未建檔時能在表單內直接建立「暫定客戶」，不需離開表單。

**Architecture:** 在 `clients` 加一個 `is_provisional` 旗標，`client_id` 維持 NOT NULL，因此收款、PDF、寄信、搜尋、報表等既有邏輯完全不動。前端用 Select2（CDN + jQuery）做可搜尋下拉，選到新客戶時立即 AJAX 建檔並把真實 id 換回 select，所以三個 Controller 的 `exists:clients,id` 驗證一行都不用改。六個表單共用同一個 Blade partial。

**Tech Stack:** Laravel 11 / Blade / CoreUI 5 / Bootstrap 5 / Select2 4.1 + jQuery 3（jsdelivr CDN）/ Pest + MySQL 測試庫

**設計文件：** `docs/CLIENT-PROVISIONAL.md`

---

## File Structure

| 檔案 | 責任 |
|---|---|
| `database/migrations/*_add_is_provisional_to_clients_table.php` | 新增 `is_provisional` 欄位與索引 |
| `app/Models/Client.php` | fillable / cast / `scopeProvisional` / `scopeFormal` |
| `app/Http/Controllers/Admin/ClientController.php` | `quickStore()` JSON API、`promote()` 轉正、`index()` 篩選、`update()` 接受旗標 |
| `routes/admin.php` | 兩條新路由（必須在 `Route::resource('clients')` 之前） |
| `resources/views/admin/partials/client-select.blade.php` | **唯一**的客戶選擇元件：下拉 + Select2 + 快速建立視窗 + AJAX |
| `resources/views/admin/{quotes,contracts,invoices}/{create,edit}.blade.php` | 各自換成一行 `@include` |
| `resources/views/admin/clients/{index,show,edit}.blade.php` | 暫定徽章、篩選、轉正按鈕、勾選框 |
| `tests/Feature/ProvisionalClientTest.php` | 本功能全部測試 |

---

### Task 1: 資料欄位與 Model

**Files:**
- Create: `database/migrations/2026_09_16_000001_add_is_provisional_to_clients_table.php`
- Modify: `app/Models/Client.php`
- Test: `tests/Feature/ProvisionalClientTest.php`

- [ ] **Step 1: 寫失敗測試**

```php
<?php

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('clients 表有 is_provisional 欄位且預設為 false', function () {
    expect(Schema::hasColumn('clients', 'is_provisional'))->toBeTrue();

    $client = Client::create(['name' => '正式客戶']);

    expect($client->fresh()->is_provisional)->toBeFalse();
});

it('provisional 與 formal scope 能正確分流', function () {
    Client::create(['name' => '正式客戶']);
    Client::create(['name' => '暫定客戶', 'is_provisional' => true]);

    expect(Client::provisional()->pluck('name')->all())->toBe(['暫定客戶']);
    expect(Client::formal()->pluck('name')->all())->toBe(['正式客戶']);
});
```

- [ ] **Step 2: 執行測試確認失敗**

Run: `php artisan test --filter=ProvisionalClientTest`
Expected: FAIL — `Schema::hasColumn` 回傳 false

- [ ] **Step 3: 建立 migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // 暫定客戶：於報價單/合約/發票表單直接建立、資料尚未補齊者
            $table->boolean('is_provisional')->default(false)->after('status');
            $table->index('is_provisional');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['is_provisional']);
            $table->dropColumn('is_provisional');
        });
    }
};
```

- [ ] **Step 4: 修改 Client model**

`$fillable` 陣列末端（`'user_id'` 之後）加入 `'is_provisional'`；`$casts` 加入 `'is_provisional' => 'boolean'`；在既有 `scopeLeads()` 之後加入：

```php
    /** 暫定客戶：開單當下順手建立、資料未補齊 */
    public function scopeProvisional(Builder $query): void
    {
        $query->where('is_provisional', true);
    }

    /** 正式客戶 */
    public function scopeFormal(Builder $query): void
    {
        $query->where('is_provisional', false);
    }
```

- [ ] **Step 5: 跑 migration 並驗證測試通過**

Run: `php artisan migrate && php artisan test --filter=ProvisionalClientTest`
Expected: PASS（2 passed）

- [ ] **Step 6: 提交**

提交檔案：`database/migrations`、`app/Models/Client.php`、`tests/Feature/ProvisionalClientTest.php`
訊息：`客戶: 新增 is_provisional 暫定旗標與 provisional/formal scope`

---

### Task 2: 快速建立客戶 API

**Files:**
- Modify: `routes/admin.php`（`Route::resource('clients', ...)` 那行之前）
- Modify: `app/Http/Controllers/Admin/ClientController.php`
- Test: `tests/Feature/ProvisionalClientTest.php`

- [ ] **Step 1: 寫失敗測試**（append 到測試檔，並在檔頭加 `use App\Models\User;`）

```php
function actingAsAdmin(): void
{
    test()->actingAs(User::create([
        'name' => '測試人員',
        'email' => 'tester'.uniqid().'@example.com',
        'password' => 'password',
    ]));
}

it('只給名稱即可建立暫定客戶並回傳 JSON', function () {
    actingAsAdmin();

    $response = $this->postJson(route('admin.clients.quick-store'), ['name' => '大東實業']);

    $response->assertOk()->assertJson(['name' => '大東實業', 'is_provisional' => true]);

    $client = Client::firstWhere('name', '大東實業');
    // 未指定時沿用既有客戶預設語意，方便日後在客戶管理接手
    expect($client->status)->toBe('lead')
        ->and($client->source)->toBe('other')
        ->and($client->tier)->toBe('standard');
});

it('快速建立可帶入聯絡資料並指定為正式客戶', function () {
    actingAsAdmin();

    $this->postJson(route('admin.clients.quick-store'), [
        'name' => '永昌科技',
        'contact_person' => '林經理',
        'email' => 'lin@example.com',
        'phone' => '02-1234-5678',
        'company' => '永昌科技股份有限公司',
        'tax_id' => '12345678',
        'is_provisional' => false,
    ])->assertOk();

    $client = Client::firstWhere('name', '永昌科技');
    expect($client->is_provisional)->toBeFalse()
        ->and($client->tax_id)->toBe('12345678')
        ->and($client->contact_person)->toBe('林經理');
});

it('快速建立缺少名稱時回傳驗證錯誤', function () {
    actingAsAdmin();

    $this->postJson(route('admin.clients.quick-store'), ['email' => 'x@example.com'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});
```

- [ ] **Step 2: 執行測試確認失敗**

Run: `php artisan test --filter=ProvisionalClientTest`
Expected: FAIL — `Route [admin.clients.quick-store] not defined`

- [ ] **Step 3: 加路由**

在 `routes/admin.php` 的 `Route::resource('clients', ClientController::class);` **之前**插入：

```php
        // 快速建立客戶（表單內 AJAX），須置於 resource 之前避免被 clients/{client} 匹配
        Route::post('clients/quick-store', [ClientController::class, 'quickStore'])->name('clients.quick-store');
```

- [ ] **Step 4: 實作 quickStore**

`ClientController` 檔頭加 `use Illuminate\Http\JsonResponse;`，並在 `store()` 之後加入：

```php
    /**
     * 表單內快速建立客戶（AJAX）。
     * 與 store() 分開的原因：store() 強制要求 source/status/tier 且回傳 redirect，AJAX 接不到。
     */
    public function quickStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:20',
        ]);

        $client = Client::create($validated + [
            'status' => 'lead',
            'source' => 'other',
            'tier' => 'standard',
            'is_provisional' => $request->boolean('is_provisional', true),
        ]);

        return response()->json([
            'id' => $client->id,
            'name' => $client->name,
            'company' => $client->company,
            'is_provisional' => $client->is_provisional,
        ]);
    }
```

- [ ] **Step 5: 驗證測試通過**

Run: `php artisan test --filter=ProvisionalClientTest`
Expected: PASS（5 passed）

- [ ] **Step 6: 提交**

提交檔案：`routes/admin.php`、`app/Http/Controllers/Admin/ClientController.php`、測試檔
訊息：`客戶: 新增表單內快速建立客戶的 JSON API`

---

### Task 3: 轉為正式客戶

**Files:**
- Modify: `routes/admin.php`
- Modify: `app/Http/Controllers/Admin/ClientController.php`（`promote()`、`update()`）
- Test: `tests/Feature/ProvisionalClientTest.php`

- [ ] **Step 1: 寫失敗測試**

```php
it('可將暫定客戶一鍵轉為正式客戶', function () {
    actingAsAdmin();
    $client = Client::create(['name' => '小林設計', 'is_provisional' => true]);

    $this->post(route('admin.clients.promote', $client))
        ->assertRedirect(route('admin.clients.show', $client));

    expect($client->fresh()->is_provisional)->toBeFalse();
});

it('編輯客戶時取消暫定勾選即轉正', function () {
    actingAsAdmin();
    $client = Client::create(['name' => '小林設計', 'is_provisional' => true]);

    $this->put(route('admin.clients.update', $client), [
        'name' => '小林設計',
        'source' => 'other',
        'status' => 'lead',
        'tier' => 'standard',
        // is_provisional 未勾選 → 表單不會送出此欄位
    ])->assertRedirect();

    expect($client->fresh()->is_provisional)->toBeFalse();
});

it('編輯客戶時維持暫定勾選則仍為暫定', function () {
    actingAsAdmin();
    $client = Client::create(['name' => '小林設計', 'is_provisional' => true]);

    $this->put(route('admin.clients.update', $client), [
        'name' => '小林設計',
        'source' => 'other',
        'status' => 'lead',
        'tier' => 'standard',
        'is_provisional' => '1',
    ])->assertRedirect();

    expect($client->fresh()->is_provisional)->toBeTrue();
});
```

- [ ] **Step 2: 執行測試確認失敗**

Run: `php artisan test --filter=ProvisionalClientTest`
Expected: FAIL — `Route [admin.clients.promote] not defined`

- [ ] **Step 3: 加路由**

於 `clients/quick-store` 下一行加入：

```php
        Route::post('clients/{client}/promote', [ClientController::class, 'promote'])->name('clients.promote');
```

- [ ] **Step 4: 實作 promote 並讓 update 接受旗標**

```php
    /** 暫定客戶轉為正式客戶 */
    public function promote(Client $client): RedirectResponse
    {
        $client->update(['is_provisional' => false]);
        flash_success('已轉為正式客戶');

        return redirect()->route('admin.clients.show', $client);
    }
```

`update()` 的 `$request->validate([...])` 陣列加入一行 `'is_provisional' => 'nullable|boolean',`，並在既有的 tags 處理之後加入：

```php
        // checkbox 未勾選時不會送出，需明確補 false 才能藉由編輯頁轉正
        $validated['is_provisional'] = $request->boolean('is_provisional');
```

- [ ] **Step 5: 驗證測試通過**

Run: `php artisan test --filter=ProvisionalClientTest`
Expected: PASS（8 passed）

- [ ] **Step 6: 提交**

提交檔案：`routes/admin.php`、`ClientController.php`、測試檔
訊息：`客戶: 支援暫定客戶轉正（快捷路由與編輯頁旗標）`

---

### Task 4: 客戶管理三個頁面

**Files:**
- Modify: `app/Http/Controllers/Admin/ClientController.php`（`index()`）
- Modify: `resources/views/admin/clients/index.blade.php`（篩選 + 徽章）
- Modify: `resources/views/admin/clients/show.blade.php`（提示條 + 轉正按鈕）
- Modify: `resources/views/admin/clients/edit.blade.php`（暫定勾選框）
- Test: `tests/Feature/ProvisionalClientTest.php`

- [ ] **Step 1: 寫失敗測試**

```php
it('客戶列表可依暫定狀態篩選', function () {
    actingAsAdmin();
    Client::create(['name' => '正式客戶']);
    Client::create(['name' => '暫定客戶', 'is_provisional' => true]);

    $onlyProvisional = $this->get(route('admin.clients.index', ['provisional' => 'only']))->viewData('clients');
    expect($onlyProvisional->pluck('name')->all())->toBe(['暫定客戶']);

    $onlyFormal = $this->get(route('admin.clients.index', ['provisional' => 'exclude']))->viewData('clients');
    expect($onlyFormal->pluck('name')->all())->toBe(['正式客戶']);

    $all = $this->get(route('admin.clients.index'))->viewData('clients');
    expect($all)->toHaveCount(2);
});

it('暫定客戶在列表與詳情頁顯示暫定標示', function () {
    actingAsAdmin();
    $client = Client::create(['name' => '小林設計', 'is_provisional' => true]);

    $this->get(route('admin.clients.index'))->assertOk()->assertSee('暫定');
    $this->get(route('admin.clients.show', $client))->assertOk()->assertSee('轉為正式客戶');
});
```

- [ ] **Step 2: 執行測試確認失敗**

Run: `php artisan test --filter=ProvisionalClientTest`
Expected: FAIL — 篩選未實作，`$onlyProvisional` 仍為 2 筆

- [ ] **Step 3: index 加篩選**

在 `ClientController::index()` 既有篩選條件之後加入：

```php
        // 暫定客戶篩選：only=僅暫定、exclude=僅正式、其他=全部
        if ($request->provisional === 'only') {
            $query->provisional();
        } elseif ($request->provisional === 'exclude') {
            $query->formal();
        }
```

- [ ] **Step 4: 三個 Blade 加 UI**

`clients/index.blade.php` — 篩選表單加入下拉（與既有 status/tier 篩選同一列）：

```blade
<select name="provisional" class="form-select" onchange="this.form.submit()">
    <option value="">全部客戶</option>
    <option value="exclude" {{ request('provisional') === 'exclude' ? 'selected' : '' }}>僅正式客戶</option>
    <option value="only" {{ request('provisional') === 'only' ? 'selected' : '' }}>僅暫定客戶</option>
</select>
```

列表與卡片的客戶名稱後加上徽章：

```blade
@if($client->is_provisional)
    <span class="badge bg-secondary ms-1" data-coreui-toggle="tooltip" title="開單時建立，資料尚未補齊">暫定</span>
@endif
```

`clients/show.blade.php` — 頁面內容最上方加入提示條：

```blade
@if($client->is_provisional)
    <div class="alert alert-secondary d-flex justify-content-between align-items-center">
        <div>
            <svg class="icon me-1"><use xlink:href="/assets/icons/free.svg#cil-info"></use></svg>
            這是開單時快速建立的<strong>暫定客戶</strong>，資料可能尚未補齊。
        </div>
        <form method="POST" action="{{ route('admin.clients.promote', $client) }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-primary">轉為正式客戶</button>
        </form>
    </div>
@endif
```

`clients/edit.blade.php` — 於狀態欄位附近加入勾選框：

```blade
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" value="1" id="is_provisional" name="is_provisional"
           {{ old('is_provisional', $client->is_provisional) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_provisional">
        暫定客戶
        <small class="text-muted d-block">開單時快速建立、資料尚未補齊。取消勾選並儲存即轉為正式客戶。</small>
    </label>
</div>
```

- [ ] **Step 5: 驗證測試與 Blade 語法**

Run:
```bash
php artisan test --filter=ProvisionalClientTest
php artisan view:clear && php artisan view:cache
for f in storage/framework/views/*.php; do php -l "$f" >/dev/null || echo "語法錯誤: $f"; done
php artisan view:clear
```
Expected: 測試 10 passed；php -l 無任何輸出（Blade 編譯產物皆通過語法檢查）

- [ ] **Step 6: 提交**

提交檔案：`ClientController.php`、`resources/views/admin/clients`、測試檔
訊息：`客戶: 列表可篩選暫定客戶，詳情頁可一鍵轉正、編輯頁可切換暫定`

---

### Task 5: 共用客戶選擇元件

**Files:**
- Create: `resources/views/admin/partials/client-select.blade.php`

元件契約（include 時傳入）：

| 參數 | 預設 | 用途 |
|---|---|---|
| `$clients` | 必要 | Client collection，由各 Controller 既有的 `create()`／`edit()` 傳入 |
| `$selected` | `null` | 預選的 client id |
| `$required` | `true` | 是否必填 |

- [ ] **Step 1: 建立 partial**

結構依序為：(1) label 與 `<select name="client_id" data-client-select>`（保留既有 `@error` 樣式）、(2) 快速建立 modal（6 欄 + 暫定勾選）、(3) `@push('styles')` 載入 Select2 與 bootstrap-5 主題 CSS、(4) `@push('scripts')` 載入 jQuery + Select2 並初始化。

關鍵 JS 行為：

```javascript
// tags:true 讓打字產生暫時項目；選到它就立刻建檔並換回真實 id，
// 這樣送出時 client_id 一定是合法 id，三個 Controller 的 exists 驗證都不用改
$select.select2({
    theme: 'bootstrap-5',
    width: '100%',
    tags: true,
    language: {
        noResults: () => '找不到客戶，直接打名字即可建立',
    },
    createTag: params => {
        const term = params.term.trim();
        return term ? { id: '__new__', text: '＋ 用「' + term + '」開單（暫定客戶）', isNew: true, term } : null;
    },
});

$select.on('select2:select', function (e) {
    if (!e.params.data.isNew) return;
    quickCreateClient({ name: e.params.data.term, is_provisional: true });
});

function quickCreateClient(payload) {
    return fetch(QUICK_STORE_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(payload),
    })
    .then(res => res.ok ? res.json() : res.json().then(e => Promise.reject(e)))
    .then(client => {
        $select.find('option[value="__new__"]').remove();
        const label = client.name + (client.is_provisional ? '（暫定）' : '');
        $select.append(new Option(label, client.id, true, true)).trigger('change');
        return client;
    });
}
```

`QUICK_STORE_URL` 以 `{{ route('admin.clients.quick-store') }}` 產生，避免硬寫 admin 前綴（前綴可由 `ADMIN_PREFIX` 變更）。

失敗處理：`.catch()` 時移除 `__new__` 選項、清空選取，並在欄位下方顯示錯誤訊息，避免使用者誤以為已建立。

- [ ] **Step 2: 語法驗證**

Run:
```bash
php artisan view:clear && php artisan view:cache
for f in storage/framework/views/*.php; do php -l "$f" >/dev/null || echo "語法錯誤: $f"; done
php artisan view:clear
```
Expected: 無輸出

- [ ] **Step 3: 提交**

提交檔案：`resources/views/admin/partials/client-select.blade.php`
訊息：`新增共用客戶選擇元件：可搜尋下拉與表單內快速建立客戶`

---

### Task 6: 六個表單改用共用元件

**Files:**
- Modify: `resources/views/admin/quotes/create.blade.php:131`、`quotes/edit.blade.php:120`
- Modify: `resources/views/admin/contracts/create.blade.php:155`、`contracts/edit.blade.php:155`
- Modify: `resources/views/admin/invoices/create.blade.php`、`invoices/edit.blade.php:104`
- Test: `tests/Feature/ProvisionalClientTest.php`

- [ ] **Step 1: 寫失敗測試**

測試輔助函式（放在測試檔上方，沿用 `ContractInvoiceTest.php` 的手動建資料風格，專案無 factory）：

```php
function makeQuoteFor(Client $client): \App\Models\Quote
{
    return \App\Models\Quote::create([
        'client_id' => $client->id, 'title' => '測試報價', 'status' => 'draft',
        'tax_rate' => 5, 'discount' => 0, 'currency' => 'TWD',
    ]);
}

function makeContractFor(Client $client): \App\Models\Contract
{
    return \App\Models\Contract::create([
        'client_id' => $client->id, 'title' => '測試合約', 'content' => '內容',
        'type' => 'service', 'status' => 'draft', 'currency' => 'TWD',
        'tax_rate' => 5, 'discount' => 0, 'payment_terms' => 'net30',
    ]);
}

function makeInvoiceFor(Client $client): \App\Models\Invoice
{
    return \App\Models\Invoice::create([
        'client_id' => $client->id, 'title' => '測試發票', 'status' => 'draft',
        'tax_rate' => 5, 'discount' => 0, 'currency' => 'TWD',
        'issued_date' => now(), 'due_date' => now()->addDays(30),
    ]);
}
```

```php
it('六個單據表單都使用可搜尋的客戶選擇元件', function () {
    actingAsAdmin();
    $client = Client::create(['name' => '大東實業']);
    $quote = makeQuoteFor($client);
    $contract = makeContractFor($client);
    $invoice = makeInvoiceFor($client);

    $urls = [
        route('admin.quotes.create'), route('admin.quotes.edit', $quote),
        route('admin.contracts.create'), route('admin.contracts.edit', $contract),
        route('admin.invoices.create'), route('admin.invoices.edit', $invoice),
    ];

    foreach ($urls as $url) {
        // data-client-select 是共用元件的標記，確保六頁都吃到同一個元件
        $this->get($url)->assertOk()->assertSee('data-client-select', false);
    }
});

it('用暫定客戶建立報價單可正常送出', function () {
    actingAsAdmin();
    $client = Client::create(['name' => '大東實業', 'is_provisional' => true]);

    $this->post(route('admin.quotes.store'), [
        'client_id' => $client->id,
        'title' => '網站改版報價',
        'status' => 'draft',
        'tax_rate' => 5,
        'items' => [['description' => '首頁設計', 'quantity' => 1, 'unit' => '式', 'unit_price' => 50000]],
    ])->assertRedirect();

    expect(\App\Models\Quote::firstWhere('title', '網站改版報價')->client->name)->toBe('大東實業');
});
```

- [ ] **Step 2: 執行測試確認失敗**

Run: `php artisan test --filter=ProvisionalClientTest`
Expected: FAIL — 頁面中找不到 `data-client-select`

- [ ] **Step 3: 六個表單各換成一行**

把原本整段 `<label>…<select name="client_id">…</select>…@error` 換成：

```blade
@include('admin.partials.client-select', ['clients' => $clients, 'selected' => old('client_id', $selectedClientId ?? null)])
```

編輯頁的 `selected` 改為對應模型：`$quote->client_id`／`$contract->client_id`／`$invoice->client_id`。

- [ ] **Step 4: 驗證測試與 Blade 語法**

Run:
```bash
php artisan test --filter=ProvisionalClientTest
php artisan view:clear && php artisan view:cache
for f in storage/framework/views/*.php; do php -l "$f" >/dev/null || echo "語法錯誤: $f"; done
php artisan view:clear
```
Expected: 測試 12 passed；php -l 無輸出

- [ ] **Step 5: 提交**

提交檔案：`resources/views/admin/quotes`、`resources/views/admin/contracts`、`resources/views/admin/invoices`、測試檔
訊息：`報價單/合約/發票: 客戶欄位改用共用可搜尋元件`

---

### Task 7: 全案驗證

- [ ] **Step 1: 全測試套件**

Run: `php artisan test`
Expected: 全綠。特別確認 `ContractInvoiceTest`（收款與營收口徑）未受影響

- [ ] **Step 2: 靜態分析與格式**

Run: `./vendor/bin/pint && ./vendor/bin/phpstan analyse`
Expected: Pint 完成格式化；PHPStan Level 6 無錯誤

- [ ] **Step 3: 前端建置**

Run: `npm run build`
Expected: 成功（本功能走 CDN，不應影響既有 bundle）

- [ ] **Step 4: 人工確認清單**

1. 報價單新增頁：打字可過濾客戶
2. 打不存在的名字 → 出現「＋ 用「xxx」開單（暫定客戶）」→ 點擊後欄位顯示「xxx（暫定）」
3. 直接送出表單 → 報價單建立成功，客戶正確
4. 客戶管理列表出現該客戶並帶「暫定」徽章；篩「僅暫定客戶」找得到
5. 客戶詳情頁出現提示條 → 按「轉為正式客戶」→ 徽章消失
6. 合約、發票的新增與編輯頁重複步驟 1–3
7. 報價單轉發票、轉合約皆正常
8. 報價單 PDF 客戶名稱正常顯示

---

## Self-Review 檢查結果

- **規格覆蓋**：設計文件四個區塊（資料層→Task 1、後端 API→Task 2-3、共用 partial→Task 5-6、客戶管理 UI→Task 4）皆有對應任務，「完全不受影響的既有功能」由 Task 7 Step 1 的全測試套件把關。
- **無 placeholder**：每個步驟都有實際程式碼或可直接執行的指令與預期輸出。
- **命名一致**：`is_provisional`、`scopeProvisional`／`scopeFormal`、`quickStore`／`promote`、路由名 `admin.clients.quick-store`／`admin.clients.promote`、元件標記 `data-client-select` 全篇一致。
