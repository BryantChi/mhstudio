<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 為經常查詢但缺少索引的欄位補上索引，提升查詢效能
     */
    public function up(): void
    {
        // quote_items — 按 quote_id 查詢
        if (Schema::hasTable('quote_items') && !$this->hasIndex('quote_items', 'quote_items_quote_id_order_index')) {
            Schema::table('quote_items', function (Blueprint $table) {
                $table->index(['quote_id', 'order'], 'quote_items_quote_id_order_index');
            });
        }

        // invoice_items — 按 invoice_id 查詢
        if (Schema::hasTable('invoice_items') && !$this->hasIndex('invoice_items', 'invoice_items_invoice_id_order_index')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->index(['invoice_id', 'order'], 'invoice_items_invoice_id_order_index');
            });
        }

        // contract_items — 按 contract_id 查詢
        if (Schema::hasTable('contract_items') && !$this->hasIndex('contract_items', 'contract_items_contract_id_order_index')) {
            Schema::table('contract_items', function (Blueprint $table) {
                $table->index(['contract_id', 'order'], 'contract_items_contract_id_order_index');
            });
        }

        // projects — slug 唯一索引 + status 索引
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (!$this->hasIndex('projects', 'projects_slug_index')) {
                    $table->index('slug', 'projects_slug_index');
                }
                if (!$this->hasIndex('projects', 'projects_status_index')) {
                    $table->index('status', 'projects_status_index');
                }
            });
        }

        // subscribers — status 索引（用於 Newsletter 批次發送）
        if (Schema::hasTable('subscribers') && !$this->hasIndex('subscribers', 'subscribers_status_index')) {
            Schema::table('subscribers', function (Blueprint $table) {
                $table->index('status', 'subscribers_status_index');
            });
        }

        // newsletter_logs — 複合索引
        if (Schema::hasTable('newsletter_logs') && !$this->hasIndex('newsletter_logs', 'newsletter_logs_newsletter_status_index')) {
            Schema::table('newsletter_logs', function (Blueprint $table) {
                $table->index(['newsletter_id', 'status'], 'newsletter_logs_newsletter_status_index');
            });
        }

        // tasks — status + order 複合索引（看板視圖）
        if (Schema::hasTable('tasks') && !$this->hasIndex('tasks', 'tasks_status_order_index')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->index(['status', 'order'], 'tasks_status_order_index');
            });
        }
    }

    public function down(): void
    {
        $indexes = [
            'quote_items' => 'quote_items_quote_id_order_index',
            'invoice_items' => 'invoice_items_invoice_id_order_index',
            'contract_items' => 'contract_items_contract_id_order_index',
            'projects' => ['projects_slug_index', 'projects_status_index'],
            'subscribers' => 'subscribers_status_index',
            'newsletter_logs' => 'newsletter_logs_newsletter_status_index',
            'tasks' => 'tasks_status_order_index',
        ];

        foreach ($indexes as $table => $indexNames) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($indexNames) {
                    foreach ((array) $indexNames as $indexName) {
                        $table->dropIndex($indexName);
                    }
                });
            }
        }
    }

    /**
     * 檢查索引是否已存在
     */
    protected function hasIndex(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if ($index['name'] === $indexName) {
                return true;
            }
        }
        return false;
    }
};
