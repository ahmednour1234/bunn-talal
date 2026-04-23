<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDepreciationItem extends Model
{
    protected $fillable = [
        'product_depreciation_id',
        'product_id',
        'quantity',
        'unit_id',
        'cost_price',
        'total_loss',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'total_loss' => 'decimal:2',
        ];
    }

    public function depreciation()
    {
        return $this->belongsTo(ProductDepreciation::class, 'product_depreciation_id');
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
