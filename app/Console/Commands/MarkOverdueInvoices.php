<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue';

    protected $description = '自動將已過期的發票標記為逾期狀態';

    public function handle(): int
    {
        $count = Invoice::whereIn('status', ['sent', 'partially_paid'])
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        $this->info("已標記 {$count} 張發票為逾期狀態。");

        return self::SUCCESS;
    }
}
