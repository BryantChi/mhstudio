<?php

use App\Models\Client;
use App\Models\Contract;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
 * 合約期間依「起算點」計算（上線日／實際交件日／自訂日期）：
 * 起算日期未記錄前存預估起訖日；在詳情頁記錄起算點日期時以實際日期覆寫。
 * 起訖日錯了會讓「即將到期」提醒與剩餘天數失準，所以公式與覆寫行為都要鎖住。
 */

function makeTermContract(array $overrides = []): Contract
{
    // users.name 有唯一索引：同一個測試建多份合約時沿用已登入的使用者
    if (! auth()->check()) {
        test()->actingAs(User::create([
            'name' => '測試人員',
            'email' => 'tester'.uniqid().'@example.com',
            'password' => 'password',
        ]));
    }

    return Contract::create(array_merge([
        'client_id' => Client::create(['name' => '測試客戶'])->id,
        'title' => '測試合約',
        'content' => '內容',
        'type' => 'service',
        'status' => 'signed',
        'currency' => 'TWD',
    ], $overrides));
}

function contractFormPayload(Client $client, array $overrides = []): array
{
    return array_merge([
        'client_id' => $client->id,
        'title' => '官網建置',
        'content' => '內容',
        'type' => 'service',
        'status' => 'draft',
    ], $overrides);
}

it('結束日為上線日加期間月數再減一天，整整 N 個月不多一天', function () {
    expect(Contract::termDatesFrom('2026-11-08', 12))
        ->toBe(['start_date' => '2026-11-08', 'end_date' => '2027-11-07']);
});

it('月底起算不會溢位到下下個月', function () {
    // 1/31 + 1 個月若溢位會變 3/3，合約就多出好幾天
    expect(Contract::termDatesFrom('2027-01-31', 1)['end_date'])->toBe('2027-02-27');
});

it('預估起訖日以預計交件日加後台設定的緩衝天數為上線日', function () {
    expect(Contract::estimatedTermDates('2026-11-01', 12))
        ->toBe(['start_date' => '2026-11-08', 'end_date' => '2027-11-07']);

    Setting::create(['group' => 'document', 'key' => 'contract_go_live_buffer_days', 'value' => '14', 'type' => 'integer']);

    expect(Contract::estimatedTermDates('2026-11-01', 12)['start_date'])->toBe('2026-11-15');
});

it('建立合約時只填預計交件日與期間，後端會補上預估起訖日', function () {
    makeTermContract(); // 登入
    $client = Client::create(['name' => '大東實業']);

    $this->post(route('admin.contracts.store'), contractFormPayload($client, [
        'expected_delivery_date' => '2026-11-01',
        'term_months' => 12,
    ]))->assertSessionHasNoErrors();

    $contract = Contract::where('title', '官網建置')->firstOrFail();
    expect($contract->start_date->toDateString())->toBe('2026-11-08')
        ->and($contract->end_date->toDateString())->toBe('2027-11-07')
        ->and($contract->isTermEstimated())->toBeTrue();
});

it('手動填的起訖日優先，不被預估值蓋掉', function () {
    makeTermContract();
    $client = Client::create(['name' => '大東實業']);

    $this->post(route('admin.contracts.store'), contractFormPayload($client, [
        'expected_delivery_date' => '2026-11-01',
        'term_months' => 12,
        'start_date' => '2026-12-01',
        'end_date' => '2027-06-30',
    ]))->assertSessionHasNoErrors();

    $contract = Contract::where('title', '官網建置')->firstOrFail();
    expect($contract->start_date->toDateString())->toBe('2026-12-01')
        ->and($contract->end_date->toDateString())->toBe('2027-06-30');
});

it('編輯合約不會改動狀態，避免繞過上線日重算與狀態機', function () {
    $contract = makeTermContract(['term_months' => 12, 'start_date' => '2026-11-08', 'end_date' => '2027-11-07']);

    $this->put(route('admin.contracts.update', $contract), contractFormPayload($contract->client, [
        'status' => 'active', // 即使有人手動送出 status 也要被忽略
        'start_date' => '2026-11-08',
        'end_date' => '2027-11-07',
    ]))->assertSessionHasNoErrors();

    expect($contract->fresh()->status)->toBe('signed');
});

it('建立合約只接受草稿或已送出，不能直接建出已簽署', function () {
    makeTermContract();
    $client = Client::create(['name' => '大東實業']);

    $this->post(route('admin.contracts.store'), contractFormPayload($client, ['status' => 'signed']))
        ->assertSessionHasErrors('status');

    expect(Contract::where('title', '官網建置')->exists())->toBeFalse();
});

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
