<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // 來源報價單
            $table->foreignId('quote_id')->nullable()->after('project_id')
                ->constrained()->nullOnDelete();

            // 財務明細
            $table->decimal('subtotal', 12, 2)->default(0)->after('amount');
            $table->decimal('tax_rate', 5, 2)->default(5)->after('subtotal');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_rate');
            $table->decimal('discount', 12, 2)->default(0)->after('tax_amount');
            $table->decimal('total', 12, 2)->default(0)->after('discount');

            // 付款追蹤
            $table->enum('payment_terms', [
                'due_on_signing', 'net15', 'net30', 'net60', 'milestone', 'custom',
            ])->default('net30')->after('notes');
            $table->string('payment_method')->nullable()->after('payment_terms');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('payment_method');
            $table->date('due_date')->nullable()->after('paid_amount');
            $table->timestamp('paid_at')->nullable()->after('due_date');

            // 續約與保固
            $table->boolean('auto_renew')->default(false)->after('paid_at');
            $table->integer('renewal_notice_days')->default(30)->after('auto_renew');
            $table->integer('warranty_months')->nullable()->after('renewal_notice_days');
            $table->enum('ip_ownership', ['client', 'shared', 'studio'])->default('client')->after('warranty_months');

            // 簽署方
            $table->string('client_signer_name')->nullable()->after('ip_ownership');
            $table->string('client_signer_title')->nullable()->after('client_signer_name');
            $table->string('client_signer_email')->nullable()->after('client_signer_title');
            $table->string('company_signer_name')->nullable()->after('client_signer_email');
            $table->enum('execution_method', ['wet_ink', 'esignature', 'email_consent'])->default('wet_ink')->after('company_signer_name');
            $table->timestamp('sent_at')->nullable()->after('execution_method');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['quote_id']);
            $table->dropColumn([
                'quote_id',
                'subtotal', 'tax_rate', 'tax_amount', 'discount', 'total',
                'payment_terms', 'payment_method', 'paid_amount', 'due_date', 'paid_at',
                'auto_renew', 'renewal_notice_days', 'warranty_months', 'ip_ownership',
                'client_signer_name', 'client_signer_title', 'client_signer_email',
                'company_signer_name', 'execution_method', 'sent_at',
            ]);
        });
    }
};
