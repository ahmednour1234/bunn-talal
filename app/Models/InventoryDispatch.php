<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryDispatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'delegate_id',
        'admin_id',
        'trip_id',
        'status',
        'total_cost',
        'expected_sales',
        'actual_sales',
        'notes',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'total_cost' => 'decimal:2',
            'expected_sales' => 'decimal:2',
            'actual_sales' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function items()
    {
        return $this->hasMany(InventoryDispatchItem::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public static function statusLabels(): array
    {
        return [
            'pending' => 'قيد الإعداد',
            'dispatched' => 'تم الصرف',
            'partial_return' => 'مرتجع جزئي',
            'returned' => 'مرتجع كامل',
            'settled' => 'تمت التسوية',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }
}
