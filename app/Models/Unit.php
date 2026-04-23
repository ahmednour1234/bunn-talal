<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'symbol',
        'type',
        'base_unit_id',
        'conversion_factor',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'decimal:6',
            'is_active' => 'boolean',
        ];
    }

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function derivedUnits()
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }

    public function isBaseUnit(): bool
    {
        return $this->base_unit_id === null;
    }

    public function convertTo(float $value, Unit $targetUnit): float
    {
        $baseValue = $value * $this->conversion_factor;
        return $baseValue / $targetUnit->conversion_factor;
    }

    public static function typeLabels(): array
    {
        return [
            'weight' => 'الوزن',
            'volume' => 'الحجم',
            'quantity' => 'الكمية',
            'length' => 'الطول',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->type] ?? $this->type;
    }
}
