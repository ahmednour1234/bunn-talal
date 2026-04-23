<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DelegateLoan extends Model
{
    protected $fillable = [
        'delegate_id',
        'treasury_id',
        'admin_id',
        'amount',
        'paid_amount',
        'due_date',
        'is_paid',
        'paid_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount'      => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date'    => 'date',
            'paid_at'     => 'date',
            'is_paid'     => 'boolean',
        ];
    }

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function getRemainingAttribute(): float
    {
        return max(0, (float)$this->amount - (float)$this->paid_amount);
    }

    public function getIsOverdueAttribute(): bool
    {
        return !$this->is_paid && $this->due_date && $this->due_date->isPast();
    }
}
