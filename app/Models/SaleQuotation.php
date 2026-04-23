<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleQuotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'quotation_number',
        'customer_id',
        'branch_id',
        'admin_id',
        'delegate_id',
        'date',
        'expiry_date',
        'subtotal',
        'discount_amount',
        'discount_type',
        'tax_amount',
        'total',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date'          => 'date',
            'expiry_date'   => 'date',
            'subtotal'      => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount'    => 'decimal:2',
            'total'         => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SaleQuotation $q) {
            if (empty($q->quotation_number)) {
                $q->quotation_number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $today = now()->format('Ymd');
        $last = static::withTrashed()
            ->where('quotation_number', 'like', "QUO-{$today}-%")
            ->orderByDesc('quotation_number')
            ->first();

        $sequence = 1;
        if ($last) {
            $lastSeq = (int) substr($last->quotation_number, -4);
            $sequence = $lastSeq + 1;
        }

        return "QUO-{$today}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    public static function statusLabels(): array
    {
        return [
            'draft'    => 'مسودة',
            'sent'     => 'مرسل',
            'accepted' => 'مقبول',
            'rejected' => 'مرفوض',
            'expired'  => 'منتهي الصلاحية',
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

    public function items()
    {
        return $this->hasMany(SaleQuotationItem::class);
    }

    public function order()
    {
        return $this->hasOne(SaleOrder::class);
    }
}
