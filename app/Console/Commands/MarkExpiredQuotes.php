<?php

namespace App\Console\Commands;

use App\Models\Quote;
use Illuminate\Console\Command;

class MarkExpiredQuotes extends Command
{
    protected $signature = 'quotes:mark-expired';

    protected $description = '自動將已過有效期限且仍為「已送出」的報價單標記為過期';

    public function handle(): int
    {
        $count = Quote::where('status', 'sent')
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now()->startOfDay())
            ->update(['status' => 'expired']);

        $this->info("已標記 {$count} 份報價單為過期狀態。");

        return self::SUCCESS;
    }
}
