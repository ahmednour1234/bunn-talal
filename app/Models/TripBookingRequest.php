<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripBookingRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'trip_id',
        'delegate_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'notes',
        'status',
        'converted_to_order_id',
    ];

    public static function statusLabels(): array
    {
        return [
            'pending'   => 'قيد الانتظار',
            'confirmed' => 'مؤكد',
            'converted' => 'محول لأمر بيع',
            'cancelled' => 'ملغي',
        ];
    }

    public static function statusColors(): array
    {
        return [
            'pending'   => 'bg-amber-100 text-amber-700',
            'confirmed' => 'bg-green-100 text-green-700',
            'converted' => 'bg-blue-100 text-blue-700',
            'cancelled' => 'bg-red-100 text-red-600',
        ];
    }

    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return static::statusColors()[$this->status] ?? 'bg-gray-100 text-gray-600';
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function items()
    {
        return $this->hasMany(TripBookingRequestItem::class, 'booking_request_id');
    }

    public function convertedOrder()
    {
        return $this->belongsTo(SaleOrder::class, 'converted_to_order_id');
    }
}
