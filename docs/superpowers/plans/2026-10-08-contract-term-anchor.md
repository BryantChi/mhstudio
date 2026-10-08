# 合約期間起算點與上線日彈性化 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 拆開「執行中」與「上線」，上線日／實際交件日改為獨立且可修改的里程碑日期，合約期間起算點可選上線日、實際交件日或自訂日期。

**Architecture:** `contracts` 表新增 `term_anchor`、`go_live_date`、`actual_delivery_date`；起算規則集中在 `Contract` model（`anchorDateField()`、`isTermEstimated()`、`recordMilestoneDate()`），controller 只做驗證與狀態守衛。狀態機 `STATUS_TRANSITIONS` 不動。前端只改 Blade 與期間欄位 partial 內的原生 JS。

**Tech Stack:** Laravel 11、PHP 8.2、MySQL 8、Pest、Blade + CoreUI（Bootstrap 5）、原生 JS。

**Spec:** `docs/superpowers/specs/2026-10-08-contract-term-anchor-design.md`

## Global Constraints

- 程式碼註解用繁體中文，說明「為什麼」與非直觀前提，不逐行翻譯；變數／函式／檔名維持英文。
- commit 訊息繁體中文、動詞或類型前綴開頭（`新增:`／`修正:`／`重構:`／`文件:`），標題 ≤ 50 字，不加 `Co-Authored-By` 或任何 AI 署名，不寫「未測試」等驗證狀態旁註；結尾保留一行 `Claude-Session: https://claude.ai/code/session_01QjnvsebvLdrrJdamE228Hk`（與既有 commit 一致）。
- 只 `git add` 本任務的檔案；工作目錄裡的 `package-lock.json` 是使用者自己的修改，**不可加入 commit、不可還原**。
- 測試：Pest + `RefreshDatabase`，資料庫是本機 `mhstudio`（會被清空，屬正常）；專案**沒有 factory**，一律 `Model::create([...])`；建合約前要先 `actingAs`（`makeTermContract()` 已內含）。
- 跑測試：`php artisan test --filter=ContractTermTest`；全套：`php artisan test`。
- Pint：`vendor/bin/*` 無執行權限，用 `php vendor/bin/pint <檔案>`；**只對新建檔案**執行，不可對既有檔案或全專案執行（會重排 74 個既有檔案）。
- Blade 驗證：`php artisan view:clear && php artisan view:cache`，再對編譯產物跑 `php -l`，最後 `php artisan view:clear`；不可只信 `view:cache` 的 success。
- 起算點值固定為 `go_live`／`delivery`／`custom`；里程碑欄位固定為 `go_live_date`／`actual_delivery_date`。
- 緩衝天數沿用既有設定 key `contract_go_live_buffer_days` 與 `Contract::goLiveBufferDays()`，**不改名**（改 key 需要資料遷移，不在範圍內）。
- 只有 `signed`、`active` 狀態可設定／修改里程碑日期；本版不支援清除已記錄的日期。

## Review Focus

1. **剛 `Contract::create()`、尚未 refresh 的 model 沒有 `term_anchor`**：DB 預設值不會回填到記憶體中的 model，`anchorDateField()` 會拿到 null 而誤判成「自訂起算」→ 不標預估。預期：視同 `go_live`。→ Task 1 用 model `$attributes` 預設值，並加測試。
2. **`field` 參數被拿來改其他欄位**（例如送 `field=status`）：`recordMilestoneDate()` 用 `update([$field => ...])`，若沒擋就是 mass assignment（大量指派）漏洞。預期：驗證失敗、資料不變。→ Task 3 測試。
3. **編輯頁改月數時，已確定的起訖日被預估值蓋掉**：預期以已記錄的實際日期重算。→ Task 5 測試 `data-*` 有正確輸出，並手動驗證 JS。
4. **異動紀錄的日期被序列化成 UTC 時間字串**（`2026-12-14T16:00:00Z`）：預期存 `2026-12-15`。→ Task 1 cast 用 `date:Y-m-d`，Task 3 測試斷言 log 內容。
5. **舊表單或其他呼叫端送出 `update()` 時沒帶 `term_anchor`**：預期維持原本的起算點，不被重設為 `go_live`。→ Task 4 測試。

---

### Task 1: 新增起算點與里程碑欄位、改寫「預估」判斷

**Files:**
- Create: `database/migrations/2026_10_08_000001_add_term_anchor_fields_to_contracts_table.php`
- Modify: `app/Models/Contract.php`（`$fillable` 約 L30-74、`$casts` 約 L76-97、`getActivitylogOptions()` 約 L117-123、期間區塊約 L243-287、label accessor 區約 L321-345）
- Test: `tests/Feature/ContractTermTest.php`

**Interfaces:**
- Consumes: 既有 `Contract::termDatesFrom()`、`Contract::estimatedTermDates()`、`Contract::goLiveBufferDays()`
- Produces:
  - `Contract::TERM_ANCHORS: array<string,string>`（`['go_live' => '上線日', 'delivery' => '實際交件日', 'custom' => '自訂日期']`）
  - `Contract::ANCHOR_DATE_FIELDS: array<string,string>`（`['go_live' => 'go_live_date', 'delivery' => 'actual_delivery_date']`）
  - `Contract::MILESTONE_EDITABLE_STATUSES: array<string>`（`['signed', 'active']`）
  - `$contract->anchorDateField(): ?string`
  - `$contract->anchorDate(): ?\Illuminate\Support\Carbon`
  - `$contract->canRecordMilestones(): bool`
  - `$contract->isTermEstimated(): bool`（改寫）
  - `$contract->term_anchor_label: string`（accessor）

- [ ] **Step 1: 寫失敗的測試**

先**刪除**既有測試 `'上線時以實際上線日覆寫預估起訖日，並記入異動紀錄'`：它斷言轉執行中後 `isTermEstimated()` 為 false，但舊流程不會寫 `go_live_date`，新判斷規則下必然失敗；同樣的行為（覆寫起訖日＋異動紀錄可查回）由 Task 3 的新測試接手。

再於 `tests/Feature/ContractTermTest.php` 檔尾加入：

