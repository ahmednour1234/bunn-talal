<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreasuryTransaction extends Model
{
    protected $fillable = [
        'treasury_id',
        'type',
        'amount',
        'description',
        'date',
        'reference_number',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public static function typeLabels(): array
    {
        return [
            'deposit' => 'إيداع',
            'withdrawal' => 'سحب',
        ];
    }
}
