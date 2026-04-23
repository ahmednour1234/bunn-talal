<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallmentPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'plan_number',
        'party_type',
        'customer_id',
        'supplier_id',
        'reference_type',
        'reference_id',
        'branch_id',
        'admin_id',
        'treasury_id',
        'start_date',
        'total_amount',
        'down_payment',
        'remaining_amount',
        'installments_count',
        'installment_amount',
        'frequency',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date'         => 'date',
            'total_amount'       => 'decimal:2',
            'down_payment'       => 'decimal:2',
            'remaining_amount'   => 'decimal:2',
            'installment_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (InstallmentPlan $p) {
            if (empty($p->plan_number)) {
                $p->plan_number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $today = now()->format('Ymd');
        $last = static::withTrashed()
            ->where('plan_number', 'like', "INST-{$today}-%")
            ->orderByDesc('plan_number')
            ->first();

        $sequence = 1;
        if ($last) {
            $lastSeq = (int) substr($last->plan_number, -4);
            $sequence = $lastSeq + 1;
        }

        return "INST-{$today}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public static function partyTypeLabels(): array
    {
        return [
            'customer' => 'عميل',
            'supplier' => 'مورد',
        ];
    }

    public static function frequencyLabels(): array
    {
        return [
            'weekly'   => 'أسبوعي',
            'biweekly' => 'نصف شهري',
            'monthly'  => 'شهري',
            'custom'   => 'مخصص',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'active'    => 'نشط',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    public function getPartyTypeLabelAttribute(): string
    {
        return static::partyTypeLabels()[$this->party_type] ?? $this->party_type;
    }

    public function getFrequencyLabelAttribute(): string
    {
        return static::frequencyLabels()[$this->frequency] ?? $this->frequency;
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->entries()->sum('paid_amount') + (float) $this->down_payment;
    }

    public function getOutstandingAttribute(): float
    {
        return max(0, (float) $this->total_amount - $this->paid_amount);
    }

    // ── Relations ─────────────────────────────────────────────────

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function entries()
    {
        return $this->hasMany(InstallmentEntry::class)->orderBy('entry_number');
    }

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class, 'reference_id')
            ->when($this->reference_type === 'sale_order', fn($q) => $q);
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'reference_id')
            ->when($this->reference_type === 'purchase_invoice', fn($q) => $q);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function getPartyNameAttribute(): string
    {
        if ($this->party_type === 'customer') {
            return $this->customer?->name ?? '—';
        }
        return $this->supplier?->name ?? '—';
    }

    public function getReferenceNumberAttribute(): ?string
    {
        if ($this->reference_type === 'sale_order') {
            return $this->saleOrder?->order_number;
        }
        if ($this->reference_type === 'purchase_invoice') {
            return $this->purchaseInvoice?->invoice_number;
        }
        return null;
    }
}
