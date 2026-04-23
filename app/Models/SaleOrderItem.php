<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleOrderItem extends Model
{
    protected $fillable = [
        'sale_order_id',
        'product_id',
        'unit_id',
        'quantity',
        'unit_price',
        'discount',
        'discount_type',
        'tax_amount',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity'   => 'decimal:4',
            'unit_price' => 'decimal:4',
            'discount'   => 'decimal:4',
            'tax_amount' => 'decimal:4',
            'total'      => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(SaleOrder::class, 'sale_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
