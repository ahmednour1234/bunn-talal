<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallmentEntry extends Model
{
    protected $fillable = [
        'installment_plan_id',
        'entry_number',
        'due_date',
        'amount',
        'paid_amount',
        'status',
        'paid_at',
        'treasury_id',
        'admin_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date'    => 'date',
            'paid_at'     => 'date',
            'amount'      => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'pending'  => 'في الانتظار',
            'partial'  => 'مدفوع جزئياً',
            'paid'     => 'مدفوع',
            'overdue'  => 'متأخر',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function plan()
    {
        return $this->belongsTo(InstallmentPlan::class, 'installment_plan_id');
    }

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
