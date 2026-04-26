<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrLeave extends Model
{
    protected $fillable = [
        'delegate_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function getDaysAttribute(): int
    {
        return $this->start_date && $this->end_date
            ? $this->start_date->diffInDays($this->end_date) + 1
            : 0;
    }

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public static function typeLabels(): array
    {
        return [
            'annual'    => 'إجازة سنوية',
            'sick'      => 'إجازة مرضية',
            'emergency' => 'إجازة طارئة',
            'unpaid'    => 'إجازة بدون راتب',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'pending'  => 'قيد الانتظار',
            'approved' => 'موافق عليها',
            'rejected' => 'مرفوضة',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }
}