```php
/*
 * 「預估 vs 確定」改看起算點日期有沒有記錄，而不是看狀態：
 * 執行中與上線脫鉤後，執行中但還沒上線的合約起訖日仍是預估值，標錯會讓人誤以為到期日已確定。
 */
it('起算點為上線日時，執行中但尚未記錄上線日仍視為預估', function () {
    $contract = makeTermContract(['status' => 'active', 'term_months' => 12, 'start_date' => '2026-11-08']);

    expect($contract->isTermEstimated())->toBeTrue();

    $contract->update(['go_live_date' => '2026-12-15']);
    expect($contract->isTermEstimated())->toBeFalse();
});

it('起算點為實際交件日時，只看交件日有沒有記錄', function () {
    $contract = makeTermContract(['term_anchor' => 'delivery', 'term_months' => 12, 'go_live_date' => '2026-12-15']);

    // 上線日有記錄也不算數，起算點是交件日
    expect($contract->isTermEstimated())->toBeTrue();

    $contract->update(['actual_delivery_date' => '2026-12-01']);
    expect($contract->isTermEstimated())->toBeFalse();
});

it('自訂起算的合約永遠視為確定', function () {
    $contract = makeTermContract(['status' => 'draft', 'term_anchor' => 'custom', 'term_months' => 12, 'start_date' => '2027-01-01']);

    expect($contract->isTermEstimated())->toBeFalse();
});

it('沒設期間月數的合約沒有預估概念', function () {
    expect(makeTermContract(['start_date' => '2026-11-08'])->isTermEstimated())->toBeFalse();
});

it('剛建立未重新讀取的合約也以上線日為起算點', function () {
    // DB 預設值不會回填到記憶體中的 model；少了 model 端預設值會被誤判成自訂起算而不標預估
    $contract = makeTermContract(['term_months' => 12, 'start_date' => '2026-11-08']);

    expect($contract->term_anchor)->toBe('go_live')
        ->and($contract->isTermEstimated())->toBeTrue();
});
```

- [ ] **Step 2: 跑測試確認失敗**

Run: `php artisan test --filter=ContractTermTest`
Expected: 新增的 5 個測試 FAIL（`Unknown column 'term_anchor'` 或 `go_live_date` 不在 fillable 而未寫入）。

- [ ] **Step 3: 建立 migration**

`database/migrations/2026_10_08_000001_add_term_anchor_fields_to_contracts_table.php`：

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // 期間起算點：go_live（上線日）／delivery（實際交件日）／custom（以 start_date 為起點）。
            // 預設 go_live，與既有合約「自上線日起算」的行為一致。
            $table->string('term_anchor', 20)->default('go_live')->after('term_months');
            // 上線、交件改為獨立的里程碑日期，不再綁「執行中」狀態；只有被選為起算點的那個會觸發起訖日重算
            $table->date('go_live_date')->nullable()->after('term_anchor');
            $table->date('actual_delivery_date')->nullable()->after('go_live_date');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['term_anchor', 'go_live_date', 'actual_delivery_date']);
        });
    }
};
```

- [ ] **Step 4: 修改 `app/Models/Contract.php`**

(a) `$fillable` 在 `'term_months',` 後面加：

```php
        'term_anchor',
        'go_live_date',
        'actual_delivery_date',
```

(b) 在 `protected $fillable` 前面加 model 預設值：

```php
    /**
     * DB 欄位預設值不會回填到剛 create() 的 model，這裡補上，
     * 否則 anchorDateField() 會拿到 null 而被當成自訂起算。
     */
    protected $attributes = [
        'term_anchor' => 'go_live',
    ];
```

(c) `$casts` 在 `'term_months' => 'integer',` 後面加：

```php
        'go_live_date' => 'date:Y-m-d',
        'actual_delivery_date' => 'date:Y-m-d',
```

(d) `getActivitylogOptions()` 的 `logOnly` 陣列尾端加入 `'term_anchor', 'go_live_date', 'actual_delivery_date'`：

```php
            ->logOnly(['title', 'status', 'total', 'paid_amount', 'sent_at', 'signed_at', 'signed_document_path', 'start_date', 'end_date', 'term_anchor', 'go_live_date', 'actual_delivery_date'])
```

(e) 把「合約期間」區塊標題改成 `/* ===== 合約期間（依起算點計算） ===== */`，緩衝天數的 docblock 改為：

```php
    /** 起算日期尚未記錄時，預計交件日到預估起點的緩衝天數（上線日、實際交件日共用；後台「單據條款」可覆寫） */
```

在 `DEFAULT_GO_LIVE_BUFFER_DAYS` 常數之前加入：

```php
    /** 期間起算點；自訂日期不另開欄位，直接以 start_date 為起點 */
    public const TERM_ANCHORS = [
        'go_live' => '上線日',
        'delivery' => '實際交件日',
        'custom' => '自訂日期',
    ];

    /** 起算點對應的里程碑日期欄位（custom 沒有對應欄位） */
    public const ANCHOR_DATE_FIELDS = [
        'go_live' => 'go_live_date',
        'delivery' => 'actual_delivery_date',
    ];

    /** 簽約前沒有上線／交件可言；已完成、已取消則鎖住，避免事後改動已結案的紀錄 */
    public const MILESTONE_EDITABLE_STATUSES = ['signed', 'active'];
```

`estimatedTermDates()` 的 docblock 改為：

```php
    /**
     * 起算日期尚未記錄時的預估起訖日：預計交件日 + 緩衝天數視為預估起點（上線日、實際交件日共用）。
     */
```

(f) 把整個 `isTermEstimated()` 換成以下內容（含新增的三個方法）：

```php
    public function anchorDateField(): ?string
    {
        return self::ANCHOR_DATE_FIELDS[$this->term_anchor] ?? null;
    }

    /** 起算點的實際日期；尚未記錄或為自訂起算時回傳 null */
    public function anchorDate(): ?Carbon
    {
        $field = $this->anchorDateField();

        return $field ? $this->{$field} : null;
    }

    public function canRecordMilestones(): bool
    {
        return in_array($this->status, self::MILESTONE_EDITABLE_STATUSES, true);
    }

    /**
     * 目前的起訖日是否仍為預估值。PDF／詳情頁據此標註「預估」。
     * 看起算點日期有沒有記錄，而非看狀態：執行中與上線已脫鉤，執行中仍可能尚未上線。
     */
    public function isTermEstimated(): bool
    {
        if ($this->term_months === null || $this->term_anchor === 'custom') {
            return false;
        }

        return $this->anchorDate() === null;
    }
```

(g) 在 `getTypeLabelAttribute()` 之後加入：

```php
    public function getTermAnchorLabelAttribute(): string
    {
        return self::TERM_ANCHORS[$this->term_anchor] ?? '未知';
    }
```

- [ ] **Step 5: 跑 migration 與測試**

Run: `php artisan test --filter=ContractTermTest`
Expected: 全部 PASS（新增 5 個＋其餘既有測試）。若有既有測試失敗，停下來查原因，不要改測試硬過。

- [ ] **Step 6: 格式化新檔**

Run: `php vendor/bin/pint database/migrations/2026_10_08_000001_add_term_anchor_fields_to_contracts_table.php`

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_10_08_000001_add_term_anchor_fields_to_contracts_table.php app/Models/Contract.php tests/Feature/ContractTermTest.php
git commit -F - <<'EOF'
新增: 合約期間起算點與上線日、交件日欄位

預估判斷改看起算點日期是否已記錄，不再看狀態，為拆開執行中與上線做準備。

Claude-Session: https://claude.ai/code/session_01QjnvsebvLdrrJdamE228Hk
EOF
```

---

### Task 2: 回填既有已上線合約的上線日

**Files:**
- Create: `database/migrations/2026_10_08_000002_backfill_go_live_date_on_contracts_table.php`
- Test: `tests/Feature/ContractTermTest.php`

