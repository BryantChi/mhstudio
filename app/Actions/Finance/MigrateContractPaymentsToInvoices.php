<?php

namespace App\Actions\Finance;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateContractPaymentsToInvoices
{
    /**
     * 把每張有「直接收款(payable=Contract)」的合約,其收款搬到發票上(payable=Invoice),
     * 金額/日期/憑證不動。
     *
     * 兩種來源分開處理,避免重複計算:
     *  - 收款本就對應某張既有發票(payments.invoice_id 有值,該發票已鏡像 paid_amount):
     *    收款直接改掛該發票,不另開承載發票。
     *  - 純直接收款(無 invoice_id):彙總為一張「承載發票」(status=paid)。
     */
    public function execute(): void
    {
        $hasInvoiceIdColumn = Schema::hasColumn('payments', 'invoice_id');

        $contractIds = Payment::where('payable_type', Contract::class)
            ->distinct()
            ->pluck('payable_id');

        $affectedClientIds = [];

        foreach ($contractIds as $contractId) {
            $contract = Contract::find($contractId);
            if (! $contract) {
                continue;
            }

            DB::transaction(function () use ($contract, $hasInvoiceIdColumn, &$affectedClientIds) {
                $payments = Payment::where('payable_type', Contract::class)
                    ->where('payable_id', $contract->id)
                    ->get();

                $directPayments = collect();

                foreach ($payments as $payment) {
                    $linkedInvoiceId = $hasInvoiceIdColumn ? $payment->invoice_id : null;

                    if ($linkedInvoiceId && Invoice::whereKey($linkedInvoiceId)->exists()) {
                        // 已對應既有發票(其 paid_amount 已鏡像)→ 收款改掛該發票,不另計
                        $payment->payable_type = Invoice::class;
                        $payment->payable_id = $linkedInvoiceId;
                        $payment->save();
                    } else {
                        $directPayments->push($payment);
                    }
                }

                if ($directPayments->isNotEmpty()) {
                    $collected = round((float) $directPayments->sum('amount'), 2);
                    $latestPaidOn = $directPayments->max('paid_on');

                    $invoice = Invoice::create([
                        'client_id' => $contract->client_id,
                        'project_id' => $contract->project_id,
                        'contract_id' => $contract->id,
                        // CLI 遷移無 auth,顯式帶入建立者(避免 created_by NOT NULL 失敗)
                        'created_by' => $contract->created_by ?? User::query()->min('id'),
                        'title' => '合約收款(轉換) '.$contract->contract_number,
                        'status' => 'paid',
                        'subtotal' => $collected,
                        'tax_rate' => 0,
                        'tax_amount' => 0,
                        'discount' => 0,
                        'total' => $collected,
                        'paid_amount' => $collected,
                        'currency' => $contract->currency ?? 'TWD',
                        'issued_date' => $latestPaidOn ?? now(),
                        'due_date' => $latestPaidOn ?? now(),
                        'paid_at' => $latestPaidOn ?? now(),
                    ]);

                    $invoice->items()->create([
                        'description' => '合約 '.$contract->contract_number.' 已收款(轉換)',
                        'quantity' => 1,
                        'unit' => '式',
                        'unit_price' => $collected,
                        'amount' => $collected,
                        'order' => 0,
                    ]);

                    foreach ($directPayments as $payment) {
                        $payment->payable_type = Invoice::class;
                        $payment->payable_id = $invoice->id;
                        $payment->save();
                    }
                }

                $affectedClientIds[$contract->client_id] = true;
            });
        }

        foreach (array_keys($affectedClientIds) as $clientId) {
            optional(Client::find($clientId))->recalculateRevenue();
        }
    }
}
