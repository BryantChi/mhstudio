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

it('creates a custom-amount invoice (tax_rate 0, single item, draft)', function () {
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
    $contract = makeContract(); // total 105000
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
    $contract = makeContract(); // total 105000
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
    $response->assertSessionHas('warning'); // flash_warning() writes session key 'warning'
});

it('linked payment creates a paid invoice bound to the payment (summary mode)', function () {
    $contract = makeContract(); // total 105000

    $this->post(route('admin.contracts.record-payment', $contract), [
        'amount' => 50000,
        'create_invoice' => '1',
        'invoice_item_mode' => 'summary',
    ]);

    $contract = $contract->fresh();
    expect((float) $contract->paid_amount)->toBe(50000.0); // 合約帳本只加一次

    $invoice = $contract->invoices()->first();
    expect($invoice)->not->toBeNull();
    expect($invoice->status)->toBe('paid');
    expect((float) $invoice->total)->toBe(50000.0);
    expect((float) $invoice->paid_amount)->toBe(50000.0);
    expect($invoice->items)->toHaveCount(1);

    $payment = $contract->payments()->first();
    expect($payment->invoice_id)->toBe($invoice->id);
});

it('linked payment custom mode uses provided description', function () {
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
    $contract = makeContract(); // 1 item 100000, total 105000

    $this->post(route('admin.contracts.record-payment', $contract), [
        'amount' => 52500, // 合約 total 的一半
        'create_invoice' => '1',
        'invoice_item_mode' => 'copy',
    ]);

    $invoice = $contract->fresh()->invoices()->first();
    expect($invoice->items)->toHaveCount(1);
    expect((float) $invoice->items->first()->amount)->toBe(50000.0); // f=0.5
    expect((float) $invoice->total)->toBe(52500.0); // 50000 + 5% tax
    expect($invoice->status)->toBe('paid');
});

it('payment without create_invoice records only the contract ledger', function () {
    $contract = makeContract();

    $this->post(route('admin.contracts.record-payment', $contract), [
        'amount' => 30000,
    ]);

    $contract = $contract->fresh();
    expect((float) $contract->paid_amount)->toBe(30000.0);
    expect($contract->invoices()->count())->toBe(0);
    expect($contract->payments()->first()->invoice_id)->toBeNull();
});

it('client revenue counts contract payments and independent-invoice payments once', function () {
    $contract = makeContract(); // makeContract 已登入並建立 client，合約 paid 0
    $client = $contract->client;

    // 合約收款 50000（走合約帳本）→ 應計入
    $contract->recordPayment(50000, '轉帳');

    // 獨立發票收款 8000（走發票自身帳本）→ 應計入
    $inv = $client->invoices()->create([
        'contract_id' => null, 'title' => '獨立', 'status' => 'sent',
        'subtotal' => 8000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 8000, 'currency' => 'TWD', 'issued_date' => now(), 'due_date' => now(),
    ]);
    $inv->recordPayment(8000, '現金');

    // 實收 = 合約收款 + 獨立發票收款，每筆只算一次
    expect((float) $client->fresh()->total_revenue)->toBe(58000.0);
});

it('dashboard month revenue sums the payments ledger including contract payments', function () {
    $contract = makeContract();
    $contract->recordPayment(50000, '轉帳'); // 合約收款也要進月營收

    $monthRevenue = $this->get(route('admin.dashboard'))->viewData('monthRevenue');
    expect((float) $monthRevenue)->toBe(50000.0);
});

it('independent invoice (contract_id null) keeps its own payment ledger intact', function () {
    // 不經 makeContract，故自行登入（Invoice::boot 取 auth()->id()）
    test()->actingAs(\App\Models\User::create([
        'name' => '管理員', 'email' => 'admin'.uniqid().'@example.com', 'password' => 'password',
    ]));
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

    $invoice->recordPayment(10000, '現金'); // 走發票自身帳本

    $invoice = $invoice->fresh();
    expect($invoice->contract_id)->toBeNull();
    expect((float) $invoice->paid_amount)->toBe(10000.0);
    expect($invoice->status)->toBe('paid');
    expect($invoice->payments()->count())->toBe(1);
});

