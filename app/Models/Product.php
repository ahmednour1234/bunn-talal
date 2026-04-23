<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'image',
        'category_id',
        'unit_id',
        'cost_price',
        'selling_price',
        'discount',
        'discount_type',
        'tax_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_product')
            ->withPivot('quantity', 'unit_id')
            ->withTimestamps();
    }

    public function getTotalQuantityAttribute(): int
    {
        return $this->branches->sum('pivot.quantity');
    }

    public function getQuantityInBranch(int $branchId): int
    {
        return $this->branches()->where('branch_id', $branchId)->first()?->pivot->quantity ?? 0;
    }

    public function getNetPriceAttribute(): float
    {
        $price = $this->selling_price;

        // Apply discount
        if ($this->discount > 0) {
            if ($this->discount_type === 'percentage') {
                $price -= ($price * $this->discount / 100);
            } else {
                $price -= $this->discount;
            }
        }

        return max(0, $price);
    }

    public function getTaxAmountAttribute(): float
    {
        if (!$this->tax) {
            return 0;
        }

        $basePrice = $this->net_price;

        if ($this->tax->type === 'percentage') {
            return $basePrice * $this->tax->rate / 100;
        }

        return $this->tax->rate;
    }

    public function getFinalPriceAttribute(): float
    {
        return $this->net_price + $this->tax_amount;
    }

    public static function discountTypeLabels(): array
    {
        return [
            'percentage' => 'نسبة مئوية %',
            'fixed' => 'مبلغ ثابت',
        ];
    }
}
