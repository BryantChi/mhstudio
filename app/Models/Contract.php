<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Contract extends Model
{
    use LogsActivity;

    protected $fillable = [
        'contract_number',
        'client_id',
        'project_id',
        'quote_id',
        'title',
        'content',
        'type',
        'status',
        'amount',
        'currency',
        'start_date',
        'end_date',
        'signed_at',
        'notes',
        'created_by',
        // 財務明細
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount',
        'total',
        // 付款追蹤
        'payment_terms',
        'payment_method',
        'paid_amount',
        'due_date',
        'paid_at',
        // 續約與保固
        'auto_renew',
        'renewal_notice_days',
        'warranty_months',
        'ip_ownership',
        // 簽署方
        'client_signer_name',
        'client_signer_title',
        'client_signer_email',
        'company_signer_name',
        'execution_method',
        'sent_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'due_date' => 'date',
        'signed_at' => 'datetime',
        'paid_at' => 'datetime',
        'sent_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contract) {
            if (empty($contract->contract_number)) {
                $contract->contract_number = static::generateNumber();
            }
            if (empty($contract->created_by)) {
                $contract->created_by = auth()->id();
            }
        });
    }

    /**
     * Activity Log 配置
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'total', 'paid_amount', 'signed_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * 自動生成合約編號 CTR-YYYYMM-001
     */
    public static function generateNumber(): string
    {
        $prefix = 'CTR-'.now()->format('Ym').'-';
        $latest = static::where('contract_number', 'like', $prefix.'%')
            ->orderByDesc('contract_number')
            ->value('contract_number');

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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ContractItem::class)->orderBy('order');
    }

    /* ===== Scopes ===== */

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): void
    {
        $query->whereNotNull('end_date')
            ->where('end_date', '<=', now()->addDays($days))
            ->where('end_date', '>=', now())
            ->whereIn('status', ['active', 'signed']);
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
        // 同步 amount 欄位（遺留相容）
        $this->amount = $this->total;
        $this->save();
    }

    /* ===== Accessors ===== */

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'sent' => 'info',
            'signed' => 'primary',
            'active' => 'success',
            'completed' => 'dark',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => '草稿',
            'sent' => '已送出',
            'signed' => '已簽署',
            'active' => '執行中',
            'completed' => '已完成',
            'cancelled' => '已取消',
            default => '未知',
        };
    }

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

    public function getPaymentTermsLabelAttribute(): string
    {
        return match ($this->payment_terms) {
            'due_on_signing' => '簽約時付款',
            'net15' => 'Net 15（15 天內付款）',
            'net30' => 'Net 30（30 天內付款）',
            'net60' => 'Net 60（60 天內付款）',
            'milestone' => '依里程碑付款',
            'custom' => '自訂',
            default => '未知',
        };
    }

    public function getIpOwnershipLabelAttribute(): string
    {
        return match ($this->ip_ownership) {
            'client' => '客戶擁有',
            'shared' => '共同擁有',
            'studio' => '工作室擁有',
            default => '未知',
        };
    }

    public function getExecutionMethodLabelAttribute(): string
    {
        return match ($this->execution_method) {
            'wet_ink' => '紙本簽署',
            'esignature' => '電子簽章',
            'email_consent' => 'Email 同意',
            default => '未知',
        };
    }

    public function getBalanceDueAttribute(): float
    {
        return round($this->total - $this->paid_amount, 2);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && $this->paid_amount < $this->total;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->end_date
            && $this->end_date->isFuture()
            && $this->end_date->diffInDays(now()) <= 30;
    }

    public function getPaymentStatusAttribute(): string
    {
        if ($this->total <= 0) {
            return 'none';
        }
        if ($this->paid_amount >= $this->total) {
            return 'paid';
        }
        if ($this->paid_amount > 0) {
            return 'partial';
        }

        return 'unpaid';
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => '已付清',
            'partial' => '部分付款',
            'unpaid' => '未付款',
            default => '-',
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'success',
            'partial' => 'warning',
            'unpaid' => 'danger',
            default => 'secondary',
        };
    }
}
