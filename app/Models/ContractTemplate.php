<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContractTemplate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'content',
        'description',
        'default_amount',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_amount' => 'decimal:2',
    ];

    /* ===== Scopes ===== */

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('order')->orderBy('name');
    }

    /* ===== Accessors ===== */

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'service' => '服務合約',
            'maintenance' => '維護合約',
            'retainer' => '長期顧問',
            'nda' => '保密協議',
            'other' => '其他',
            default => '未知',
        };
    }

    /**
     * 將內容中的佔位符替換為實際值。
     * 僅替換「有值」的佔位符；值為 null 或空字串者原樣保留，方便人工填寫。
     */
    public static function fillPlaceholders(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $content = str_replace('{{'.$key.'}}', (string) $value, $content);
        }

        return $content;
    }

    /**
     * 將範本內容替換佔位符
     */
    public function renderContent(array $variables = []): string
    {
        return static::fillPlaceholders($this->content, $variables);
    }
}