/** 手動開立一張有 contract_id 的合約發票（sent，餘額 = total） */
function makeContractInvoice(Contract $contract, float $total = 30000): Invoice
{
    return $contract->invoices()->create([
        'client_id' => $contract->client_id,
        'title' => '手動合約發票',
        'status' => 'sent',
        'subtotal' => $total, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => $total, 'currency' => 'TWD',
        'issued_date' => now(), 'due_date' => now()->addDays(30),
    ]);
}

it('invoice index revenue sums the payments ledger (contract + independent, once)', function () {
    $contract = makeContract();
    $contract->recordPayment(50000, '轉帳'); // 合約收款

    $inv = $contract->client->invoices()->create([
        'contract_id' => null, 'title' => '獨立', 'status' => 'sent',
        'subtotal' => 8000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 8000, 'currency' => 'TWD', 'issued_date' => now(), 'due_date' => now(),
    ]);
    $inv->recordPayment(8000, '現金'); // 獨立發票收款

    $stats = $this->get(route('admin.invoices.index'))->viewData('stats');
    expect((float) $stats['total_revenue'])->toBe(58000.0);
    expect((float) $stats['month_revenue'])->toBe(58000.0); // 收款皆在本月
});

it('records a contract invoice payment into the contract ledger and marks it paid', function () {
    $contract = makeContract(); // total 105000
    $invoice = makeContractInvoice($contract, 30000);

    $this->post(route('admin.invoices.record-contract-payment', $invoice), [
        'amount' => 30000,
    ]);

    $invoice = $invoice->fresh();
    expect($invoice->status)->toBe('paid');
    expect((float) $invoice->paid_amount)->toBe(30000.0);
    // 收款寫進合約帳本（唯一真實來源）
    expect((float) $contract->fresh()->paid_amount)->toBe(30000.0);
    // 該筆 Payment 綁定發票、payable 為合約
    $payment = Payment::where('invoice_id', $invoice->id)->first();
    expect($payment)->not->toBeNull();
    expect($payment->payable_type)->toBe(Contract::class);
    // 發票自身帳本（morphMany payable=Invoice）為空，無雙重入帳
    expect($invoice->payments()->count())->toBe(0);
});

it('supports partial payment on a contract invoice', function () {
    $contract = makeContract();
    $invoice = makeContractInvoice($contract, 30000);

    $this->post(route('admin.invoices.record-contract-payment', $invoice), ['amount' => 10000]);

    $invoice = $invoice->fresh();
    expect($invoice->status)->toBe('partially_paid');
    expect((float) $invoice->paid_amount)->toBe(10000.0);
    expect((float) $contract->fresh()->paid_amount)->toBe(10000.0);
});

it('rejects the contract-payment route for an independent invoice', function () {
    test()->actingAs(\App\Models\User::create([
        'name' => 'a', 'email' => 'ind'.uniqid().'@example.com', 'password' => 'password',
    ]));
    $client = \App\Models\Client::create(['name' => '獨立']);
    $invoice = Invoice::create([
        'client_id' => $client->id, 'contract_id' => null, 'title' => '獨立', 'status' => 'sent',
        'subtotal' => 5000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 5000, 'currency' => 'TWD', 'issued_date' => now(), 'due_date' => now()->addDays(30),
    ]);

    $this->post(route('admin.invoices.record-contract-payment', $invoice), ['amount' => 5000])
        ->assertStatus(404);
});

it('blocks the invoice self-ledger payment route for contract invoices', function () {
    $contract = makeContract();
    $invoice = makeContractInvoice($contract, 30000);

    $this->post(route('admin.invoices.record-payment', $invoice), ['amount' => 30000]);

    // 不得寫入發票自身帳本，避免雙重入帳
    $invoice = $invoice->fresh();
    expect($invoice->payments()->count())->toBe(0);
    expect((float) $invoice->paid_amount)->toBe(0.0);
});