**Interfaces:**
- Consumes: Task 1 的 `go_live_date` 欄位
- Produces: 無（資料遷移）

> 回填獨立成第二支 migration 的理由：`RefreshDatabase` 跑完後欄位已存在，測試無法重跑「加欄位」的 migration；拆開後測試可以單獨 `require` 回填這支再呼叫 `up()`，真正驗證回填條件。

- [ ] **Step 1: 寫失敗的測試**

在 `tests/Feature/ContractTermTest.php` 檔尾加入：

```php
/*
 * 回填條件是推論出來的：term_months 是 9/29 才加的欄位，有月數又已執行中的合約必定走過舊版
 * 「轉執行中＋填上線日」流程，start_date 就是上線日；沒有月數的舊合約 start_date 是手填，猜錯比空著更糟。
 */
it('回填只補有期間月數且已上線的合約，舊合約與未上線的不猜', function () {
    $live = makeTermContract(['status' => 'active', 'term_months' => 12, 'start_date' => '2026-10-20', 'end_date' => '2027-10-19']);
    $done = makeTermContract(['status' => 'completed', 'term_months' => 6, 'start_date' => '2026-01-05']);
    $legacy = makeTermContract(['status' => 'active', 'start_date' => '2025-01-01']);
    $notLive = makeTermContract(['status' => 'signed', 'term_months' => 12, 'start_date' => '2026-11-08']);

    (require database_path('migrations/2026_10_08_000002_backfill_go_live_date_on_contracts_table.php'))->up();

    expect($live->fresh()->go_live_date->toDateString())->toBe('2026-10-20')
        ->and($done->fresh()->go_live_date->toDateString())->toBe('2026-01-05')
        ->and($legacy->fresh()->go_live_date)->toBeNull()
        ->and($notLive->fresh()->go_live_date)->toBeNull();
});

it('回填不會覆蓋已記錄的上線日，也不產生異動紀錄', function () {
    $contract = makeTermContract(['status' => 'active', 'term_months' => 12, 'start_date' => '2026-10-20', 'go_live_date' => '2026-10-18']);
    $logCount = $contract->activities()->count();

    (require database_path('migrations/2026_10_08_000002_backfill_go_live_date_on_contracts_table.php'))->up();

    expect($contract->fresh()->go_live_date->toDateString())->toBe('2026-10-18')
        ->and($contract->activities()->count())->toBe($logCount);
});
```

- [ ] **Step 2: 跑測試確認失敗**

Run: `php artisan test --filter=ContractTermTest`
Expected: 兩個新測試 FAIL（`Failed opening required ... backfill_go_live_date...`）。

- [ ] **Step 3: 建立回填 migration**

`database/migrations/2026_10_08_000002_backfill_go_live_date_on_contracts_table.php`：

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 有期間月數又已執行中／已完成的合約，必定走過舊版「轉執行中＋填上線日」流程，start_date 即上線日。
        // 沒有月數的是功能上線前的合約，start_date 為手填、不保證等於上線日，寧可留空。
        // 用 query builder 而非 Eloquent：不觸發 activity log，避免灌出一批假的異動紀錄。
        DB::table('contracts')
            ->whereIn('status', ['active', 'completed'])
            ->whereNotNull('term_months')
            ->whereNotNull('start_date')
            ->whereNull('go_live_date')
            ->update(['go_live_date' => DB::raw('start_date')]);
    }

    public function down(): void
    {
        // 回填的值無法與之後手動記錄的區分，交由前一支 migration 的 down() 連欄位一併移除
    }
};
```

- [ ] **Step 4: 跑測試確認通過**

Run: `php artisan test --filter=ContractTermTest`
Expected: 兩個回填測試 PASS。

- [ ] **Step 5: 格式化新檔**

Run: `php vendor/bin/pint database/migrations/2026_10_08_000002_backfill_go_live_date_on_contracts_table.php`

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_10_08_000002_backfill_go_live_date_on_contracts_table.php tests/Feature/ContractTermTest.php
git commit -F - <<'EOF'
新增: 回填既有已上線合約的上線日

有期間月數又已執行中的合約必定走過舊版上線流程，start_date 即上線日；
沒有月數的舊合約開始日為手填，不回填。

Claude-Session: https://claude.ai/code/session_01QjnvsebvLdrrJdamE228Hk
EOF
```

---

### Task 3: 記錄里程碑日期的 action，轉執行中不再綁上線日

**Files:**
- Modify: `app/Models/Contract.php`（`isTermEstimated()` 之後）
- Modify: `app/Http/Controllers/Admin/ContractController.php`（`store()` 註解約 L105、`updateStatus()` 約 L361-395，並在其後新增 `updateMilestoneDate()`）
- Modify: `routes/admin.php`（約 L206，`contracts.update-status` 之後）
- Test: `tests/Feature/ContractTermTest.php`

**Interfaces:**
- Consumes: Task 1 的 `ANCHOR_DATE_FIELDS`、`anchorDateField()`、`canRecordMilestones()`、`termDatesFrom()`
- Produces:
  - `$contract->recordMilestoneDate(string $field, string $date): void`（`$field` 不在 `ANCHOR_DATE_FIELDS` 時丟 `InvalidArgumentException`）
  - 路由 `PUT contracts/{contract}/milestone-date`，名稱 `admin.contracts.update-milestone-date`，參數 `field`、`date`

- [ ] **Step 1: 改寫既有測試、寫新的失敗測試**

在 `tests/Feature/ContractTermTest.php` 中：

**刪除**這兩個既有測試（行為已被取代）：`'轉為執行中必須填上線日'`、`'未設期間月數的舊合約上線時只改開始日，保留手填的結束日'`。（`'上線時以實際上線日覆寫…'` 已在 Task 1 刪除。）

把檔頭說明註解改為：

```php
/*
 * 合約期間依「起算點」計算（上線日／實際交件日／自訂日期）：
 * 起算日期未記錄前存預估起訖日；在詳情頁記錄起算點日期時以實際日期覆寫。
 * 起訖日錯了會讓「即將到期」提醒與剩餘天數失準，所以公式與覆寫行為都要鎖住。
 */
```

檔尾加入：

