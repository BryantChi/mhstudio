<?php

use App\Models\ContractTemplate;
use Illuminate\Database\Migrations\Migration;

/**
 * {{revision_limit}} 無對應資料來源，改以固定文字呈現。
 * 將既有範本內容中的 {{revision_limit}} 佔位符替換為預設值 3（僅動含此佔位符的範本，冪等）。
 */
return new class extends Migration
{
    public function up(): void
    {
        ContractTemplate::where('content', 'like', '%{{revision_limit}}%')
            ->get()
            ->each(function ($template) {
                $template->update([
                    'content' => str_replace('{{revision_limit}}', '3', $template->content),
                ]);
            });
    }

    public function down(): void
    {
        // 不還原（無從得知原值）
    }
};
