<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'sale_quotation_id',
        'customer_id',
        'branch_id',
        'admin_id',
        'delegate_id',
        'treasury_id',
        'trip_id',
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
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date'            => 'date',
            'due_date'        => 'date',
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'total'           => 'decimal:2',
            'paid_amount'     => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SaleOrder $o) {
            if (empty($o->order_number)) {
                $o->order_number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $today = now()->format('Ymd');
        $last = static::withTrashed()
            ->where('order_number', 'like', "ORD-{$today}-%")
            ->orderByDesc('order_number')
            ->first();

        $sequence = 1;
        if ($last) {
            $lastSeq = (int) substr($last->order_number, -4);
            $sequence = $lastSeq + 1;
        }

        return "ORD-{$today}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->paid_amount);
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    public static function statusLabels(): array
    {
        return [
            'draft'       => 'مسودة',
            'confirmed'   => 'مؤكد',
            'partial_paid' => 'مدفوع جزئياً',
            'paid'        => 'مدفوع',
            'cancelled'   => 'ملغي',
        ];
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return static::paymentMethodLabels()[$this->payment_method] ?? $this->payment_method;
    }

    public static function paymentMethodLabels(): array
    {
        return [
            'cash'    => 'نقداً',
            'credit'  => 'آجل',
            'partial' => 'جزئي',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function quotation()
    {
        return $this->belongsTo(SaleQuotation::class, 'sale_quotation_id');
    }

    public function items()
    {
        return $this->hasMany(SaleOrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SaleOrderPayment::class);
    }

    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