```php
/*
 * 執行中只代表開始執行，上線是另外記錄的日期：開發期間就能轉執行中，上線日填錯或延後也能修正。
 */
it('轉為執行中不需要上線日，也不會動到預估起訖日', function () {
    $contract = makeTermContract(['term_months' => 12, 'start_date' => '2026-11-08', 'end_date' => '2027-11-07']);

    $this->put(route('admin.contracts.update-status', $contract), ['status' => 'active'])
        ->assertSessionHasNoErrors();

    $contract->refresh();
    expect($contract->status)->toBe('active')
        ->and($contract->start_date->toDateString())->toBe('2026-11-08')
        ->and($contract->end_date->toDateString())->toBe('2027-11-07')
        ->and($contract->isTermEstimated())->toBeTrue();
});

it('記錄的上線日是起算點時，以它重算起訖日並記入異動紀錄', function () {
    $contract = makeTermContract(['status' => 'active', 'term_months' => 12, 'start_date' => '2026-11-08', 'end_date' => '2027-11-07']);

    $this->put(route('admin.contracts.update-milestone-date', $contract), [
        'field' => 'go_live_date',
        'date' => '2026-12-15',
    ])->assertSessionHasNoErrors();

    $contract->refresh();
    expect($contract->go_live_date->toDateString())->toBe('2026-12-15')
        ->and($contract->start_date->toDateString())->toBe('2026-12-15')
        ->and($contract->end_date->toDateString())->toBe('2027-12-14')
        ->and($contract->isTermEstimated())->toBeFalse();

    // 選擇「直接覆寫」的前提：原本的預估值要能從異動紀錄查回，且日期不能被序列化成 UTC 時間字串
    $log = $contract->activities()->latest('id')->first();
    expect($log->properties['old']['start_date'])->toBe('2026-11-08')
        ->and($log->properties['attributes']['go_live_date'])->toBe('2026-12-15');
});

it('上線後修改上線日，起訖日跟著重算', function () {
    $contract = makeTermContract([
        'status' => 'active', 'term_months' => 12,
        'go_live_date' => '2026-12-15', 'start_date' => '2026-12-15', 'end_date' => '2027-12-14',
    ]);

    $this->put(route('admin.contracts.update-milestone-date', $contract), [
        'field' => 'go_live_date',
        'date' => '2027-01-10',
    ])->assertSessionHasNoErrors();

    $contract->refresh();
    expect($contract->start_date->toDateString())->toBe('2027-01-10')
        ->and($contract->end_date->toDateString())->toBe('2028-01-09');
});

it('記錄的日期不是起算點時只存日期，起訖日完全不動', function () {
    $contract = makeTermContract(['term_anchor' => 'delivery', 'term_months' => 12, 'start_date' => '2026-11-08', 'end_date' => '2027-11-07']);

    $this->put(route('admin.contracts.update-milestone-date', $contract), [
        'field' => 'go_live_date',
        'date' => '2026-12-15',
    ])->assertSessionHasNoErrors();

    $contract->refresh();
    expect($contract->go_live_date->toDateString())->toBe('2026-12-15')
        ->and($contract->start_date->toDateString())->toBe('2026-11-08')
        ->and($contract->end_date->toDateString())->toBe('2027-11-07')
        ->and($contract->isTermEstimated())->toBeTrue();

    // 起算點是交件日，記錄交件日才重算
    $this->put(route('admin.contracts.update-milestone-date', $contract), [
        'field' => 'actual_delivery_date',
        'date' => '2026-12-01',
    ]);

    $contract->refresh();
    expect($contract->start_date->toDateString())->toBe('2026-12-01')
        ->and($contract->end_date->toDateString())->toBe('2027-11-30');
});

it('未設期間月數的舊合約記錄上線日時只改開始日，保留手填的結束日', function () {
    $contract = makeTermContract(['start_date' => '2026-10-01', 'end_date' => '2027-09-30']);

    $this->put(route('admin.contracts.update-milestone-date', $contract), [
        'field' => 'go_live_date',
        'date' => '2026-10-20',
    ]);

    $contract->refresh();
    expect($contract->start_date->toDateString())->toBe('2026-10-20')
        ->and($contract->end_date->toDateString())->toBe('2027-09-30');
});

it('只有已簽署或執行中的合約能記錄上線日', function (string $status) {
    $contract = makeTermContract(['status' => $status, 'term_months' => 12, 'start_date' => '2026-11-08']);

    $this->put(route('admin.contracts.update-milestone-date', $contract), [
        'field' => 'go_live_date',
        'date' => '2026-12-15',
    ]);

    $contract->refresh();
    expect($contract->go_live_date)->toBeNull()
        ->and($contract->start_date->toDateString())->toBe('2026-11-08');
})->with(['draft', 'sent', 'completed', 'cancelled']);

it('只接受上線日與交件日兩個欄位，不能藉此改其他欄位', function () {
    $contract = makeTermContract();

    $this->put(route('admin.contracts.update-milestone-date', $contract), [
        'field' => 'status',
        'date' => '2026-12-15',
    ])->assertSessionHasErrors('field');

    expect($contract->fresh()->status)->toBe('signed');
});

it('model 層也拒絕非里程碑欄位，防止其他呼叫端繞過驗證', function () {
    $contract = makeTermContract();

    expect(fn () => $contract->recordMilestoneDate('status', '2026-12-15'))
        ->toThrow(InvalidArgumentException::class);
});
```

- [ ] **Step 2: 跑測試確認失敗**

Run: `php artisan test --filter=ContractTermTest`
Expected: 新測試 FAIL（`Route [admin.contracts.update-milestone-date] not defined`、轉執行中被要求 `go_live_date`）。

- [ ] **Step 3: 在 model 加入 `recordMilestoneDate()`**

`app/Models/Contract.php`，緊接在 `isTermEstimated()` 之後：

```php
    /**
     * 記錄上線日或實際交件日。只有記錄的正好是起算點時才重算起訖日；
     * 沒設期間月數的舊合約只改開始日，保留原本手填的結束日。
     */
    public function recordMilestoneDate(string $field, string $date): void
    {
        // 擋在 model 層：$field 直接當作 update() 的 key，放行任意欄位等於開了大量指派的口
        if (! in_array($field, self::ANCHOR_DATE_FIELDS, true)) {
            throw new \InvalidArgumentException("不是里程碑日期欄位：{$field}");
        }

        $data = [$field => $date];

        if ($field === $this->anchorDateField()) {
            $termDates = self::termDatesFrom($date, $this->term_months);
            $data['start_date'] = $termDates['start_date'];
            if ($termDates['end_date']) {
                $data['end_date'] = $termDates['end_date'];
            }
        }

        $this->update($data);
    }
```

- [ ] **Step 4: 修改 controller**

`app/Http/Controllers/Admin/ContractController.php`：

(a) `store()` 驗證陣列中 `status` 上方的註解改為：

```php
            // 新合約只能是草稿或已送出；簽署需上傳回簽檔，在詳情頁進行
```

(b) `updateStatus()` 的驗證改為（移除 `go_live_date`）：

```php
        // 簽署（signed）僅能透過上傳客戶回簽檔達成，不開放此處直接設定
        $request->validate([
            'status' => 'required|in:draft,sent,active,completed,cancelled',
        ]);
```

並刪除整段 `if ($request->status === 'active') { ... }`（含 `termDatesFrom` 重算與「未設期間月數的舊合約…」註解）。

(c) 在 `updateStatus()` 之後新增：

