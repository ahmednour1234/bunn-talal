<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'return_number',
        'purchase_invoice_id',
        'supplier_id',
        'branch_id',
        'admin_id',
        'date',
        'subtotal',
        'loss_amount',
        'refund_amount',
        'status',
        'treasury_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'subtotal' => 'decimal:2',
            'loss_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PurchaseReturn $return) {
            if (empty($return->return_number)) {
                $return->return_number = static::generateReturnNumber();
            }
        });
    }

    public static function generateReturnNumber(): string
    {
        $today = now()->format('Ymd');
        $last = static::withTrashed()
            ->where('return_number', 'like', "RET-{$today}-%")
            ->orderByDesc('return_number')
            ->first();

        $sequence = 1;
        if ($last) {
            $lastSeq = (int) substr($last->return_number, -4);
            $sequence = $lastSeq + 1;
        }

        return "RET-{$today}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    public static function statusLabels(): array
    {
        return [
            'pending' => 'معلق',
            'confirmed' => 'مؤكد',
            'refunded' => 'تم الاسترداد',
            'cancelled' => 'ملغى',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
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
        return $this->hasMany(PurchaseReturnItem::class);
    }
}
