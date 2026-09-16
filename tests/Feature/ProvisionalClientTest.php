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