```php
    /**
     * 記錄上線日／實際交件日。與「執行中」狀態脫鉤：開發期間可先轉執行中，上線日填錯或延後也能修正。
     */
    public function updateMilestoneDate(Request $request, Contract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'field' => 'required|in:'.implode(',', Contract::ANCHOR_DATE_FIELDS),
            'date' => 'required|date',
        ]);

        if (! $contract->canRecordMilestones()) {
            flash_error("「{$contract->status_label}」的合約無法設定上線日或交件日");

            return redirect()->route('admin.contracts.show', $contract);
        }

        $contract->recordMilestoneDate($validated['field'], $validated['date']);
        flash_success('日期已更新');

        return redirect()->route('admin.contracts.show', $contract);
    }
```

- [ ] **Step 5: 加路由**

`routes/admin.php`，在 `contracts.update-status` 那行之後（仍在 `Route::resource('contracts', ...)` 之前）加：

```php
        Route::put('contracts/{contract}/milestone-date', [ContractController::class, 'updateMilestoneDate'])->name('contracts.update-milestone-date');
```

- [ ] **Step 6: 跑測試確認通過**

Run: `php artisan test --filter=ContractTermTest`
Expected: 全部 PASS（含 Task 1、2 的測試）。

- [ ] **Step 7: Commit**

```bash
git add app/Models/Contract.php app/Http/Controllers/Admin/ContractController.php routes/admin.php tests/Feature/ContractTermTest.php
git commit -F - <<'EOF'
新增: 上線日與交件日改為可隨時記錄的里程碑

轉執行中不再強制填上線日；已簽署、執行中的合約可記錄或修正兩個日期，
只有記錄的是起算點時才重算起訖日。

Claude-Session: https://claude.ai/code/session_01QjnvsebvLdrrJdamE228Hk
EOF
```

---

### Task 4: 建立／編輯合約可選起算點，後端備援與複製合約跟著調整

**Files:**
- Modify: `app/Http/Controllers/Admin/ContractController.php`（`store()`、`update()` 驗證與寫入陣列；`fillEstimatedTermDates()` 約 L349-358；`duplicate()` 約 L630-650）
- Test: `tests/Feature/ContractTermTest.php`

**Interfaces:**
- Consumes: Task 1 的 `TERM_ANCHORS`、`ANCHOR_DATE_FIELDS`、`termDatesFrom()`、`estimatedTermDates()`
- Produces: `store()`／`update()` 接受 `term_anchor`（`nullable|in:go_live,delivery,custom`）；`fillEstimatedTermDates(array $validated, ?Contract $contract = null): array`

- [ ] **Step 1: 寫失敗的測試**

在 `tests/Feature/ContractTermTest.php` 檔尾加入：

```php
it('建立自訂起算的合約時，後端不會用預計交件日代補預估值', function () {
    makeTermContract();
    $client = Client::create(['name' => '大東實業']);

    $this->post(route('admin.contracts.store'), contractFormPayload($client, [
        'term_anchor' => 'custom',
        'expected_delivery_date' => '2026-11-01',
        'term_months' => 12,
    ]))->assertSessionHasNoErrors();

    $contract = Contract::where('title', '官網建置')->firstOrFail();
    expect($contract->term_anchor)->toBe('custom')
        ->and($contract->start_date)->toBeNull();
});

it('起算點只接受三種值', function () {
    makeTermContract();
    $client = Client::create(['name' => '大東實業']);

    $this->post(route('admin.contracts.store'), contractFormPayload($client, ['term_anchor' => 'signed_at']))
        ->assertSessionHasErrors('term_anchor');
});

it('編輯時清空起訖日，後端備援以已記錄的上線日重算而非預估值', function () {
    $contract = makeTermContract([
        'status' => 'active', 'term_months' => 12, 'expected_delivery_date' => '2026-11-01',
        'go_live_date' => '2026-12-15', 'start_date' => '2026-12-15', 'end_date' => '2027-12-14',
    ]);

    $this->put(route('admin.contracts.update', $contract), contractFormPayload($contract->client, [
        'term_anchor' => 'go_live',
        'expected_delivery_date' => '2026-11-01',
        'term_months' => 12,
    ]))->assertSessionHasNoErrors();

    $contract->refresh();
    expect($contract->start_date->toDateString())->toBe('2026-12-15')
        ->and($contract->end_date->toDateString())->toBe('2027-12-14');
});

it('編輯時沒送起算點，維持原本的起算點', function () {
    $contract = makeTermContract(['term_anchor' => 'delivery', 'term_months' => 12, 'start_date' => '2026-11-08', 'end_date' => '2027-11-07']);

    $this->put(route('admin.contracts.update', $contract), contractFormPayload($contract->client, [
        'start_date' => '2026-11-08',
        'end_date' => '2027-11-07',
    ]))->assertSessionHasNoErrors();

    expect($contract->fresh()->term_anchor)->toBe('delivery');
});

it('複製合約時清空里程碑日期但沿用起算點', function () {
    $contract = makeTermContract([
        'status' => 'active', 'term_anchor' => 'delivery', 'term_months' => 12,
        'go_live_date' => '2026-12-15', 'actual_delivery_date' => '2026-12-01',
    ]);

    $this->post(route('admin.contracts.duplicate', $contract));

    $copy = Contract::where('title', '測試合約 (複本)')->firstOrFail();
    expect($copy->go_live_date)->toBeNull()
        ->and($copy->actual_delivery_date)->toBeNull()
        ->and($copy->term_anchor)->toBe('delivery');
});
```

- [ ] **Step 2: 跑測試確認失敗**

Run: `php artisan test --filter=ContractTermTest`
Expected: 新測試 FAIL（`term_anchor` 未寫入、複本仍帶著里程碑日期、編輯備援用預估值 2026-11-08）。

- [ ] **Step 3: 修改 `store()` 與 `update()`**

兩個方法的驗證陣列，在 `'term_months' => ...` 之後各加一行：

```php
            'term_anchor' => 'nullable|in:'.implode(',', array_keys(Contract::TERM_ANCHORS)),
```

`store()` 的 `$validated = $this->fillEstimatedTermDates($validated);` 不變；`Contract::create([...])` 在 `'term_months' => ...` 之後加：

```php
            'term_anchor' => $validated['term_anchor'] ?? 'go_live',
```

`update()` 改為傳入 `$contract`：

```php
        $validated = $this->fillEstimatedTermDates($validated, $contract);
```

`$contract->update([...])` 在 `'term_months' => ...` 之後加：

```php
            // 沒送起算點（舊表單或其他呼叫端）時維持原值，不要被重設成上線日
            'term_anchor' => $validated['term_anchor'] ?? $contract->term_anchor,
```

- [ ] **Step 4: 改寫 `fillEstimatedTermDates()`**

整個方法換成：

