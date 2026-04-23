<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryDispatchItem extends Model
{
    protected $fillable = [
        'inventory_dispatch_id',
        'product_id',
        'quantity',
        'cost_price',
        'selling_price',
        'returned_quantity',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
        ];
    }

    public function dispatch()
    {
        return $this->belongsTo(InventoryDispatch::class, 'inventory_dispatch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSoldQuantityAttribute(): int
    {
        return $this->quantity - $this->returned_quantity;
    }
}
