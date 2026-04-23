<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'account_number',
        'visible_to_delegate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'visible_to_delegate' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function financialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }
}