```php
    /**
     * 沒填開始日期時由後端補上起訖日（前端 JS 失效時的備援）。
     * 起算點日期已記錄就用實際日期；未記錄才用預計交件日推估；自訂起算以使用者填的開始日為準，不代補。
     */
    private function fillEstimatedTermDates(array $validated, ?Contract $contract = null): array
    {
        if (! empty($validated['start_date'])) {
            return $validated;
        }

        $anchor = $validated['term_anchor'] ?? $contract?->term_anchor ?? 'go_live';
        if ($anchor === 'custom') {
            return $validated;
        }

        $termMonths = $validated['term_months'] ?? null;
        $anchorDate = $contract?->{Contract::ANCHOR_DATE_FIELDS[$anchor]};

        if ($anchorDate) {
            $termDates = Contract::termDatesFrom($anchorDate, $termMonths);
        } elseif (! empty($validated['expected_delivery_date'])) {
            $termDates = Contract::estimatedTermDates($validated['expected_delivery_date'], $termMonths);
        } else {
            return $validated;
        }

        $validated['start_date'] = $termDates['start_date'];
        $validated['end_date'] = $validated['end_date'] ?? $termDates['end_date'];

        return $validated;
    }
```

- [ ] **Step 5: 修改 `duplicate()`**

在 `$newContract->expected_delivery_date = null;` 之後加：

```php
        $newContract->go_live_date = null; // 里程碑日期是原合約的執行紀錄，複本重新記錄；起算點則沿用
        $newContract->actual_delivery_date = null;
```

- [ ] **Step 6: 跑測試確認通過**

Run: `php artisan test --filter=ContractTermTest`
Expected: 全部 PASS。

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Admin/ContractController.php tests/Feature/ContractTermTest.php
git commit -F - <<'EOF'
新增: 建立與編輯合約可選擇期間起算點

後端補起訖日的備援改依起算點：已記錄實際日期就用實際日期，自訂起算不代補；
複製合約時清空里程碑日期。

Claude-Session: https://claude.ai/code/session_01QjnvsebvLdrrJdamE228Hk
EOF
```

---

### Task 5: 期間欄位表單加入起算點下拉，JS 依起算點帶日期

**Files:**
- Modify: `resources/views/admin/contracts/partials/term-fields.blade.php`（整檔）
- Modify: `resources/views/admin/settings/documents.blade.php`（約 L58、L63）
- Test: `tests/Feature/ContractTermTest.php`

**Interfaces:**
- Consumes: Task 1 的 `Contract::TERM_ANCHORS`、`go_live_date`、`actual_delivery_date`；Task 4 的 `term_anchor` 表單欄位
- Produces: 表單欄位 `term_anchor`（`<select id="term_anchor">`），帶 `data-go-live-date`、`data-delivery-date`

- [ ] **Step 1: 寫失敗的測試**

在 `tests/Feature/ContractTermTest.php` 檔尾加入：

```php
it('編輯頁把已記錄的實際日期交給前端，改月數時才不會被預估值蓋掉', function () {
    $contract = makeTermContract([
        'status' => 'active', 'term_anchor' => 'delivery', 'term_months' => 12,
        'go_live_date' => '2026-12-15', 'actual_delivery_date' => '2026-12-01',
    ]);

    $this->withoutVite(); // 測試不依賴 public/build 的打包產物
    $this->get(route('admin.contracts.edit', $contract))
        ->assertOk()
        ->assertSee('name="term_anchor"', false)
        ->assertSee('data-go-live-date="2026-12-15"', false)
        ->assertSee('data-delivery-date="2026-12-01"', false)
        ->assertSee('<option value="delivery" selected>', false);
});
```

- [ ] **Step 2: 跑測試確認失敗**

Run: `php artisan test --filter=ContractTermTest`
Expected: FAIL（頁面沒有 `name="term_anchor"`）。若 FAIL 原因是 `assertOk` 拿到 302／403，先停下來查 edit 頁的權限或中介層，不要改測試硬過。

- [ ] **Step 3: 改寫 `partials/term-fields.blade.php`**

整檔換成：

```blade
{{--
    合約期間欄位（建立／編輯共用）。
    期間起算點三選一：上線日（預設）／實際交件日／自訂日期。
    上線日、實際交件日在詳情頁記錄；尚未記錄時以「預計交件日 + 緩衝天數」帶出預估起訖日。
    傳入：$contract（編輯時；建立時為 null）
--}}
@php
    $bufferDays = \App\Models\Contract::goLiveBufferDays();
    $termAnchor = old('term_anchor', $contract?->term_anchor ?? 'go_live');
