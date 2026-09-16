<?php

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/** 專案無 factory，沿用 ContractInvoiceTest 的手動建資料風格 */
function actingAsAdmin(): void
{
    test()->actingAs(User::create([
        'name' => '測試人員',
        'email' => 'tester'.uniqid().'@example.com',
        'password' => 'password',
    ]));
}

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

it('只給名稱即可建立暫定客戶並回傳 JSON', function () {
    actingAsAdmin();

    $this->postJson(route('admin.clients.quick-store'), ['name' => '大東實業'])
        ->assertOk()
        ->assertJson(['name' => '大東實業', 'is_provisional' => true]);

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
