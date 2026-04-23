<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripBookingRequestItem extends Model
{
    protected $fillable = [
        'booking_request_id',
        'product_id',
        'quantity',
        'unit_id',
        'unit_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'   => 'decimal:3',
            'unit_price' => 'decimal:2',
        ];
    }

    public function bookingRequest()
    {
        return $this->belongsTo(TripBookingRequest::class, 'booking_request_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function getSubtotalAttribute(): float
    {
        return round((float)$this->quantity * (float)$this->unit_price, 2);
    }
}
