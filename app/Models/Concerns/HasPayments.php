<?php

namespace App\Models\Concerns;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * 共用收款帳本：Contract 與 Invoice 透過多型 payments 表記錄逐筆收款，
 * paid_amount 一律由帳本加總同步（帳本為唯一真實來源）。
 */
trait HasPayments
{
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable')
            ->orderByDesc('paid_on')
            ->orderByDesc('id');
    }

    /**
     * 登記一筆收款並重新同步 paid_amount。
     */
    public function recordPayment(float $amount, ?string $method = null, ?string $paidOn = null, ?string $note = null): Payment
    {
        $payment = $this->payments()->create([
            'amount' => round($amount, 2),
            'payment_method' => $method,
            'paid_on' => $paidOn ?: now()->toDateString(),
            'note' => $note,
            'created_by' => auth()->id(),
        ]);

        $this->syncPaidAmount();

        return $payment;
    }

    /**
     * 由帳本加總重算 paid_amount，再交由各模型更新狀態/paid_at。
     */
    public function syncPaidAmount(): void
    {
        $this->paid_amount = round((float) $this->payments()->sum('amount'), 2);
        $this->afterPaymentsSynced();
        $this->save();
        $this->afterPaymentsSaved();
    }

    /**
     * 收款同步後、存檔前的處理（更新狀態、paid_at 等）。各模型自行覆寫。
     */
    protected function afterPaymentsSynced(): void
    {
        // 預設不做任何事，由 Contract / Invoice 覆寫
    }

    /**
     * 收款同步並存檔後的處理（如重算客戶營收）。各模型自行覆寫。
     */
    protected function afterPaymentsSaved(): void
    {
        // 預設不做任何事
    }
}
