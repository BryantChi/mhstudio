<?php

use App\Models\ContractTemplate;
use Illuminate\Database\Migrations\Migration;

/**
 * 為既有「維護合約」「一頁式網站合約」範本補上保密約定條款（插於簽章/備註之前）。
 * 防呆：找不到範本、已含「保密」、或結構（錨點）不符時皆跳過，不破壞人工編輯內容。
 */
return new class extends Migration
{
    public function up(): void
    {
        $body = "保密約定\n"
            ."1. 雙方對於合作過程中知悉之對方商業機密，負有保密義務。\n"
            ."2. 未經對方書面同意，不得將保密資訊揭露予第三方。\n"
            ."3. 保密義務於合約終止後仍持續 2 年。\n\n";

        // 維護合約：插入「七、保密約定」於簽章欄前
        $this->insertClauseBefore('維護合約', "七、{$body}", "甲方（委託方）\n公司/姓名：");

        // 一頁式網站合約：插入「八、保密約定」於備註前
        $this->insertClauseBefore('一頁式網站合約', "八、{$body}", '備註：');
    }

    private function insertClauseBefore(string $name, string $clause, string $anchor): void
    {
        $template = ContractTemplate::where('name', $name)->first();

        if (! $template
            || str_contains($template->content, '保密')
            || ! str_contains($template->content, $anchor)) {
            return;
        }

        $pos = strpos($template->content, $anchor);
        $template->content = substr($template->content, 0, $pos).$clause.substr($template->content, $pos);
        $template->save();
    }

    public function down(): void
    {
        // 不自動移除，避免誤刪人工編輯內容
    }
};