@endphp
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="expected_delivery_date" class="form-label">預計交件日</label>
        <input type="date" class="form-control @error('expected_delivery_date') is-invalid @enderror"
               id="expected_delivery_date" name="expected_delivery_date"
               value="{{ old('expected_delivery_date', $contract?->expected_delivery_date?->format('Y-m-d')) }}">
        @error('expected_delivery_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="term_months" class="form-label">合約期間（月）</label>
        <input type="number" class="form-control @error('term_months') is-invalid @enderror"
               id="term_months" name="term_months" min="1" max="600" placeholder="例：12"
               value="{{ old('term_months', $contract?->term_months) }}">
        @error('term_months') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="term_anchor" class="form-label">期間起算點</label>
        {{-- 已記錄的實際日期交給 JS：有值時以實際日期起算，避免改月數時被預估值蓋掉已確定的起訖日 --}}
        <select class="form-select @error('term_anchor') is-invalid @enderror" id="term_anchor" name="term_anchor"
                data-go-live-date="{{ $contract?->go_live_date?->format('Y-m-d') }}"
                data-delivery-date="{{ $contract?->actual_delivery_date?->format('Y-m-d') }}">
            @foreach(\App\Models\Contract::TERM_ANCHORS as $value => $label)
                <option value="{{ $value }}" @selected($termAnchor === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('term_anchor') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="start_date" class="form-label">開始日期</label>
        <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date"
               value="{{ old('start_date', $contract?->start_date?->format('Y-m-d')) }}">
        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="end_date" class="form-label">結束日期</label>
        <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date"
               value="{{ old('end_date', $contract?->end_date?->format('Y-m-d')) }}">
        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<small class="text-muted d-block mb-3" style="margin-top: -0.5rem;">
    起算點為上線日或實際交件日時：該日期尚未記錄前，以預計交件日 + {{ $bufferDays }} 天帶出預估起訖日；實際日期在詳情頁記錄後會自動重算。
    起算點為自訂日期時：填入開始日期即帶出結束日。起訖日皆可手動調整。
</small>

@push('scripts')
<script>
(function () {
    const bufferDays = {{ $bufferDays }};
    const delivery = document.getElementById('expected_delivery_date');
    const term = document.getElementById('term_months');
    const anchor = document.getElementById('term_anchor');
    const start = document.getElementById('start_date');
    const end = document.getElementById('end_date');

    // 編輯頁才有值；建立頁為空字串
    const recorded = { go_live: anchor.dataset.goLiveDate, delivery: anchor.dataset.deliveryDate };

    // 以 UTC 計算，避免時區造成 toISOString() 跨日
    const parse = (v) => { const [y, m, d] = v.split('-').map(Number); return new Date(Date.UTC(y, m - 1, d)); };
    const fmt = (dt) => dt.toISOString().slice(0, 10);

    // 與後端 Contract::termDatesFrom() 同一公式：結束日 = 開始 + N 個月 − 1 天，月底不溢位
    function addMonthsNoOverflow(dt, months) {
        const y = dt.getUTCFullYear(), m = dt.getUTCMonth() + months, d = dt.getUTCDate();
        const lastDay = new Date(Date.UTC(y, m + 1, 0)).getUTCDate();
        return new Date(Date.UTC(y, m, Math.min(d, lastDay)));
    }

    function fillEnd(from) {
        const months = parseInt(term.value, 10);
        if (months > 0) {
            const e = addMonthsNoOverflow(from, months);
            e.setUTCDate(e.getUTCDate() - 1);
            end.value = fmt(e);
        }
    }

    function recalc() {
        if (anchor.value === 'custom') {
            if (start.value) fillEnd(parse(start.value));
            return;
        }

        let from;
        if (recorded[anchor.value]) {
            from = parse(recorded[anchor.value]);
        } else if (delivery.value) {
            from = parse(delivery.value);
            from.setUTCDate(from.getUTCDate() + bufferDays);
        } else {
            return;
        }
        start.value = fmt(from);
        fillEnd(from);
    }

    // 只在使用者改動欄位時才重算，不在載入時覆蓋既有（可能手動調整過）的日期
    delivery.addEventListener('change', recalc);
    term.addEventListener('change', recalc);
    anchor.addEventListener('change', recalc);
    // 自訂起算時開始日期就是起點，要連動結束日；其他起算點下手動改開始日視為微調，不連動
    start.addEventListener('change', () => { if (anchor.value === 'custom') recalc(); });
})();
</script>
@endpush
```

- [ ] **Step 4: 修改設定頁說明文字**

`resources/views/admin/settings/documents.blade.php`：

L58 label 文字 `上線緩衝天數` 改為 `預估起算緩衝天數`。

L63 的 `<small>` 改為：

```blade
                        <small class="text-muted">合約起算點為上線日或實際交件日、且該日期尚未記錄時，以預計交件日＋此天數作為預估起點，帶出預估起訖日。實際日期在合約詳情頁記錄後會以實際日期重算。</small>
```

（設定 key `contract_go_live_buffer_days` 不改。）

- [ ] **Step 5: 跑測試確認通過**

Run: `php artisan test --filter=ContractTermTest`
Expected: 全部 PASS。

- [ ] **Step 6: Blade 語法驗證**

Run:
```bash
php artisan view:clear && php artisan view:cache
for f in $(grep -rl "term_anchor\|預估起算緩衝天數" storage/framework/views/*.php); do php -l "$f"; done
php artisan view:clear
```
Expected: 每個檔案都是 `No syntax errors detected`。

- [ ] **Step 7: 手動驗證 JS（瀏覽器）**

`php artisan serve` + `npm run dev`，以 `admin@example.com`／`password` 登入，逐項確認：

1. 建立頁：起算點「上線日」，填預計交件日 2026-11-01、月數 12 → 開始 2026-11-08、結束 2027-11-07。
2. 同頁改起算點為「自訂日期」→ 開始日期不變；把開始日期改成 2027-01-01 → 結束日期變 2027-12-31。
3. 切回「上線日」→ 開始日期回到 2026-11-08。
4. 對一份已記錄上線日 2026-12-15 的合約開編輯頁，把月數改成 6 → 開始 2026-12-15、結束 2027-06-14（**不能**變成預估的 2026-11-08）。

任何一項不符，停下來修 JS 再重驗。

- [ ] **Step 8: Commit**

```bash
git add resources/views/admin/contracts/partials/term-fields.blade.php resources/views/admin/settings/documents.blade.php tests/Feature/ContractTermTest.php
git commit -F - <<'EOF'
新增: 合約表單可選期間起算點並依此帶出起訖日

已記錄的實際日期交給前端，避免編輯時改月數被預估值蓋掉已確定的起訖日。

Claude-Session: https://claude.ai/code/session_01QjnvsebvLdrrJdamE228Hk
EOF
```

---

### Task 6: 詳情頁里程碑按鈕與資訊、PDF 文字

**Files:**
- Modify: `resources/views/admin/contracts/show.blade.php`（L69 `$statusLabels` 的 `@php`；L76-89 狀態按鈕迴圈；L139 回簽檔區塊之後；L247-251 資訊表格；L513-550 `goLiveModal`）
- Modify: `resources/views/admin/contracts/pdf.blade.php`（約 L147-149）
- Test: `tests/Feature/ContractTermTest.php`

**Interfaces:**
- Consumes: Task 1 的 `canRecordMilestones()`、`anchorDateField()`、`term_anchor_label`、`isTermEstimated()`；Task 3 的路由 `admin.contracts.update-milestone-date`
- Produces: 無

- [ ] **Step 1: 寫失敗的測試**

在 `tests/Feature/ContractTermTest.php` 檔尾加入：

```php
it('詳情頁的里程碑 modal 說清楚哪個日期會重算期間', function () {
    $contract = makeTermContract(['term_anchor' => 'delivery', 'term_months' => 12, 'start_date' => '2026-11-08', 'end_date' => '2027-11-07']);

    $this->withoutVite();
    $this->get(route('admin.contracts.show', $contract))
        ->assertOk()
        ->assertSee('設定上線日')
        ->assertSee('設定實際交件日')
        // 上線日不是起算點：只記錄
        ->assertSee('僅記錄日期，不影響合約期間（目前起算點：實際交件日）')
        // 交件日是起算點：會覆寫
        ->assertSee('將以此日期起算 12 個月，覆寫目前的起訖日')
        ->assertSee('自實際交件日起 12 個月')
        // 舊的「轉執行中強制填上線日」modal 已移除
        ->assertDontSee('goLiveModal');
});

it('已完成的合約詳情頁不提供設定上線日', function () {
    $contract = makeTermContract(['status' => 'completed']);

    $this->withoutVite();
    $this->get(route('admin.contracts.show', $contract))
        ->assertOk()
        ->assertDontSee('設定上線日')
        ->assertDontSee('修改上線日');
});

it('PDF 的預估說明依起算點顯示', function () {
    $contract = makeTermContract(['term_anchor' => 'delivery', 'term_months' => 12, 'start_date' => '2026-11-08', 'end_date' => '2027-11-07']);
    $contract->load(['client', 'project', 'creator', 'items']);

    $html = view('admin.contracts.pdf', compact('contract'))->render();

    expect($html)->toContain('預估；將以實際交件日起算 12 個月');
});
```

- [ ] **Step 2: 跑測試確認失敗**

Run: `php artisan test --filter=ContractTermTest`
Expected: 三個新測試 FAIL（沒有「設定上線日」、仍有 `goLiveModal`、PDF 仍是「實際以上線日起算」）。

- [ ] **Step 3: 狀態按鈕回歸一般確認**

`show.blade.php` L69 的 `@php` 改為（多定義里程碑清單，後面按鈕與 modal 共用）：

```blade
        @php
            $statusLabels = ['draft' => '草稿', 'sent' => '已送出', 'signed' => '已簽署', 'active' => '執行中', 'completed' => '已完成', 'cancelled' => '已取消'];
            $milestones = ['go_live_date' => '上線日', 'actual_delivery_date' => '實際交件日'];
        @endphp
```

在狀態按鈕 `@foreach($contract->allowedNextStatuses() as $val)` 迴圈內，**刪除**整段：

```blade
                        @if($val === 'active')
                            {{-- 轉為執行中＝上線：需先填實際上線日，合約期間以此重算 --}}
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-coreui-toggle="modal" data-coreui-target="#goLiveModal">{{ $statusLabels[$val] }}</button>
                            @continue
                        @endif
```

- [ ] **Step 4: 加入里程碑按鈕**

在「已簽署檔案」區塊的 `@endif`（約 L139，`</div>` card-body 結尾之前）之後插入：

```blade
                {{-- 里程碑日期：上線、交件與「執行中」狀態脫鉤，已簽署／執行中可隨時設定或修正 --}}
                @if($contract->canRecordMilestones())
                <hr>
                <div class="d-flex gap-2 flex-wrap">
                    @foreach($milestones as $field => $label)
                    <button type="button" class="btn btn-sm btn-outline-primary"
                            data-coreui-toggle="modal" data-coreui-target="#milestoneModal-{{ $field }}">
                        {{ $contract->{$field} ? '修改' : '設定' }}{{ $label }}
                    </button>
                    @endforeach
                </div>
                @endif
```

- [ ] **Step 5: 資訊表格**

把：

```blade
                        @if($contract->term_months)
                        <tr><th>合約期間</th><td>自上線日起 {{ $contract->term_months }} 個月</td></tr>
                        @endif
```

換成：

```blade
                        @if($contract->term_months)
                        <tr><th>合約期間</th><td>{{ $contract->term_anchor === 'custom' ? '自訂起算' : '自'.$contract->term_anchor_label.'起' }} {{ $contract->term_months }} 個月</td></tr>
                        @endif
                        <tr><th>實際上線日</th><td>{{ $contract->go_live_date?->format('Y-m-d') ?? '-' }}</td></tr>
                        <tr><th>實際交件日</th><td>{{ $contract->actual_delivery_date?->format('Y-m-d') ?? '-' }}</td></tr>
```

- [ ] **Step 6: 以里程碑 modal 取代 `goLiveModal`**

刪除從 `{{-- 上線（signed → active）：填實際上線日，重算合約起訖日 --}}` 到對應 `@endif` 的整段 `goLiveModal`，原位置換成：

```blade
{{-- 記錄上線日／實際交件日：只有記錄的正好是起算點才會重算起訖日 --}}
@if($contract->canRecordMilestones())
@foreach($milestones as $field => $label)
@php $isAnchor = $contract->anchorDateField() === $field; @endphp
<div class="modal fade" id="milestoneModal-{{ $field }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.contracts.update-milestone-date', $contract) }}">
                @csrf @method('PUT')
                <input type="hidden" name="field" value="{{ $field }}">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $contract->{$field} ? '修改' : '設定' }}{{ $label }}</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="milestone-{{ $field }}" class="form-label">{{ $label }} <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="milestone-{{ $field }}" name="date"
                               value="{{ $contract->{$field}?->format('Y-m-d') ?? now()->format('Y-m-d') }}" required>
                    </div>
                    <p class="small text-muted mb-0">
                        @if(! $isAnchor)
                            僅記錄日期，不影響合約期間（目前起算點：{{ $contract->term_anchor_label }}）。
                        @elseif($contract->term_months)
                            將以此日期起算 {{ $contract->term_months }} 個月，覆寫目前的起訖日
                            （{{ $contract->start_date?->format('Y-m-d') ?? '-' }} ~ {{ $contract->end_date?->format('Y-m-d') ?? '-' }}）。
                        @else
                            此合約未設定期間月數，只會把開始日期改為此日期，結束日期維持不變（{{ $contract->end_date?->format('Y-m-d') ?? '未設定' }}）。
                        @endif
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">儲存</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endif
```

- [ ] **Step 7: PDF 文字**

`pdf.blade.php` 把：

```blade
                    （預估；實際以上線日起算 {{ $contract->term_months }} 個月）
```

換成：

```blade
                    （預估；將以{{ $contract->term_anchor_label }}起算 {{ $contract->term_months }} 個月）
```

（`custom` 永遠不是預估，不會進到這個分支。）

- [ ] **Step 8: 跑測試確認通過**

Run: `php artisan test --filter=ContractTermTest`
Expected: 全部 PASS。

- [ ] **Step 9: Blade 語法驗證**

Run:
```bash
php artisan view:clear && php artisan view:cache
for f in $(grep -rl "milestoneModal\|term_anchor_label" storage/framework/views/*.php); do php -l "$f"; done
php artisan view:clear
```
Expected: 每個檔案都是 `No syntax errors detected`。

- [ ] **Step 10: 手動驗證（瀏覽器）**

登入後台，對一份「已簽署」合約：

1. 點「執行中」→ 只跳 `confirm()`，確認後狀態變執行中，起訖日不變，仍有「預估」徽章。
2. 點「設定上線日」→ modal 說明會覆寫起訖日 → 儲存後起訖日重算、「預估」徽章消失、按鈕變「修改上線日」。
3. 點「修改上線日」改成另一天 → 起訖日再次重算；下方「異動紀錄」看得到前後值。
4. 下載 PDF，確認期間文字正確。

- [ ] **Step 11: Commit**

```bash
git add resources/views/admin/contracts/show.blade.php resources/views/admin/contracts/pdf.blade.php tests/Feature/ContractTermTest.php
git commit -F - <<'EOF'
新增: 合約詳情頁可設定與修改上線日、交件日

轉執行中改為一般確認，不再強制填上線日；modal 標明該日期是否會重算期間。

Claude-Session: https://claude.ai/code/session_01QjnvsebvLdrrJdamE228Hk
EOF
```

---

### Task 7: 全套驗證

**Files:** 無修改（只驗證）

**Interfaces:**
- Consumes: Task 1-6 全部
- Produces: 無

- [ ] **Step 1: 全套測試**

Run: `php artisan test`
Expected: 全部 PASS，沒有 skipped。若其他測試檔（例如 `ContractInvoiceTest`、`ProvisionalClientTest`）因 `isTermEstimated()` 或 `updateStatus()` 行為改變而失敗，判斷是測試依賴舊行為還是真的 bug，回報後再修。

- [ ] **Step 2: 確認 migration 可回滾再重跑**

Run:
```bash
php artisan migrate:rollback --step=2 && php artisan migrate
```
Expected: 兩支 migration 先回滾（欄位移除）再重新套用，皆無錯誤。

- [ ] **Step 3: 確認工作目錄乾淨**

Run: `git status --short`
Expected: 只剩 ` M package-lock.json`（使用者原本的修改），沒有其他未 commit 的檔案。
