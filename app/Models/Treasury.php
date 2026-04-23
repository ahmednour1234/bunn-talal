<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Treasury extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function transactions()
    {
        return $this->hasMany(TreasuryTransaction::class);
    }
}
