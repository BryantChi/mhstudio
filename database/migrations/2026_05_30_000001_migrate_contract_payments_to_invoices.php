<?php

use App\Actions\Finance\MigrateContractPaymentsToInvoices;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        (new MigrateContractPaymentsToInvoices)->execute();

        if (Schema::hasColumn('payments', 'invoice_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('invoice_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('payments', 'invoice_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('invoice_id')->nullable()->after('id')->constrained('invoices')->nullOnDelete();
            });
        }
    }
};
