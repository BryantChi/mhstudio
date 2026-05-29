<?php

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function makeContract(array $overrides = []): Contract
{
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

it('has contract_id column on invoices and invoice_id on payments', function () {
    expect(Schema::hasColumn('invoices', 'contract_id'))->toBeTrue();
    expect(Schema::hasColumn('payments', 'invoice_id'))->toBeTrue();
});

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
