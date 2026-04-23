<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    protected $fillable = [
        'sale_return_id',
        'sale_order_item_id',
        'product_id',
        'unit_id',
        'quantity',
        'unit_price',
        'refund_amount',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity'      => 'decimal:4',
            'unit_price'    => 'decimal:4',
            'refund_amount' => 'decimal:2',
        ];
    }

    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class, 'sale_return_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(SaleOrderItem::class, 'sale_order_item_id');
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
