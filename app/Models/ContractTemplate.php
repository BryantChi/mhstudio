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
     * 將範本內容替換佔位符
     */
    public function renderContent(array $variables = []): string
    {
        $content = $this->content;

        foreach ($variables as $key => $value) {
            $content = str_replace('{{'.$key.'}}', $value, $content);
        }

        return $content;
    }
}
