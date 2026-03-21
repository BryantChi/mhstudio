<?php

namespace App\Traits;

/**
 * 讓 Admin Controller 在 store/update 後自動重排 order 欄位，
 * 確保與拖曳排序的結果保持同步。
 *
 * 使用方式：
 *   use ReordersItems;
 *   // store() 或 update() 最後呼叫：
 *   $this->syncOrder(Service::class, $service->id, $service->order);
 */
trait ReordersItems
{
    /**
     * 將指定項目插入到 targetOrder 位置，其餘項目順移，保持 0-based 連續。
     *
     * @param  string  $modelClass  Model 全名（例如 \App\Models\Service::class）
     * @param  int  $itemId  剛儲存的項目 ID
     * @param  int  $targetOrder  使用者指定的排序值
     * @param  array  $scopeWhere  額外篩選條件（如同類別下的排序），例如 ['pricing_category_id' => 5]
     */
    protected function syncOrder(string $modelClass, int $itemId, int $targetOrder, array $scopeWhere = []): void
    {
        $query = $modelClass::orderBy('order');
        foreach ($scopeWhere as $col => $val) {
            if (is_null($val)) {
                $query->whereNull($col);
            } else {
                $query->where($col, $val);
            }
        }
        $items = $query->get();

        // 將目標項目抽出
        $item = $items->firstWhere('id', $itemId);
        if (! $item) {
            return;
        }

        $remaining = $items->reject(fn ($i) => $i->id === $itemId)->values();

        // 將目標項目插入指定位置
        $targetOrder = max(0, min($targetOrder, $remaining->count()));
        $remaining->splice($targetOrder, 0, [$item]);

        // 重新指派連續的 order 值
        foreach ($remaining as $index => $i) {
            if ((int) $i->order !== $index) {
                $modelClass::where('id', $i->id)->update(['order' => $index]);
            }
        }
    }

    /**
     * 刪除項目後重新排序，確保 order 值連續。
     * 在 destroy() 方法中，刪除後呼叫此方法。
     */
    protected function resequenceAfterDelete(string $modelClass, array $scopeWhere = []): void
    {
        $query = $modelClass::orderBy('order');
        foreach ($scopeWhere as $col => $val) {
            if (is_null($val)) {
                $query->whereNull($col);
            } else {
                $query->where($col, $val);
            }
        }

        $items = $query->get();
        foreach ($items as $index => $item) {
            if ((int) $item->order !== $index) {
                $modelClass::where('id', $item->id)->update(['order' => $index]);
            }
        }
    }
}
