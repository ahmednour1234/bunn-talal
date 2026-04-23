<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'area_id',
        'address',
        'latitude',
        'longitude',
        'credit_limit',
        'opening_balance',
        'balance',
        'classification',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude'        => 'decimal:7',
            'longitude'       => 'decimal:7',
            'credit_limit'    => 'decimal:2',
            'opening_balance' => 'decimal:2',
            'balance'         => 'decimal:2',
            'is_active'       => 'boolean',
        ];
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function saleOrders()
    {
        return $this->hasMany(SaleOrder::class);
    }

    public function saleReturns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public static function classificationLabels(): array
    {
        return [
            'premium' => 'مميز',
            'regular' => 'عادي',
            'medium' => 'متوسط',
        ];
    }

    public function getClassificationLabelAttribute(): string
    {
        return self::classificationLabels()[$this->classification] ?? $this->classification;
    }
}
