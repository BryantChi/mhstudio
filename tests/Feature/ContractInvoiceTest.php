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
