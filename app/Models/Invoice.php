<?php

namespace App\Models;

use App\Models\Concerns\HasPayments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use HasPayments, LogsActivity;

    protected $fillable = [
        'invoice_number',
        'client_id',
        'project_id',
        'quote_id',
        'contract_id',
        'title',
        'status',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount',
        'total',
        'paid_amount',
        'currency',
        'issued_date',
        'due_date',
        'paid_at',
        'payment_method',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'issued_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateNumber();
            }
            if (empty($invoice->created_by)) {
                $invoice->created_by = auth()->id();
            }
        });
    }

    /**
     * Activity Log 配置
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'total', 'paid_amount', 'due_date', 'paid_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * 自動生成發票編號 INV-YYYYMM-001
     */
    public static function generateNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ym').'-';
        $latest = static::where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        if ($latest) {
            $number = (int) substr($latest, -3) + 1;
        } else {
            $number = 1;
        }

        return $prefix.str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /* ===== Relations ===== */

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('order');
    }

    /* ===== Scopes ===== */

    public function scopeOverdue(Builder $query): void
    {
        $query->where('due_date', '<', now())
            ->whereIn('status', ['sent', 'partially_paid']);
    }

    public function scopePaid(Builder $query): void
    {
        $query->where('status', 'paid');
    }

    public function scopeUnpaid(Builder $query): void
    {
        $query->whereIn('status', ['sent', 'partially_paid', 'overdue']);
    }

    /* ===== Methods ===== */

    /**
     * 重新計算金額
     */
    public function recalculate(): void
    {
        $this->subtotal = $this->items()->sum('amount');
        $taxable = max(0, $this->subtotal - $this->discount);
        $this->tax_amount = round($taxable * ($this->tax_rate / 100), 2);
        $this->total = $taxable + $this->tax_amount;
        $this->save();
    }

    /**
     * 收款同步後（存檔前）：更新發票狀態與 paid_at。
     * recordPayment() / syncPaidAmount() 由 HasPayments trait 提供。
     */
    protected function afterPaymentsSynced(): void
    {
        if ($this->total > 0 && $this->paid_amount >= $this->total) {
            $this->status = 'paid';
            $this->paid_at = $this->paid_at ?: now();
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partially_paid';
            $this->paid_at = null;
        } elseif (in_array($this->status, ['paid', 'partially_paid', 'overdue'], true)) {
            // 收款被刪光，回到已送出
            $this->status = 'sent';
            $this->paid_at = null;
        }
    }

    /**
     * 存檔後：重算客戶累計營收（只計已付款發票）。
     */
    protected function afterPaymentsSaved(): void
    {
        $this->client->recalculateRevenue();
    }

    /**
     * 檢查並更新逾期狀態
     */
    public function checkOverdue(): void
    {
        if (
            $this->due_date->isPast()
            && in_array($this->status, ['sent', 'partially_paid'])
        ) {
            $this->update(['status' => 'overdue']);
        }
    }

    /* ===== Accessors ===== */

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'sent' => 'info',
            'paid' => 'success',
            'partially_paid' => 'warning',
            'overdue' => 'danger',
            'cancelled' => 'dark',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => '草稿',
            'sent' => '已送出',
            'paid' => '已付款',
            'partially_paid' => '部分付款',
            'overdue' => '已逾期',
            'cancelled' => '已取消',
            default => '未知',
        };
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, $this->total - $this->paid_amount);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && in_array($this->status, ['sent', 'partially_paid', 'overdue']);
    }
}
