<?php

namespace App\Actions\Finance;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class MigrateContractPaymentsToInvoices
{
    /**
     * 把每張有「直接收款(payable=Contract)」的合約,
     * 其收款搬到一張承載發票上(payable=Invoice),金額/日期/憑證不動。
     */
    public function execute(): void
    {
        $contractIds = Payment::where('payable_type', Contract::class)
            ->distinct()
            ->pluck('payable_id');

        $affectedClientIds = [];

        foreach ($contractIds as $contractId) {
            $contract = Contract::find($contractId);
            if (! $contract) {
                continue;
            }

            DB::transaction(function () use ($contract, &$affectedClientIds) {
                $payments = Payment::where('payable_type', Contract::class)
                    ->where('payable_id', $contract->id)
                    ->get();

                $collected = round((float) $payments->sum('amount'), 2);
                $latestPaidOn = $payments->max('paid_on');

                $invoice = Invoice::create([
                    'client_id' => $contract->client_id,
                    'project_id' => $contract->project_id,
                    'contract_id' => $contract->id,
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

                foreach ($payments as $payment) {
                    $payment->payable_type = Invoice::class;
                    $payment->payable_id = $invoice->id;
                    $payment->save();
                }

                $affectedClientIds[$contract->client_id] = true;
            });
        }

        foreach (array_keys($affectedClientIds) as $clientId) {
            optional(Client::find($clientId))->recalculateRevenue();
        }
    }
}
