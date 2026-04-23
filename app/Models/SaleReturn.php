<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'return_number',
        'sale_order_id',
        'customer_id',
        'branch_id',
        'admin_id',
        'treasury_id',
        'trip_id',
        'date',
        'subtotal',
        'refund_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date'          => 'date',
            'subtotal'      => 'decimal:2',
            'refund_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SaleReturn $r) {
            if (empty($r->return_number)) {
                $r->return_number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $today = now()->format('Ymd');
        $last = static::withTrashed()
            ->where('return_number', 'like', "SRET-{$today}-%")
            ->orderByDesc('return_number')
            ->first();

        $sequence = 1;
        if ($last) {
            $lastSeq = (int) substr($last->return_number, -4);
            $sequence = $lastSeq + 1;
        }

        return "SRET-{$today}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    public static function statusLabels(): array
    {
        return [
            'pending'   => 'في الانتظار',
            'confirmed' => 'مؤكد',
            'refunded'  => 'تم الاسترداد',
            'cancelled' => 'ملغي',
        ];
    }

    public function order()
    {
        return $this->belongsTo(SaleOrder::class, 'sale_order_id');
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

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
