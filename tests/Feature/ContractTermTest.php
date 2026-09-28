<?php

use App\Models\Client;
use App\Models\Contract;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
 * 合約期間自「上線日」起算：
 * 簽約時只知道預計交件日 → 存預估起訖日；轉為執行中（上線）時以實際上線日覆寫。
 * 起訖日錯了會讓「即將到期」提醒與剩餘天數失準，所以公式與覆寫行為都要鎖住。
 */

function makeTermContract(array $overrides = []): Contract
{
    test()->actingAs(User::create([
        'name' => '測試人員',
        'email' => 'tester'.uniqid().'@example.com',
        'password' => 'password',
    ]));

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

it('上線時以實際上線日覆寫預估起訖日，並記入異動紀錄', function () {
    $contract = makeTermContract([
        'term_months' => 12,
        'start_date' => '2026-11-08',
        'end_date' => '2027-11-07',
    ]);

    $this->put(route('admin.contracts.update-status', $contract), [
        'status' => 'active',
        'go_live_date' => '2026-12-15',
    ])->assertSessionHasNoErrors();

    $contract->refresh();
    expect($contract->status)->toBe('active')
        ->and($contract->start_date->toDateString())->toBe('2026-12-15')
        ->and($contract->end_date->toDateString())->toBe('2027-12-14')
        ->and($contract->isTermEstimated())->toBeFalse();

    // 選擇「直接覆寫」的前提：原本的預估值要能從異動紀錄查回
    $log = $contract->activities()->latest('id')->first();
    expect($log->properties['old']['start_date'])->toBe('2026-11-08');
});

it('轉為執行中必須填上線日', function () {
    $contract = makeTermContract(['term_months' => 12]);

    $this->put(route('admin.contracts.update-status', $contract), ['status' => 'active'])
        ->assertSessionHasErrors('go_live_date');

    expect($contract->fresh()->status)->toBe('signed');
});

it('未設期間月數的舊合約上線時只改開始日，保留手填的結束日', function () {
    $contract = makeTermContract(['start_date' => '2026-10-01', 'end_date' => '2027-09-30']);

    $this->put(route('admin.contracts.update-status', $contract), [
        'status' => 'active',
        'go_live_date' => '2026-10-20',
    ]);

    $contract->refresh();
    expect($contract->start_date->toDateString())->toBe('2026-10-20')
        ->and($contract->end_date->toDateString())->toBe('2027-09-30');
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
