<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'supplier_id',
        'branch_id',
        'admin_id',
        'date',
        'due_date',
        'subtotal',
        'discount_amount',
        'discount_type',
        'tax_amount',
        'total',
        'paid_amount',
        'payment_method',
        'status',
        'treasury_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PurchaseInvoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $today = now()->format('Ymd');
        $lastInvoice = static::withTrashed()
            ->where('invoice_number', 'like', "PUR-{$today}-%")
            ->orderByDesc('invoice_number')
            ->first();

        $sequence = 1;
        if ($lastInvoice) {
            $lastSeq = (int) substr($lastInvoice->invoice_number, -4);
            $sequence = $lastSeq + 1;
        }

        return "PUR-{$today}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function getRemainingAmountAttribute(): float
    {
        return (float) $this->total - (float) $this->paid_amount;
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    public static function statusLabels(): array
    {
        return [
            'draft' => 'مسودة',
            'confirmed' => 'مؤكدة',
            'partial_paid' => 'مدفوعة جزئياً',
            'paid' => 'مدفوعة بالكامل',
            'cancelled' => 'ملغاة',
        ];
    }

    public static function paymentMethodLabels(): array
    {
        return [
            'cash' => 'كاش',
            'credit' => 'آجل',
            'partial' => 'دفع جزئي',
        ];
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

    public function items()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchaseInvoicePayment::class);
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }
}
