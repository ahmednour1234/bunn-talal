<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'rate',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public static function typeLabels(): array
    {
        return [
            'percentage' => 'نسبة مئوية %',
            'fixed' => 'مبلغ ثابت',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->type] ?? $this->type;
    }

    public function getFormattedRateAttribute(): string
    {
        return $this->type === 'percentage'
            ? $this->rate . '%'
            : number_format($this->rate, 2);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
