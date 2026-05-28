<?php

use App\Models\Contract;
use App\Models\Invoice;
use Illuminate\Database\Migrations\Migration;

/**
 * 將既有的 paid_amount（舊流程直接累加，無帳本列）回填為一筆「期初」收款，
 * 確保 payments 帳本加總 == paid_amount，之後的登記/刪除才不會覆蓋掉舊已收金額。
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ([Contract::class, Invoice::class] as $model) {
            $model::where('paid_amount', '>', 0)
                ->whereDoesntHave('payments')
                ->get()
                ->each(function ($record) {
                    $record->payments()->create([
                        'amount' => $record->paid_amount,
                        'payment_method' => $record->payment_method ?? null,
                        'paid_on' => optional($record->paid_at)->toDateString()
                            ?? $record->updated_at->toDateString(),
                        'note' => '期初既有收款（資料遷移）',
                        'created_by' => $record->created_by,
                    ]);
                });
        }
    }

    public function down(): void
    {
        // 僅刪除本遷移建立的回填列
        \App\Models\Payment::where('note', '期初既有收款（資料遷移）')->delete();
    }
};
