<?php

use App\Actions\Finance\MigrateContractPaymentsToInvoices;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // 既有合約直接收款 → 發票(純搬移,金額/日期/憑證不動)。
        // 註:payments.invoice_id 欄位保留(供本遷移辨識已鏡像的收款;且未來無害),不移除。
        (new MigrateContractPaymentsToInvoices)->execute();
    }

    public function down(): void
    {
        // 資料搬移不自動回滾(請用資料庫備份)。
    }
};
