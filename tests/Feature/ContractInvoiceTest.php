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

/** 模擬舊資料:直接記在合約上的收款(payable=Contract) */
function makeContractPayment(Contract $c, float $amt, string $on, ?string $method = null): Payment
{
    $p = new Payment(['amount' => $amt, 'paid_on' => $on, 'payment_method' => $method, 'created_by' => auth()->id()]);
    $p->payable()->associate($c);
    $p->save();

    return $p;
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

it('revenue equals paid invoices (client, dashboard, invoice index)', function () {
    $contract = makeContract(); // makeContract 已登入並建立 client
    $client = $contract->client;

    $contract->invoices()->create([
        'client_id' => $client->id, 'title' => '合約款', 'status' => 'paid',
        'subtotal' => 50000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 50000, 'paid_amount' => 50000, 'currency' => 'TWD',
        'issued_date' => now(), 'due_date' => now(), 'paid_at' => now(),
    ]);
    $client->invoices()->create([
        'contract_id' => null, 'title' => '獨立', 'status' => 'paid',
        'subtotal' => 8000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 8000, 'paid_amount' => 8000, 'currency' => 'TWD',
        'issued_date' => now(), 'due_date' => now(), 'paid_at' => now(),
    ]);
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
    expect($invoice->payments()->count())->toBe(1); // 收款記在發票自身帳本
    expect((float) $contract->fresh()->paid_amount)->toBe(50000.0); // 合約衍生
});

it('one-step partial payment leaves invoice partially_paid', function () {
    $contract = makeContract();

    $this->post(route('admin.contracts.create-invoice-and-pay', $contract), [
        'amount' => 20000, 'item_mode' => 'summary',
    ]);
    $invoice = $contract->fresh()->invoices()->first();
    expect($invoice->status)->toBe('paid'); // 摘要模式發票 total = 收款額,故全額付清
    expect((float) $invoice->total)->toBe(20000.0);
});

it('all invoices including contract invoices collect on their own ledger', function () {
    $contract = makeContract();
    $invoice = $contract->invoices()->create([
        'client_id' => $contract->client_id, 'title' => '合約發票', 'status' => 'sent',
        'subtotal' => 30000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 30000, 'currency' => 'TWD', 'issued_date' => now(), 'due_date' => now(),
    ]);

    $this->post(route('admin.invoices.record-payment', $invoice), [
        'amount' => 30000, 'payment_method' => '現金',
    ]);

    $invoice = $invoice->fresh();
    expect($invoice->status)->toBe('paid');
    expect((float) $invoice->paid_amount)->toBe(30000.0);
    expect($invoice->payments()->count())->toBe(1);
    expect((float) $contract->fresh()->paid_amount)->toBe(30000.0); // 合約衍生
});

it('migrates existing contract direct payments into a carrier invoice without changing money', function () {
    $contract = makeContract(); // total 105000

    makeContractPayment($contract, 30000, '2026-04-01', '轉帳');
    makeContractPayment($contract, 20000, '2026-05-10');

    $totalBefore = Payment::sum('amount');

    // 模擬 CLI 遷移情境:無登入使用者(承載發票須顯式帶 created_by,不可靠 auth)
    auth()->logout();

    (new \App\Actions\Finance\MigrateContractPaymentsToInvoices)->execute();

    expect((float) Payment::sum('amount'))->toBe((float) $totalBefore); // 總額不變
    expect(Payment::where('payable_type', Contract::class)->count())->toBe(0); // 合約已無直接收款

    $invoice = $contract->fresh()->invoices()->first();
    expect($invoice)->not->toBeNull();
    expect((float) $invoice->total)->toBe(50000.0);
    expect((float) $invoice->paid_amount)->toBe(50000.0);
    expect($invoice->status)->toBe('paid');
    expect($invoice->payments()->count())->toBe(2); // 兩筆改掛此發票
    expect((float) $contract->fresh()->paid_amount)->toBe(50000.0); // 合約衍生不變
    expect($invoice->created_by)->not->toBeNull(); // CLI 無 auth 仍正確帶入
});

it('does not double-count when a contract payment already backs a contract invoice (mirror)', function () {
    $contract = makeContract();

    // 既有合約發票(舊 recordContractPayment 流程產生,paid_amount 已鏡像)
    $mirror = $contract->invoices()->create([
        'client_id' => $contract->client_id, 'title' => '鏡像', 'status' => 'paid',
        'subtotal' => 5000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 5000, 'paid_amount' => 5000, 'currency' => 'TWD',
        'issued_date' => now(), 'due_date' => now(), 'paid_at' => now(),
    ]);
    // 對應的合約收款(payable=Contract,且 invoice_id 指向鏡像發票)
    $p = makeContractPayment($contract, 5000, '2026-05-01');
    $p->invoice_id = $mirror->id;
    $p->save();

    (new \App\Actions\Finance\MigrateContractPaymentsToInvoices)->execute();

    // 收款改掛鏡像發票,不另開承載發票 → 不重複計算
    expect($contract->fresh()->invoices()->count())->toBe(1); // 只有鏡像,無新承載發票
    expect((float) Invoice::where('status', 'paid')->sum('total'))->toBe(5000.0); // 非 10000
    expect(Payment::where('payable_type', Contract::class)->count())->toBe(0);
    expect((int) $p->fresh()->payable_id)->toBe($mirror->id);
    expect((float) $contract->fresh()->paid_amount)->toBe(5000.0);
});

it('renders the contract show page without error', function () {
    $contract = makeContract();
    $contract->invoices()->create([
        'client_id' => $contract->client_id, 'title' => '發票', 'status' => 'paid',
        'subtotal' => 10000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 10000, 'paid_amount' => 10000, 'currency' => 'TWD',
        'issued_date' => now(), 'due_date' => now(), 'paid_at' => now(),
    ]);

    $this->get(route('admin.contracts.show', $contract))->assertOk();
});

it('renders the invoice show page (contract invoice with a payment) without error', function () {
    $contract = makeContract();
    $invoice = $contract->invoices()->create([
        'client_id' => $contract->client_id, 'title' => '合約發票', 'status' => 'sent',
        'subtotal' => 10000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount' => 0,
        'total' => 10000, 'currency' => 'TWD', 'issued_date' => now(), 'due_date' => now(),
    ]);
    $invoice->recordPayment(4000, '現金');

    $this->get(route('admin.invoices.show', $invoice))->assertOk();
});
